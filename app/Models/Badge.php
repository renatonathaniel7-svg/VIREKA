<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Badge Model — Master data badge yang tersedia.
 *
 * Badge bersifat permanen — tidak hilang meskipun streak direset.
 * Relasi M-N dengan users via tabel pivot user_badges.
 * Ini memenuhi syarat relasi M-N pada persyaratan TA.
 *
 * Milestone default:
 *   starter_saver      → 3  hari streak
 *   smart_spender      → 7  hari streak
 *   budget_master      → 14 hari streak
 *   financial_discipline→ 30 hari streak
 *   money_legend       → 60 hari streak
 */
class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',          // unique identifier: 'starter_saver', 'money_legend'
        'name',          // Display name: 'Starter Saver'
        'description',   // Deskripsi singkat badge
        'icon',          // Emoji atau icon class
        'required_streak', // Berapa hari streak dibutuhkan
        'tier',          // bronze | silver | gold | platinum | legendary
    ];

    // ── M-N Relationship ─────────────────────────────────────────────────────
    /**
     * Users yang sudah mendapatkan badge ini.
     * Ini adalah sisi lain dari M-N relationship.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot(['earned_at', 'streak_at_earn'])
            ->withTimestamps();
    }
}
