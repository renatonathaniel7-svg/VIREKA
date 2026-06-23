<?php

namespace Database\Seeders;

use App\Models\AppreciationLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AppreciationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            // ── Daily logs: 2 minggu terakhir ──────────────────────
            for ($dayOffset = 1; $dayOffset <= 14; $dayOffset++) {
                $date       = Carbon::now()->subDays($dayOffset);
                $spendingPct = rand(20, 110); // Simulasi variasi spending %

                if ($spendingPct >= 75) {
                    // WARNING log
                    AppreciationLog::create([
                        'user_id'       => $user->id,
                        'type'          => 'daily_warning',
                        'trigger_value' => $spendingPct,
                        'streak_count'  => max(0, $user->current_streak - ($dayOffset > 7 ? rand(1, 5) : 0)),
                        'badge_earned'  => null,
                        'message'       => "⚠️ Pengeluaran hari ini sudah {$spendingPct}% dari budget harian. Pertimbangkan mengurangi pengeluaran.",
                        'created_at'    => $date,
                        'updated_at'    => $date,
                    ]);
                } elseif ($spendingPct < 50) {
                    // APPRECIATION log
                    AppreciationLog::create([
                        'user_id'       => $user->id,
                        'type'          => 'daily_appreciation',
                        'trigger_value' => $spendingPct,
                        'streak_count'  => $user->current_streak,
                        'badge_earned'  => null,
                        'message'       => "🎉 Luar biasa! Pengeluaran hari ini hanya {$spendingPct}% dari budget. Streak kamu bertambah!",
                        'created_at'    => $date,
                        'updated_at'    => $date,
                    ]);
                }

                $count++;
            }

            // ── Monthly Summary: 2 bulan terakhir ──────────────────
            for ($monthOffset = 1; $monthOffset <= 2; $monthOffset++) {
                $monthDate = Carbon::now()->subMonths($monthOffset)->endOfMonth();
                $score     = rand(35, 88);
                $tier      = $this->getTier($score);

                AppreciationLog::create([
                    'user_id'       => $user->id,
                    'type'          => 'monthly_summary',
                    'trigger_value' => $score,
                    'streak_count'  => $user->best_streak,
                    'badge_earned'  => null,
                    'message'       => "📊 Laporan Bulanan {$monthDate->format('F Y')}: Financial Health Score kamu adalah {$score}/100 (Tier {$tier}). BAR: " . rand(50,90) . "% | SR: " . rand(10,35) . "% | SC: " . rand(20,80) . "%",
                    'created_at'    => $monthDate,
                    'updated_at'    => $monthDate,
                ]);

                $count++;
            }

            // ── Streak Badge logs: berdasarkan best_streak user ──────
            $milestones = [
                3  => 'STARTER',
                7  => 'SMART',
                14 => 'MASTER',
                30 => 'ELITE',
                60 => 'LEGEND',
            ];

            foreach ($milestones as $streak => $badgeKey) {
                if ($user->best_streak >= $streak) {
                    $daysAgo = max(1, $user->best_streak - $streak + rand(1, 10));
                    $badgeDate = Carbon::now()->subDays($daysAgo);

                    $badgeNames = [
                        'STARTER' => 'Starter Saver',
                        'SMART'   => 'Smart Spender',
                        'MASTER'  => 'Budget Master',
                        'ELITE'   => 'Financial Discipline',
                        'LEGEND'  => 'Money Legend',
                    ];

                    AppreciationLog::create([
                        'user_id'       => $user->id,
                        'type'          => 'streak_badge',
                        'trigger_value' => (float) $streak,
                        'streak_count'  => $streak,
                        'badge_earned'  => $badgeKey,
                        'message'       => "🏆 Selamat! Kamu mendapat badge '{$badgeNames[$badgeKey]}' dengan streak {$streak} hari berturut-turut!",
                        'created_at'    => $badgeDate,
                        'updated_at'    => $badgeDate,
                    ]);

                    $count++;
                }
            }

            // ── Income Growth log (jika applicable) ─────────────────
            if (rand(0, 1)) {
                $growthPct = rand(5, 35);
                AppreciationLog::create([
                    'user_id'       => $user->id,
                    'type'          => 'income_growth',
                    'trigger_value' => (float) $growthPct,
                    'streak_count'  => $user->current_streak,
                    'badge_earned'  => null,
                    'message'       => "📈 Income Growth! Pendapatan bulan ini tumbuh {$growthPct}% dibanding bulan lalu. Pertahankan!",
                    'created_at'    => Carbon::now()->subDays(rand(1, 5)),
                    'updated_at'    => Carbon::now()->subDays(rand(1, 5)),
                ]);

                $count++;
            }
        }

        $this->command->info("✅ AppreciationSeeder: {$count} appreciation logs created.");
    }

    private function getTier(int $score): string
    {
        return match (true) {
            $score >= 85 => 'S',
            $score >= 70 => 'A',
            $score >= 55 => 'B',
            $score >= 40 => 'C',
            default      => 'D',
        };
    }
}
