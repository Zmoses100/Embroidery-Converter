<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, Billable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_admin',
        'timezone',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    // Relationships
    public function embroideryFiles()
    {
        return $this->hasMany(EmbroideryFile::class);
    }

    public function conversions()
    {
        return $this->hasMany(Conversion::class);
    }

    public function conversionUsage()
    {
        return $this->hasMany(ConversionUsage::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    // Helpers
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Get current active plan details.
     */
    public function activePlan(): ?Plan
    {
        // Get plan from subscription
        if ($this->subscribed('default')) {
            $stripePriceId = $this->subscription('default')?->stripe_price;
            return Plan::where('stripe_monthly_price_id', $stripePriceId)
                ->orWhere('stripe_yearly_price_id', $stripePriceId)
                ->first();
        }

        // Free plan
        $freePlan = Plan::where('slug', 'free')->first();

        if ($freePlan) {
            return $freePlan;
        }

        // Ensure we always have a baseline plan to prevent null access / 500s
        return Plan::firstOrCreate(
            ['slug' => 'free'],
            [
                'name'                => 'Free',
                'price_monthly'       => 0,
                'price_yearly'        => 0,
                'conversions_per_day' => 5,
                'storage_limit_mb'    => 100,
                'max_file_size_mb'    => 5,
                'max_batch_size'      => 1,
                'priority_queue'      => false,
                'preview_enabled'     => false,
                'api_access'          => false,
                'is_active'           => true,
                'sort_order'          => 1,
            ]
        );
    }

    /**
     * Get today's conversion count.
     */
    public function todayConversionCount(): int
    {
        try {
            return ConversionUsage::where('user_id', $this->id)
                ->where('date', today()->toDateString())
                ->value('count') ?? 0;
        } catch (\Throwable $e) {
            // Gracefully handle missing table during first-run / misconfigured environments
            return 0;
        }
    }

    /**
     * Check if user can convert files today.
     */
    public function canConvert(): bool
    {
        $plan = $this->activePlan();
        if (!$plan) return false;
        if ($plan->conversions_per_day === -1) return true;

        return $this->todayConversionCount() < $plan->conversions_per_day;
    }

    /**
     * Get total storage used in bytes.
     */
    public function storageUsedBytes(): int
    {
        try {
            return (int) $this->embroideryFiles()->sum('size_bytes');
        } catch (\Throwable $e) {
            // Gracefully handle missing table during first-run / misconfigured environments
            return 0;
        }
    }

    /**
     * Get storage used in MB.
     */
    public function storageUsedMb(): float
    {
        return round($this->storageUsedBytes() / 1024 / 1024, 2);
    }

    /**
     * Check if user has storage available for a file of given bytes.
     */
    public function hasStorageFor(int $bytes): bool
    {
        $plan = $this->activePlan();
        if (!$plan) return false;
        $limitBytes = $plan->storage_limit_mb * 1024 * 1024;

        return ($this->storageUsedBytes() + $bytes) <= $limitBytes;
    }

    /**
     * Get avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }
}
