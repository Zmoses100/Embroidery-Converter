<?php

namespace App\Services\Embroidery;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * EmbroideryConverter
 *
 * Handles embroidery file format conversion using pyembroidery.
 * Windows/Laragon-safe version.
 */
class EmbroideryConverter
{
    /**
     * Supported embroidery formats and their metadata.
     */
    public const SUPPORTED_FORMATS = [
        'pes' => ['name' => 'Brother PES', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'dst' => ['name' => 'Tajima DST', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'jef' => ['name' => 'Janome JEF', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'exp' => ['name' => 'Melco EXP', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'vp3' => ['name' => 'Pfaff VP3', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'hus' => ['name' => 'Husqvarna Viking HUS', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'xxx' => ['name' => 'Singer XXX', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'sew' => ['name' => 'Janome SEW', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'vip' => ['name' => 'Pfaff VIP', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'pec' => ['name' => 'Brother PEC', 'mime' => 'application/octet-stream', 'read' => true, 'write' => false],
        'pcs' => ['name' => 'Pfaff PCS', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'shv' => ['name' => 'Viking SHV', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'csv' => ['name' => 'Comma-Separated Stitch Data', 'mime' => 'text/csv', 'read' => true, 'write' => true],
        'dat' => ['name' => 'Barudan DAT', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'dsb' => ['name' => 'Barudan DSB', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'dsz' => ['name' => 'ZSK DSZ', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'emb' => ['name' => 'Wilcom EMB', 'mime' => 'application/octet-stream', 'read' => false, 'write' => false],
        'fxy' => ['name' => 'Fortron FXY', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'gt'  => ['name' => 'Gold Thread GT', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'inb' => ['name' => 'Inbro INB', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'jbf' => ['name' => 'Janome JBF', 'mime' => 'application/octet-stream', 'read' => true, 'write' => false],
        'ksm' => ['name' => 'Pfaff KSM', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'pcd' => ['name' => 'Pfaff PCD', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'pcq' => ['name' => 'Pfaff PCQ', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'rgb' => ['name' => 'RGB Color File', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'stc' => ['name' => 'Data Stitch STC', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'stx' => ['name' => 'Data Stitch STX', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'tap' => ['name' => 'Happy TAP', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'u01' => ['name' => 'Barudan U01', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'zhs' => ['name' => 'ZSK ZHS', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
        'zxy' => ['name' => 'ZSK ZXY', 'mime' => 'application/octet-stream', 'read' => true, 'write' => true],
    ];

    /**
     * Formats that cannot be fully preserved during conversion.
     */
    private const LOSSY_FORMATS = ['emb', 'pec', 'jbf'];

    private string $pythonBin;
    private bool $pyembroideryAvailable;

    public function __construct()
    {
        // Windows/Laragon should use "py".
        // Linux production can use PYTHON_BIN=/usr/bin/python3.
        $this->pythonBin = env(
            'PYTHON_BIN',
            env('PYEMBROIDERY_PYTHON_BIN', PHP_OS_FAMILY === 'Windows' ? 'py' : 'python3')
        );

        $this->pyembroideryAvailable = filter_var(
            env('PYEMBROIDERY_AVAILABLE', false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Convert an embroidery file from one format to another.
     */
    public function convert(string $sourcePath, string $targetFormat, string $outputDir): ConversionResult
    {
        $targetFormat = strtolower($targetFormat);
        $sourceExt = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);

        $outputDir = rtrim($outputDir, DIRECTORY_SEPARATOR . '/\\');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $baseName . '.' . $targetFormat;

        if (! isset(self::SUPPORTED_FORMATS[$sourceExt])) {
            return ConversionResult::failure("Source format '{$sourceExt}' is not supported.");
        }

        if (! isset(self::SUPPORTED_FORMATS[$targetFormat])) {
            return ConversionResult::failure("Target format '{$targetFormat}' is not supported.");
        }

        if (! self::SUPPORTED_FORMATS[$targetFormat]['write']) {
            return ConversionResult::failure("Writing to format '{$targetFormat}' is not supported.");
        }

        if (! file_exists($sourcePath)) {
            return ConversionResult::failure("Source file not found: {$sourcePath}");
        }

        $warnings = [];

        if (in_array($targetFormat, self::LOSSY_FORMATS, true) || in_array($sourceExt, self::LOSSY_FORMATS, true)) {
            $warnings[] = "Conversion involving '{$sourceExt}' or '{$targetFormat}' may lose some design properties.";
        }

        if ($sourceExt === 'dst' && $targetFormat !== 'dst') {
            $warnings[] = 'DST format does not store color information natively. Thread colors may not be preserved.';
        }

        if ($this->pyembroideryAvailable) {
            return $this->convertWithPyembroidery($sourcePath, $outputPath, $warnings);
        }

        return ConversionResult::failure(
            'Embroidery conversion engine pyembroidery is not enabled. Run: py -m pip install pyembroidery and set PYEMBROIDERY_AVAILABLE=true in .env',
            $warnings
        );
    }

    /**
     * Convert using pyembroidery Python library.
     */
    private function convertWithPyembroidery(
        string $sourcePath,
        string $outputPath,
        array $warnings = []
    ): ConversionResult {
        $script = <<<'PYTHON'
import pyembroidery
import sys
import os

source = sys.argv[1]
target = sys.argv[2]

pattern = pyembroidery.read(source)

if pattern is None:
    raise Exception("pyembroidery could not read the source file.")

output_dir = os.path.dirname(target)

if output_dir and not os.path.exists(output_dir):
    os.makedirs(output_dir, exist_ok=True)

pyembroidery.write(pattern, target)

if not os.path.exists(target):
    raise Exception("Output file was not created.")
PYTHON;

        $startTime = microtime(true);

        $process = new Process([
            $this->pythonBin,
            '-c',
            $script,
            $sourcePath,
            $outputPath,
        ]);

        $process->setTimeout(120);
        $process->run();

        $elapsedMs = (int) ((microtime(true) - $startTime) * 1000);

        if (! $process->isSuccessful() || ! file_exists($outputPath)) {
            $errorMsg = trim($process->getErrorOutput() . "\n" . $process->getOutput());

            Log::error('pyembroidery conversion failed', [
                'python_bin' => $this->pythonBin,
                'source' => $sourcePath,
                'target' => $outputPath,
                'error' => $errorMsg,
            ]);

            return ConversionResult::failure(
                'Conversion failed: ' . ($errorMsg ?: 'Unknown pyembroidery error.'),
                $warnings
            );
        }

        $metadata = $this->extractMetadata($sourcePath);

        return ConversionResult::success($outputPath, $elapsedMs, $warnings, $metadata);
    }

    /**
     * Extract design metadata from an embroidery file using pyembroidery.
     */
    public function extractMetadata(string $filePath): array
    {
        if (! $this->pyembroideryAvailable || ! file_exists($filePath)) {
            return [];
        }

        $script = <<<'PYTHON'
import pyembroidery
import json
import sys

try:
    pattern = pyembroidery.read(sys.argv[1])

    if pattern is None:
        print(json.dumps({}))
        sys.exit(0)

    colors = []

    for thread in pattern.threadlist:
        colors.append({
            "name": getattr(thread, "name", "") or "",
            "color": "#{:06X}".format(getattr(thread, "color", 0) or 0),
            "catalog_number": getattr(thread, "catalog_number", "") or "",
        })

    bounds = pattern.bounds()

    width = None
    height = None

    if bounds:
        width = round((bounds[2] - bounds[0]) / 10, 2)
        height = round((bounds[3] - bounds[1]) / 10, 2)

    info = {
        "stitch_count": len(pattern.stitches),
        "color_count": len(pattern.threadlist),
        "thread_colors": colors,
        "width_mm": width,
        "height_mm": height,
    }

    print(json.dumps(info))
except Exception as e:
    print(json.dumps({"error": str(e)}))
PYTHON;

        $process = new Process([
            $this->pythonBin,
            '-c',
            $script,
            $filePath,
        ]);

        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::warning('pyembroidery metadata extraction failed', [
                'python_bin' => $this->pythonBin,
                'file' => $filePath,
                'error' => trim($process->getErrorOutput() . "\n" . $process->getOutput()),
            ]);

            return [];
        }

        $json = trim($process->getOutput());
        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Generate a preview PNG from an embroidery file using pyembroidery.
     */
    public function generatePreview(string $filePath, string $outputDir): ?string
    {
        if (! $this->pyembroideryAvailable || ! file_exists($filePath)) {
            return null;
        }

        $outputDir = rtrim($outputDir, DIRECTORY_SEPARATOR . '/\\');

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $baseName = pathinfo($filePath, PATHINFO_FILENAME);
        $outputPath = $outputDir . DIRECTORY_SEPARATOR . $baseName . '_preview.png';

        $script = <<<'PYTHON'
import pyembroidery
import sys
import os

source = sys.argv[1]
target = sys.argv[2]

pattern = pyembroidery.read(source)

if pattern is None:
    sys.exit(1)

output_dir = os.path.dirname(target)

if output_dir and not os.path.exists(output_dir):
    os.makedirs(output_dir, exist_ok=True)

pyembroidery.write(pattern, target)
PYTHON;

        $process = new Process([
            $this->pythonBin,
            '-c',
            $script,
            $filePath,
            $outputPath,
        ]);

        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::warning('pyembroidery preview generation failed', [
                'python_bin' => $this->pythonBin,
                'file' => $filePath,
                'target' => $outputPath,
                'error' => trim($process->getErrorOutput() . "\n" . $process->getOutput()),
            ]);

            return null;
        }

        return file_exists($outputPath) ? $outputPath : null;
    }

    /**
     * Get supported read formats.
     */
    public static function readableFormats(): array
    {
        return array_keys(array_filter(self::SUPPORTED_FORMATS, fn ($f) => $f['read']));
    }

    /**
     * Get supported write formats.
     */
    public static function writableFormats(): array
    {
        return array_keys(array_filter(self::SUPPORTED_FORMATS, fn ($f) => $f['write']));
    }

    /**
     * Check if a format is supported for reading.
     */
    public static function isReadable(string $format): bool
    {
        $format = strtolower($format);

        return isset(self::SUPPORTED_FORMATS[$format]) && self::SUPPORTED_FORMATS[$format]['read'];
    }

    /**
     * Check if a format is supported for writing.
     */
    public static function isWritable(string $format): bool
    {
        $format = strtolower($format);

        return isset(self::SUPPORTED_FORMATS[$format]) && self::SUPPORTED_FORMATS[$format]['write'];
    }
}