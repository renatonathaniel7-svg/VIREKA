<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AppreciationLog Model
 *
 * Mencatat semua event behavioral:
 *   - daily_appreciation: penghargaan hari hemat
 *   - daily_warning: peringatan overspend atau survive mode
 *   - streak_badge: badge milestone yang diraih
 *   - monthly_summary: ringkasan bulanan health score
 *
 * Log ini menjadi basis notifikasi dan riwayat perilaku keuangan user.
 * Tidak boleh dihapus — ini adalah audit trail behavioral.
 */
class AppreciationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',           // daily_warning | daily_appreciation | streak_badge | monthly_summary
        'trigger_value',  // nilai yang memicu (e.g. spending_pct = 68.3)
        'streak_count',   // streak saat log dibuat
        'badge_earned',   // nama badge jika ada (nullable)
        'message',        // pesan yang ditampilkan ke user
    ];

    protected $casts = [
        'trigger_value' => 'float',
        'streak_count'  => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Local Scopes ─────────────────────────────────────────────────────────
    public function scopeForUser(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
