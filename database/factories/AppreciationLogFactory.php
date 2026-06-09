<?php

namespace Database\Factories;

use App\Models\AppreciationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: AppreciationLogFactory
 */
class AppreciationLogFactory extends Factory
{
    protected $model = AppreciationLog::class;

    private array $appreciationMessages = [
        'Luar biasa! Pengeluaran hari ini hanya {pct}% dari budget. Terus pertahankan! 🎉',
        'Hebat! Kamu berhasil hemat hari ini. Streak kamu makin kuat! 💪',
        'Excellent day! Pengeluaran sangat terkontrol — kamu di jalur yang benar! ✨',
    ];

    private array $warningMessages = [
        'Hati-hati! Pengeluaran hari ini sudah {pct}% dari budget harian.',
        'Peringatan: Budget hampir habis. Pertimbangkan mengurangi pengeluaran.',
        'Alert: Spending melebihi threshold. Streak terancam!',
    ];

    public function definition(): array
    {
        $type         = $this->faker->randomElement([
            'daily_appreciation', 'daily_appreciation',  // ~40%
            'daily_warning',                              // ~20%
            'streak_badge',                               // ~20%
            'monthly_summary',                            // ~15%
            'income_growth',                              // ~5%
        ]);

        $streakCount  = $this->faker->numberBetween(0, 30);
        $triggerValue = match ($type) {
            'daily_appreciation' => $this->faker->randomFloat(1, 10, 49),
            'daily_warning'      => $this->faker->randomFloat(1, 75, 120),
            'streak_badge'       => (float) $streakCount,
            'monthly_summary'    => $this->faker->randomFloat(1, 30, 95),
            'income_growth'      => $this->faker->randomFloat(1, 5, 40),
            default              => null,
        };

        $badgeEarned = $type === 'streak_badge'
            ? $this->faker->randomElement(['STARTER', 'SMART', 'MASTER'])
            : null;

        $message = match ($type) {
            'daily_appreciation' => 'Luar biasa! Pengeluaran hari ini hanya ' . round($triggerValue, 1) . '% dari budget. Pertahankan! 🎉',
            'daily_warning'      => 'Hati-hati! Pengeluaran hari ini sudah ' . round($triggerValue, 1) . '% dari budget harian.',
            'streak_badge'       => 'Selamat! Kamu mendapat badge ' . ($badgeEarned ?? 'STARTER') . ' dengan streak ' . $streakCount . ' hari! 🏆',
            'monthly_summary'    => 'Laporan Bulanan: Financial Health Score kamu adalah ' . round($triggerValue, 1) . '. ' . ($triggerValue >= 70 ? 'Kerja bagus! 🌟' : 'Ada ruang untuk improvement. 💡'),
            'income_growth'      => 'Income Growth Alert: Pendapatan bulan ini tumbuh ' . round($triggerValue, 1) . '% dari bulan lalu! 📈',
            default              => 'Notifikasi sistem.',
        };

        return [
            'user_id'       => User::factory(),
            'type'          => $type,
            'trigger_value' => $triggerValue,
            'streak_count'  => $streakCount,
            'badge_earned'  => $badgeEarned,
            'message'       => $message,
            'created_at'    => now()->subDays($this->faker->numberBetween(1, 60)),
            'updated_at'    => now()->subDays($this->faker->numberBetween(1, 60)),
        ];
    }
}
