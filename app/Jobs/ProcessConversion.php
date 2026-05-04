<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Conversion;
use App\Models\ConversionUsage;
use App\Models\EmbroideryFile;
use App\Models\User;
use App\Notifications\ConversionCompleted;
use App\Notifications\ConversionFailed;
use App\Services\Embroidery\EmbroideryConverter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessConversion implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300; // 5 minutes per job

    public function __construct(
        private readonly Conversion $conversion,
        private readonly User       $user
    ) {}

    public function handle(EmbroideryConverter $converter): void
    {
        $this->conversion->update(['status' => 'processing']);
        $startTime = microtime(true);

        try {
            $sourceFile = $this->conversion->sourceFile;

            if (!$sourceFile) {
                throw new \RuntimeException('Source file not found in database.');
            }

            $sourcePath  = Storage::disk($sourceFile->disk)->path($sourceFile->path);
            $outputDir   = Storage::disk('local')->path("embroidery/{$this->user->id}/converted");

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Run the conversion
            $result = $converter->convert($sourcePath, $this->conversion->target_format, $outputDir);

            $elapsedMs = (int) ((microtime(true) - $startTime) * 1000);

            if ($result->isFailure()) {
                throw new \RuntimeException($result->getMessage());
            }

            // Store output file record
            $outputPath = $result->getOutputPath();
            $metadata   = $result->getMetadata();
            $storedName = basename($outputPath);
            $relPath    = "embroidery/{$this->user->id}/converted/{$storedName}";

            $outputFile = EmbroideryFile::create([
                'user_id'       => $this->user->id,
                'original_name' => pathinfo($sourceFile->original_name, PATHINFO_FILENAME) . '.' . $this->conversion->target_format,
                'stored_name'   => $storedName,
                'disk'          => 'local',
                'path'          => $relPath,
                'extension'     => $this->conversion->target_format,
                'size_bytes'    => filesize($outputPath),
                'type'          => 'converted',
                'parent_id'     => $sourceFile->id,
                'stitch_count'  => $metadata['stitch_count'] ?? null,
                'color_count'   => $metadata['color_count'] ?? null,
                'thread_colors' => $metadata['thread_colors'] ?? null,
                'width_mm'      => $metadata['width_mm'] ?? null,
                'height_mm'     => $metadata['height_mm'] ?? null,
                'metadata'      => $metadata ?: null,
            ]);

            // Mark conversion complete
            $this->conversion->update([
                'status'             => 'completed',
                'output_file_id'     => $outputFile->id,
                'warnings'           => $result->getWarnings() ?: null,
                'processing_time_ms' => $elapsedMs,
                'completed_at'       => now(),
            ]);

            // Track usage
            ConversionUsage::incrementForUser($this->user->id);

            // Notify user
            $this->user->notify(new ConversionCompleted($this->conversion, $outputFile));

            AuditLog::log('conversion.completed', $this->user->id, Conversion::class, $this->conversion->id);

        } catch (\Throwable $e) {
            $elapsedMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->conversion->update([
                'status'             => 'failed',
                'error_message'      => $e->getMessage(),
                'processing_time_ms' => $elapsedMs,
                'completed_at'       => now(),
            ]);

            Log::error('Conversion failed', [
                'conversion_id' => $this->conversion->id,
                'user_id'       => $this->user->id,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            // Notify user of failure
            $this->user->notify(new ConversionFailed($this->conversion));

            AuditLog::log('conversion.failed', $this->user->id, Conversion::class, $this->conversion->id, [], [
                'error' => $e->getMessage(),
            ]);

            // Don't retry on conversion-logic failures; only retry on infrastructure failures
            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->conversion->update([
            'status'        => 'failed',
            'error_message' => 'Job failed after maximum retries: ' . $exception->getMessage(),
            'completed_at'  => now(),
        ]);

        Log::error('ProcessConversion job permanently failed', [
            'conversion_id' => $this->conversion->id,
            'error'         => $exception->getMessage(),
        ]);
    }
}
