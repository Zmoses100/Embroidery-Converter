<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionUsage extends Model
{
    protected $fillable = ['user_id', 'date', 'count'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment today's conversion count for a user.
     */
    public static function incrementForUser(int $userId): void
    {
        static::firstOrCreate(
            ['user_id' => $userId, 'date' => today()->toDateString()],
            ['count'   => 0]
        );

        static::where('user_id', $userId)
            ->where('date', today()->toDateString())
            ->increment('count');
    }
}
