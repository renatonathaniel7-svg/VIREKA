<?php

namespace App\Services;

use App\Models\AppreciationLog;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * BadgeService
 *
 * WHY SEPARATE FROM STREAKSERVICE:
 * Badge logic adalah domain tersendiri dengan rule-set yang berbeda.
 * Memisahkannya memungkinkan:
 * 1. BadgeService bisa dipanggil dari konteks selain streak (future: income goals, dll)
 * 2. Testing badge logic secara independen
 * 3. Mudah menambah badge type baru tanpa menyentuh StreakService
 *
 * IDEMPOTENCY DESIGN:
 * checkAndAward() dirancang idempotent — memanggil berkali-kali untuk streak
 * yang sama TIDAK akan double-award. Ini dicapai melalui:
 * 1. Exact match streak === milestone (bukan >=)
 * 2. exists() check sebelum attach()
 * 3. attach() bukan sync() — sync() akan menghapus badge yang ada
 *
 * BADGE PERMANENCE:
 * Badge TIDAK PERNAH dihapus, bahkan ketika streak di-reset.
 * Ini adalah keputusan desain untuk:
 * - Mempertahankan motivasi jangka panjang
 * - Menghargai pencapaian historis
 * - Mencegah frustasi berlebihan saat streak reset
 */
class BadgeService
{
    /**
     * Milestones: streak_required => badge_key
     * Sesuai dengan data yang di-seed di tabel badges
     */
    private const MILESTONES = [
        3  => 'STARTER',
        7  => 'SMART',
        14 => 'MASTER',
        30 => 'ELITE',
        60 => 'LEGEND',
    ];

    /**
     * Cek apakah user layak mendapat badge baru berdasarkan current_streak.
     * Dipanggil setiap kali streak diperbarui oleh StreakService.
     *
     * LOGIC: Award hanya ketika current_streak TEPAT SAMA dengan milestone.
     * Contoh: streak 7 → award SMART badge. Streak 8 → tidak award lagi.
     * Ini mencegah double-award saat streak terus naik.
     *
     * Namun ada satu edge case: jika user mendapat badge, lalu streak reset,
     * lalu naik ke milestone yang sama lagi → tidak akan dapat badge ganda
     * karena alreadyEarned check melihat ALL-TIME, bukan hanya current streak.
     *
     * @param User $user User yang sudah di-save dengan current_streak terbaru
     */
    public function checkAndAward(User $user): void
    {
        foreach (self::MILESTONES as $streakRequired => $badgeKey) {
            // Hanya award saat streak TEPAT mencapai milestone
            // Bukan >= agar tidak trigger setiap hari setelah milestone tercapai
            if ($user->current_streak !== $streakRequired) {
                continue;
            }

            $badge = Badge::where('badge_key', $badgeKey)->first();

            if (!$badge) {
                // Badge key tidak ada di DB → log warning dan skip
                Log::warning("BadgeService: Badge key '{$badgeKey}' not found in badges table.");
                continue;
            }

            // Cek apakah user SUDAH PERNAH mendapat badge ini (all-time)
            // Menggunakan Eloquent relationship: User hasMany UserBadge
            $alreadyEarned = $user->badges()
                ->where('badge_id', $badge->id)
                ->exists();

            if ($alreadyEarned) {
                // Sudah pernah dapat — ini adalah re-achievement, tidak award ulang
                continue;
            }

            // ── Award badge ──
            // WAJIB gunakan attach() bukan sync() karena:
            // sync() akan menghapus badge-badge lain yang sudah earned
            $user->badges()->attach($badge->id, [
                'earned_at' => now(),
            ]);

            // Log ke appreciation_logs sebagai notifikasi in-app
            AppreciationLog::create([
                'user_id'       => $user->id,
                'type'          => 'streak_badge',
                'trigger_value' => null,
                'streak_count'  => $user->current_streak,
                'badge_earned'  => $badgeKey,
                'message'       => "Badge baru diraih: {$badge->name}! 🎉 Kamu berhasil mencapai streak {$streakRequired} hari!",
            ]);

            Log::info("BadgeService: User {$user->id} earned badge '{$badgeKey}' at streak {$streakRequired}.");
        }
    }

    /**
     * Ambil semua badge dengan status earned/locked untuk user tertentu.
     * Digunakan oleh BadgeController untuk halaman badges/index.
     *
     * @param User $user
     * @return \Illuminate\Support\Collection Badge dengan property earned dan earned_at
     */
    public function getBadgesWithStatus(User $user): \Illuminate\Support\Collection
    {
        // Ambil semua badge master data, urutkan berdasarkan streak_required
        $allBadges = Badge::orderBy('streak_required')->get();

        // Ambil badge yang sudah diraih user (pivot data)
        $earnedBadges = $user->badges()
            ->withPivot('earned_at')
            ->get()
            ->keyBy('id');

        // Map tiap badge dengan status earned/locked
        return $allBadges->map(function (Badge $badge) use ($earnedBadges, $user) {
            $isEarned = $earnedBadges->has($badge->id);
            $earnedAt = $isEarned ? $earnedBadges->get($badge->id)->pivot->earned_at : null;

            // Badge locked tapi streak sudah melampaui — tidak akan pernah earned
            // (hanya terjadi jika badge ditambahkan setelah user melampaui milestone)
            $isReachable = !$isEarned && ($user->best_streak < $badge->streak_required);
            $isPermanentlyMissed = !$isEarned && ($user->best_streak >= $badge->streak_required);

            return (object) [
                'id'                  => $badge->id,
                'badge_key'           => $badge->badge_key,
                'name'                => $badge->name,
                'description'         => $badge->description,
                'icon'                => $badge->icon,
                'streak_required'     => $badge->streak_required,
                'earned'              => $isEarned,
                'earned_at'           => $earnedAt,
                'is_reachable'        => $isReachable,
                'is_permanently_missed' => $isPermanentlyMissed,
                'remaining_streak'    => $isEarned ? 0 : max(0, $badge->streak_required - $user->current_streak),
            ];
        });
    }

    /**
     * Seed data badges ke tabel badges.
     * Dipanggil dari DatabaseSeeder atau bisa dijadikan Artisan command.
     * Menggunakan updateOrCreate untuk idempotency.
     */
    public function seedBadges(): void
    {
        $badgeData = [
            [
                'badge_key'       => 'STARTER',
                'name'            => 'Starter Saver',
                'description'     => 'Berhasil menjaga pengeluaran di bawah 75% budget selama 3 hari berturut-turut.',
                'icon'            => '🥉',
                'streak_required' => 3,
            ],
            [
                'badge_key'       => 'SMART',
                'name'            => 'Smart Spender',
                'description'     => 'Konsisten hemat selama seminggu penuh. Keuanganmu mulai teratur!',
                'icon'            => '🥈',
                'streak_required' => 7,
            ],
            [
                'badge_key'       => 'MASTER',
                'name'            => 'Budget Master',
                'description'     => 'Dua minggu pengelolaan keuangan yang disiplin. Kamu menguasai anggaran!',
                'icon'            => '🥇',
                'streak_required' => 14,
            ],
            [
                'badge_key'       => 'ELITE',
                'name'            => 'Financial Discipline',
                'description'     => 'Sebulan penuh konsistensi. Level disiplin finansial yang langka!',
                'icon'            => '💎',
                'streak_required' => 30,
            ],
            [
                'badge_key'       => 'LEGEND',
                'name'            => 'Money Legend',
                'description'     => 'Dua bulan tanpa berhenti. Kamu adalah legenda pengelolaan keuangan!',
                'icon'            => '🏆',
                'streak_required' => 60,
            ],
        ];

        foreach ($badgeData as $data) {
            Badge::updateOrCreate(
                ['badge_key' => $data['badge_key']],
                $data
            );
        }
    }
}
