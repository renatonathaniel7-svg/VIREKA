<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UserBadge — Pivot Model untuk relasi M-N users ↔ badges.
 *
 * Relasi Many-to-Many ini memenuhi syarat TA:
 *   "ada relasi 1-N dan M-N"
 *
 * Satu user bisa punya banyak badge.
 * Satu badge bisa dimiliki banyak user.
 * → M-N via tabel pivot user_badges.
 *
 * Extra data di pivot:
 *   - earned_at      : kapan badge diraih
 *   - streak_at_earn : berapa streak saat badge diraih (historis)
 *
 * Badge bersifat PERMANEN. Sekali earned, tidak bisa di-unearned.
 * Ini adalah design decision untuk motivasi jangka panjang.
 */
class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badges';

    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        'streak_at_earn',
    ];

    protected $casts = [
        'earned_at'      => 'datetime',
        'streak_at_earn' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
