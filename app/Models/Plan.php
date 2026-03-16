<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly', 'price_yearly',
        'stripe_monthly_price_id', 'stripe_yearly_price_id',
        'conversions_per_day', 'storage_limit_mb', 'max_file_size_mb',
        'max_batch_size', 'preview_enabled', 'history_enabled',
        'api_access', 'priority_queue', 'is_active', 'is_featured',
        'sort_order', 'features',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly'    => 'decimal:2',
            'price_yearly'     => 'decimal:2',
            'preview_enabled'  => 'boolean',
            'history_enabled'  => 'boolean',
            'api_access'       => 'boolean',
            'priority_queue'   => 'boolean',
            'is_active'        => 'boolean',
            'is_featured'      => 'boolean',
            'features'         => 'array',
        ];
    }

    public function isFree(): bool
    {
        return $this->slug === 'free';
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_monthly == 0) {
            return 'Free';
        }

        return '$' . number_format($this->price_monthly, 2) . '/mo';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
