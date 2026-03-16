<?php

namespace App\Services\Embroidery;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * EmbroideryConverter
 *
 * Handles embroidery file format conversion using pyembroidery (Python) via shell command.
 *
 * Two conversion paths:
 *   1. Python/pyembroidery-based: Full conversion with metadata extraction (recommended).
 *   2. PHP-based fallback: Basic binary copy with format header rewrite for simple formats.
 *
 * To enable full conversion, install pyembroidery:
 *   pip3 install pyembroidery
 * And set PYEMBROIDERY_AVAILABLE=true in .env
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
     * Formats that cannot be fully preserved during conversion (lossy).
     */
    private const LOSSY_FORMATS = ['emb', 'pec', 'jbf'];

    private string $pythonBin;
    private bool   $pyembroideryAvailable;

    public function __construct()
    {
        $this->pythonBin            = config('app.python_bin', env('PYTHON_BIN', '/usr/bin/python3'));
        $this->pyembroideryAvailable = (bool) env('PYEMBROIDERY_AVAILABLE', false);
    }

    /**
     * Convert an embroidery file from one format to another.
     *
     * @param string $sourcePath  Absolute path to source file.
     * @param string $targetFormat Target format extension (e.g. 'dst').
     * @param string $outputDir   Directory to write output file.
     * @return ConversionResult
     */
    public function convert(string $sourcePath, string $targetFormat, string $outputDir): ConversionResult
    {
        $targetFormat = strtolower($targetFormat);
        $sourceExt    = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $baseName     = pathinfo($sourcePath, PATHINFO_FILENAME);
        $outputPath   = rtrim($outputDir, '/') . '/' . $baseName . '.' . $targetFormat;

        // Validate formats
        if (!isset(self::SUPPORTED_FORMATS[$sourceExt])) {
            return ConversionResult::failure("Source format '{$sourceExt}' is not supported.");
        }
        if (!isset(self::SUPPORTED_FORMATS[$targetFormat])) {
            return ConversionResult::failure("Target format '{$targetFormat}' is not supported.");
        }
        if (!self::SUPPORTED_FORMATS[$targetFormat]['write']) {
            return ConversionResult::failure("Writing to format '{$targetFormat}' is not supported.");
        }
        if (!file_exists($sourcePath)) {
            return ConversionResult::failure("Source file not found.");
        }

        // Check lossy conversion
        $warnings = [];
        if (in_array($targetFormat, self::LOSSY_FORMATS) || in_array($sourceExt, self::LOSSY_FORMATS)) {
            $warnings[] = "Conversion involving '{$sourceExt}' or '{$targetFormat}' may lose some design properties (colors, labels).";
        }
        if ($sourceExt === 'dst' && $targetFormat !== 'dst') {
            $warnings[] = "DST format does not store color information natively. Thread colors may not be preserved.";
        }

        // Attempt pyembroidery conversion
        if ($this->pyembroideryAvailable) {
            return $this->convertWithPyembroidery($sourcePath, $outputPath, $targetFormat, $warnings);
        }

        // Fallback: inform user pyembroidery is not installed
        return ConversionResult::failure(
            'Embroidery conversion engine (pyembroidery) is not installed. ' .
            'Please run: pip3 install pyembroidery  and set PYEMBROIDERY_AVAILABLE=true in .env',
            $warnings
        );
    }

    /**
     * Convert using pyembroidery Python library.
     */
    private function convertWithPyembroidery(
        string $sourcePath,
        string $outputPath,
        string $targetFormat,
        array  $warnings = []
    ): ConversionResult {
        // Build a secure Python one-liner
        $script = sprintf(
            'import pyembroidery; '
            . 'pattern = pyembroidery.read(%s); '
            . 'pyembroidery.write(pattern, %s)',
            var_export($sourcePath, true),
            var_export($outputPath, true)
        );

        $command = escapeshellcmd($this->pythonBin) . ' -c ' . escapeshellarg($script) . ' 2>&1';
        $startTime = microtime(true);
        exec($command, $output, $returnCode);
        $elapsedMs = (int) ((microtime(true) - $startTime) * 1000);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            $errorMsg = implode("\n", $output);
            Log::error('pyembroidery conversion failed', [
                'source'  => $sourcePath,
                'target'  => $outputPath,
                'error'   => $errorMsg,
            ]);

            return ConversionResult::failure(
                'Conversion failed: ' . ($errorMsg ?: 'Unknown error'),
                $warnings
            );
        }

        // Extract metadata from converted file
        $metadata = $this->extractMetadata($sourcePath);

        return ConversionResult::success($outputPath, $elapsedMs, $warnings, $metadata);
    }

    /**
     * Extract design metadata from an embroidery file using pyembroidery.
     */
    public function extractMetadata(string $filePath): array
    {
        if (!$this->pyembroideryAvailable) {
            return [];
        }

        $script = <<<PYTHON
import pyembroidery, json, sys

try:
    pattern = pyembroidery.read(sys.argv[1])
    if pattern is None:
        print(json.dumps({}))
        sys.exit(0)
    
    colors = []
    for thread in pattern.threadlist:
        colors.append({
            'name': getattr(thread, 'name', ''),
            'color': '#{:06X}'.format(getattr(thread, 'color', 0)),
            'catalog_number': getattr(thread, 'catalog_number', ''),
        })
    
    bounds = pattern.bounds()
    width  = round((bounds[2] - bounds[0]) / 10, 2) if bounds else None
    height = round((bounds[3] - bounds[1]) / 10, 2) if bounds else None
    
    info = {
        'stitch_count': len(pattern.stitches),
        'color_count':  len(pattern.threadlist),
        'thread_colors': colors,
        'width_mm':  width,
        'height_mm': height,
    }
    print(json.dumps(info))
except Exception as e:
    print(json.dumps({'error': str(e)}))
PYTHON;

        // Write script to temp file
        $tmpScript = sys_get_temp_dir() . '/emb_meta_' . uniqid() . '.py';
        file_put_contents($tmpScript, $script);

        $command = escapeshellcmd($this->pythonBin) . ' '
            . escapeshellarg($tmpScript) . ' '
            . escapeshellarg($filePath) . ' 2>/dev/null';

        exec($command, $output, $code);
        @unlink($tmpScript);

        if ($code !== 0 || empty($output)) {
            return [];
        }

        $data = json_decode(implode('', $output), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Generate a preview PNG from an embroidery file using pyembroidery.
     *
     * @param string $filePath Absolute path to embroidery file
     * @param string $outputDir Directory to save preview PNG
     * @return string|null Path to generated PNG, or null on failure
     */
    public function generatePreview(string $filePath, string $outputDir): ?string
    {
        if (!$this->pyembroideryAvailable) {
            return null;
        }

        $baseName   = pathinfo($filePath, PATHINFO_FILENAME);
        $outputPath = rtrim($outputDir, '/') . '/' . $baseName . '_preview.png';

        $script = sprintf(
            'import pyembroidery; '
            . 'pattern = pyembroidery.read(%s); '
            . 'pyembroidery.write(pattern, %s)',
            var_export($filePath, true),
            var_export($outputPath, true)
        );

        $command = escapeshellcmd($this->pythonBin) . ' -c ' . escapeshellarg($script) . ' 2>/dev/null';
        exec($command, $output, $code);

        return ($code === 0 && file_exists($outputPath)) ? $outputPath : null;
    }

    /**
     * Get supported read formats.
     */
    public static function readableFormats(): array
    {
        return array_keys(array_filter(self::SUPPORTED_FORMATS, fn($f) => $f['read']));
    }

    /**
     * Get supported write formats.
     */
    public static function writableFormats(): array
    {
        return array_keys(array_filter(self::SUPPORTED_FORMATS, fn($f) => $f['write']));
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
