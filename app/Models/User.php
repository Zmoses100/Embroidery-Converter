<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
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
      *
      * Returns a persisted Plan when available; in failure scenarios (e.g. missing plans table)
      * this returns an in-memory Free plan instance so callers never receive null.
      */
     public function activePlan(): Plan
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

        $defaultFreePlan = Plan::freeDefaults();

        // Ensure we always have a baseline plan to prevent null access / 500s
        try {
            return Plan::firstOrCreate(
                ['slug' => 'free'],
                $defaultFreePlan
            );
        } catch (QueryException $e) {
            Log::warning('activePlan fallback creation failed; returning in-memory free plan', [
                'user_id' => $this->id,
                'error'   => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);

            // This Plan instance is not persisted; it prevents nulls when the plans table is unavailable.
            return new Plan($defaultFreePlan);
        }
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
        } catch (QueryException $e) {
            Log::warning('todayConversionCount failed; returning 0 as fallback', [
                'user_id' => $this->id,
                'error'   => $e->getMessage(),
            ]);

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
        } catch (QueryException $e) {
            Log::warning('storageUsedBytes failed; returning 0 as fallback', [
                'user_id' => $this->id,
                'error'   => $e->getMessage(),
            ]);

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
