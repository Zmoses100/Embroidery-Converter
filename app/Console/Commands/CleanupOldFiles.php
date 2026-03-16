<?php

namespace App\Console\Commands;

use App\Models\EmbroideryFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldFiles extends Command
{
    protected $signature   = 'embroidery:cleanup-old-files {--days=30 : Number of days to keep converted files}';
    protected $description = 'Delete old converted embroidery files past their retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $old = EmbroideryFile::where('type', 'converted')
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        if ($old->isEmpty()) {
            $this->info("No old converted files to clean up.");
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($old as $file) {
            $file->deletePhysicalFile();
            $file->delete();
            $deleted++;
        }

        $this->info("Cleaned up {$deleted} converted file(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
