<?php

namespace App\Services\Embroidery;

/**
 * Value object representing the result of an embroidery conversion attempt.
 */
class ConversionResult
{
    private bool   $success;
    private string $message;
    private array  $warnings;
    private array  $metadata;
    private ?string $outputPath;
    private int    $processingTimeMs;

    private function __construct(
        bool    $success,
        string  $message,
        array   $warnings = [],
        array   $metadata = [],
        ?string $outputPath = null,
        int     $processingTimeMs = 0
    ) {
        $this->success          = $success;
        $this->message          = $message;
        $this->warnings         = $warnings;
        $this->metadata         = $metadata;
        $this->outputPath       = $outputPath;
        $this->processingTimeMs = $processingTimeMs;
    }

    public static function success(
        string $outputPath,
        int    $processingTimeMs = 0,
        array  $warnings = [],
        array  $metadata = []
    ): self {
        return new self(true, 'Conversion completed successfully.', $warnings, $metadata, $outputPath, $processingTimeMs);
    }

    public static function failure(string $message, array $warnings = []): self
    {
        return new self(false, $message, $warnings);
    }

    public function isSuccess(): bool     { return $this->success; }
    public function isFailure(): bool     { return !$this->success; }
    public function getMessage(): string  { return $this->message; }
    public function getWarnings(): array  { return $this->warnings; }
    public function getMetadata(): array  { return $this->metadata; }
    public function getOutputPath(): ?string { return $this->outputPath; }
    public function getProcessingTimeMs(): int { return $this->processingTimeMs; }
    public function hasWarnings(): bool   { return !empty($this->warnings); }
}
