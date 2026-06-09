<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder: UserBadgeSeeder
 *
 * Mengisi tabel pivot M-N user_badges.
 * Setiap user mendapat badge sesuai best_streak-nya.
 *
 * ATURAN BISNIS:
 * - Badge diberikan berdasarkan best_streak, bukan current_streak
 * - Badge bersifat PERMANEN (tidak bisa dicabut)
 * - earned_at disimulasikan sebagai hari dimana streak milestone tercapai
 *
 * RELASI M-N YANG TERBENTUK:
 * Budi (streak 21)   → STARTER, SMART, MASTER
 * Siti (streak 14)   → STARTER, SMART, MASTER
 * Ahmad (streak 10)  → STARTER, SMART
 * Dewi (streak 7)    → STARTER, SMART
 * Rudi (streak 62)   → STARTER, SMART, MASTER, ELITE, LEGEND
 *
 * Total: berbagai kombinasi user ↔ badge = bukti relasi M-N
 */
class UserBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $users  = User::all();
        $badges = Badge::all()->keyBy('badge_key');

        $milestoneMap = [
            'STARTER' => 3,
            'SMART'   => 7,
            'MASTER'  => 14,
            'ELITE'   => 30,
            'LEGEND'  => 60,
        ];

        $count = 0;

        foreach ($users as $user) {
            foreach ($milestoneMap as $badgeKey => $requiredStreak) {
                // User mendapat badge ini jika best_streak-nya >= required
                if ($user->best_streak >= $requiredStreak) {
                    $badge = $badges->get($badgeKey);

                    if (!$badge) {
                        continue;
                    }

                    // Hitung earned_at: estimasi kapan user mencapai streak milestone
                    // Asumsi: streaknya dicapai $requiredStreak hari sebelum sekarang
                    // (dengan sedikit variasi untuk lebih natural)
                    $daysAgo  = $user->best_streak - $requiredStreak + rand(1, 5);
                    $earnedAt = Carbon::now()->subDays($daysAgo);

                    // updateOrCreate untuk avoid duplicate jika seeder dijalankan ulang
                    $user->badges()->syncWithoutDetaching([
                        $badge->id => [
                            'earned_at'  => $earnedAt,
                            'created_at' => $earnedAt,
                            'updated_at' => $earnedAt,
                        ],
                    ]);

                    $count++;
                }
            }
        }

        // Tampilkan summary relasi M-N yang terbentuk
        $this->command->info("✅ UserBadgeSeeder: {$count} user-badge M-N relationships created.");
        $this->command->line('');
        $this->command->line('   M-N Relationship Summary:');

        foreach ($users as $user) {
            $badgeList = $user->badges()->pluck('badge_key')->join(', ');
            $this->command->line("   {$user->name} (streak: {$user->best_streak}) → [{$badgeList}]");
        }
    }
}
