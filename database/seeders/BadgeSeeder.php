<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'badge_key'        => 'STARTER',
                'name'             => 'Starter Saver',
                'description'      => 'Berhasil menjaga pengeluaran di bawah 75% budget selama 3 hari berturut-turut. Awal yang bagus!',
                'streak_required'  => 3,
                'icon'             => '🌱',
            ],
            [
                'badge_key'        => 'SMART',
                'name'             => 'Smart Spender',
                'description'      => 'Konsisten hemat selama 7 hari penuh. Kebiasaan finansial yang baik mulai terbentuk.',
                'streak_required'  => 7,
                'icon'             => '⚡',
            ],
            [
                'badge_key'        => 'MASTER',
                'name'             => 'Budget Master',
                'description'      => 'Dua minggu penuh disiplin finansial. Kamu sudah membuktikan bahwa konsistensi adalah kunci.',
                'streak_required'  => 14,
                'icon'             => '🏅',
            ],
            [
                'badge_key'        => 'ELITE',
                'name'             => 'Financial Discipline',
                'description'      => 'Satu bulan penuh pengelolaan keuangan yang luar biasa. Elite level achievement!',
                'streak_required'  => 30,
                'icon'             => '🏆',
            ],
            [
                'badge_key'        => 'LEGEND',
                'name'             => 'Money Legend',
                'description'      => '60 hari streak! Kamu adalah legenda finansial. Pencapaian jangka panjang yang luar biasa.',
                'streak_required'  => 60,
                'icon'             => '👑',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['badge_key' => $badge['badge_key']],
                $badge
            );
        }

        $this->command->info('✅ BadgeSeeder: 5 badges created.');
    }
}
