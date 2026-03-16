<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'source_file_id', 'output_file_id',
        'source_format', 'target_format', 'original_filename',
        'status', 'job_id', 'error_message', 'warnings',
        'processing_time_ms', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'warnings'      => 'array',
            'completed_at'  => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceFile()
    {
        return $this->belongsTo(EmbroideryFile::class, 'source_file_id');
    }

    public function outputFile()
    {
        return $this->belongsTo(EmbroideryFile::class, 'output_file_id');
    }

    // Helpers
    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'    => '<span class="badge badge-warning">Pending</span>',
            'processing' => '<span class="badge badge-info">Processing</span>',
            'completed'  => '<span class="badge badge-success">Completed</span>',
            'failed'     => '<span class="badge badge-danger">Failed</span>',
            default      => '<span class="badge badge-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function scopePending($query)     { return $query->where('status', 'pending'); }
    public function scopeCompleted($query)   { return $query->where('status', 'completed'); }
    public function scopeFailed($query)      { return $query->where('status', 'failed'); }
}
