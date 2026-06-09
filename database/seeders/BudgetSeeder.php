<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder: BudgetSeeder
 *
 * Setiap user mendapat budget untuk semua kategori.
 * Budget dibuat untuk bulan ini dan 2 bulan sebelumnya
 * (untuk keperluan laporan historis dan testing survive mode).
 *
 * Daily limit yang realistis (dalam Rupiah):
 * - want: 50.000 - 150.000/hari
 * - need: 100.000 - 200.000/hari
 * - saving: 30.000 - 100.000/hari (target saving harian)
 * - investment: 50.000 - 200.000/hari (target investasi harian)
 */
class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $users      = User::all();
        $categories = Category::all();

        // Budget untuk 3 bulan: 2 bulan lalu, 1 bulan lalu, bulan ini
        $periods = [
            ['month' => Carbon::now()->subMonths(2)->month, 'year' => Carbon::now()->subMonths(2)->year],
            ['month' => Carbon::now()->subMonths(1)->month, 'year' => Carbon::now()->subMonths(1)->year],
            ['month' => Carbon::now()->month,               'year' => Carbon::now()->year],
        ];

        // Daily limit ranges per tipe kategori (dalam Rupiah)
        $dailyLimitRanges = [
            'want'       => [30000, 150000],
            'need'       => [80000, 250000],
            'saving'     => [30000, 100000],
            'investment' => [50000, 150000],
        ];

        $count = 0;

        foreach ($users as $user) {
            foreach ($periods as $period) {
                foreach ($categories as $category) {
                    [$min, $max] = $dailyLimitRanges[$category->type];

                    // Bulatkan ke puluhan ribu terdekat (lebih realistis)
                    $rawLimit  = rand($min / 10000, $max / 10000) * 10000;
                    $dailyLimit = max($min, min($max, $rawLimit));

                    Budget::updateOrCreate(
                        [
                            'user_id'     => $user->id,
                            'category_id' => $category->id,
                            'month'       => $period['month'],
                            'year'        => $period['year'],
                        ],
                        [
                            'daily_limit' => $dailyLimit,
                        ]
                    );

                    $count++;
                }
            }
        }

        $this->command->info("✅ BudgetSeeder: {$count} budget records created (5 users × 10 categories × 3 months).");
    }
}
