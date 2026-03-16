<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class EmbroideryFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'original_name', 'stored_name', 'disk', 'path',
        'extension', 'size_bytes', 'type', 'parent_id',
        'stitch_count', 'color_count', 'thread_colors',
        'width_mm', 'height_mm', 'hoop_size', 'metadata',
        'preview_path', 'preview_generated',
    ];

    protected function casts(): array
    {
        return [
            'thread_colors'     => 'array',
            'metadata'          => 'array',
            'preview_generated' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(EmbroideryFile::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(EmbroideryFile::class, 'parent_id');
    }

    public function conversions()
    {
        return $this->hasMany(Conversion::class, 'source_file_id');
    }

    // Accessors
    public function getSizeHumanAttribute(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';

        return round($bytes / 1048576, 2) . ' MB';
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('files.download', $this->id);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if ($this->preview_path) {
            return Storage::url($this->preview_path);
        }

        return null;
    }

    public function getDimensionsAttribute(): ?string
    {
        if ($this->width_mm && $this->height_mm) {
            return $this->width_mm . ' × ' . $this->height_mm . ' mm';
        }

        return null;
    }

    /**
     * Delete the physical file from storage.
     */
    public function deletePhysicalFile(): void
    {
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }

        if ($this->preview_path && Storage::disk($this->disk)->exists($this->preview_path)) {
            Storage::disk($this->disk)->delete($this->preview_path);
        }
    }

    public function scopeOriginals($query)
    {
        return $query->where('type', 'original');
    }

    public function scopeConverted($query)
    {
        return $query->where('type', 'converted');
    }
}
