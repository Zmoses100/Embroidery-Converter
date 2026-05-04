<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionUsage extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'count',
    ];

    protected $casts = [
        'date' => 'datetime',
        'count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment today's conversion count for a user.
     * SQLite stores date values as datetime strings, so we use the same value
     * for lookup and insert to avoid duplicate unique-key errors.
     */
    public static function incrementForUser(int $userId): void
    {
        $date = today()->startOfDay()->toDateTimeString();

        $usage = static::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (! $usage) {
            $usage = static::create([
                'user_id' => $userId,
                'date' => $date,
                'count' => 0,
            ]);
        }

        $usage->increment('count');
    }

    /**
     * Get today's conversion count for a user.
     */
    public static function todayCountForUser(int $userId): int
    {
        $date = today()->startOfDay()->toDateTimeString();

        return (int) static::where('user_id', $userId)
            ->where('date', $date)
            ->value('count');
    }
}