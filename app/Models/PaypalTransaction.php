<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaypalTransaction extends Model
{
    use HasFactory;

    protected $table = 'paypal_transactions';

    protected $fillable = [
        'user_id',
        'plan_id',
        'subscription_id',
        'order_id',
        'billing_plan_id',
        'interval',
        'amount',
        'currency',
        'status',
        'payer_email',
        'payer_id',
        'metadata',
        'error_message',
        'activated_at',
        'cancelled_at',
        'next_billing_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'metadata'      => 'array',
            'activated_at'  => 'datetime',
            'cancelled_at'  => 'datetime',
            'next_billing_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySubscriptionId($query, string $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function markAsActive(): void
    {
        $this->update([
            'status'       => 'active',
            'activated_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status'      => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status'         => 'failed',
            'error_message'  => $errorMessage,
        ]);
    }
}
