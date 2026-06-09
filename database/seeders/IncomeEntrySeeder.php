<?php

namespace Database\Seeders;

use App\Models\IncomeEntry;
use App\Models\IncomeSource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder: IncomeEntrySeeder
 *
 * Min 50 rows income tersebar dalam 3 bulan terakhir.
 * Mix status ~70% verified, 30% unverified/pending.
 * Nominal realistis Indonesia: 500.000 - 8.000.000 per entri.
 *
 * POLA YANG DISIMULASIKAN:
 * - Setiap user mendapat 1-2 income utama per bulan (gaji)
 * - Plus beberapa income tambahan (freelance, bonus)
 * - Tersebar di 3 bulan terakhir
 */
class IncomeEntrySeeder extends Seeder
{
    public function run(): void
    {
        $users   = User::all();
        $sources = IncomeSource::all()->keyBy('name');

        $gajiSource      = $sources['Gaji'];
        $freelanceSource = $sources['Freelance'];
        $bonusSource     = $sources['Bonus'];
        $investSource    = $sources['Investasi Cair'];
        $lainSource      = $sources['Lainnya'];

        // Data income realistis per user (simulasi 3 bulan)
        $incomeData = [];

        foreach ($users as $user) {
            for ($monthOffset = 0; $monthOffset <= 2; $monthOffset++) {
                $baseDate = Carbon::now()->subMonths($monthOffset);

                // Gaji pokok tiap bulan (1 entry per bulan)
                $gajiNominal = $this->randomGaji();
                $incomeData[] = [
                    'user_id'         => $user->id,
                    'source_id'       => $gajiSource->id,
                    'amount'          => $gajiNominal,
                    'date'            => $baseDate->copy()->setDay(rand(1, 5))->format('Y-m-d'),
                    'verified_status' => 'verified',
                    'verification_id' => null,
                    'note'            => 'Gaji ' . $baseDate->format('F Y'),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                // Freelance (50% kemungkinan per bulan)
                if (rand(0, 1)) {
                    $incomeData[] = [
                        'user_id'         => $user->id,
                        'source_id'       => $freelanceSource->id,
                        'amount'          => rand(50, 300) * 10000,
                        'date'            => $baseDate->copy()->setDay(rand(8, 20))->format('Y-m-d'),
                        'verified_status' => rand(0, 9) < 7 ? 'verified' : 'pending',
                        'verification_id' => null,
                        'note'            => 'Project freelance ' . $baseDate->format('F'),
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                // Bonus (30% kemungkinan, biasanya bulan tertentu)
                if (rand(0, 9) < 3) {
                    $incomeData[] = [
                        'user_id'         => $user->id,
                        'source_id'       => $bonusSource->id,
                        'amount'          => rand(50, 200) * 10000,
                        'date'            => $baseDate->copy()->setDay(rand(10, 25))->format('Y-m-d'),
                        'verified_status' => rand(0, 9) < 6 ? 'verified' : 'unverified',
                        'verification_id' => null,
                        'note'            => 'Bonus kinerja',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }

                // Usaha sampingan / lainnya (20% kemungkinan)
                if (rand(0, 9) < 2) {
                    $incomeData[] = [
                        'user_id'         => $user->id,
                        'source_id'       => $lainSource->id,
                        'amount'          => rand(20, 100) * 10000,
                        'date'            => $baseDate->copy()->setDay(rand(5, 28))->format('Y-m-d'),
                        'verified_status' => 'unverified',
                        'verification_id' => null,
                        'note'            => 'Pendapatan sampingan',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }
        }

        // Insert dalam batch untuk performa
        $chunks = array_chunk($incomeData, 50);
        foreach ($chunks as $chunk) {
            IncomeEntry::insert($chunk);
        }

        $total = count($incomeData);
        $this->command->info("✅ IncomeEntrySeeder: {$total} income entries created.");
    }

    /**
     * Generate nominal gaji realistis Indonesia (3jt - 8jt)
     * Dibulatkan ke 100rb terdekat.
     */
    private function randomGaji(): int
    {
        $gajiOptions = [
            3000000, 3500000, 4000000, 4500000,
            5000000, 5500000, 6000000, 6500000,
            7000000, 7500000, 8000000,
        ];

        return $gajiOptions[array_rand($gajiOptions)];
    }
}
