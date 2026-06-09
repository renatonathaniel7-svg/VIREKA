<?php

namespace Database\Seeders;

use App\Models\InvestmentEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder: InvestmentSeeder
 *
 * Min 20 investment entries tersebar di semua user.
 * Instrumen: Emas, Reksa Dana, Saham, Deposito, ORI, SBR.
 *
 * Mix allocation_type: saving dan investment.
 * Simulasi return: sebagian profit (+5% s/d +25%), sebagian loss (-10% s/d -2%).
 */
class InvestmentSeeder extends Seeder
{
    private array $savingInstruments = [
        'Tabungan Berjangka BCA',
        'Tabungan Berjangka BRI',
        'Tabungan Berjangka Mandiri',
        'Deposito 3 Bulan BNI',
        'Deposito 6 Bulan BCA',
    ];

    private array $investmentInstruments = [
        'Emas Antam 1gr',
        'Emas Antam 5gr',
        'Reksa Dana Pasar Uang - Bibit',
        'Reksa Dana Saham - Bareksa',
        'Reksa Dana Pendapatan Tetap - Tokopedia',
        'Saham BBCA',
        'Saham BBRI',
        'Saham TLKM',
        'ORI023 (Obligasi Ritel)',
        'SBR012 (Saving Bond Ritel)',
    ];

    private array $notes = [
        'Dana darurat jangka menengah',
        'Investasi untuk DP rumah 3 tahun lagi',
        'Portofolio jangka panjang',
        'Diversifikasi aset',
        'Dana pendidikan anak',
        'Passive income stream',
        'Target: double in 5 years',
        null,
    ];

    public function run(): void
    {
        $users = User::all();
        $count = 0;

        // Data investment yang telah didesain (bukan random murni)
        // untuk cerita yang lebih koheren di demo
        $investmentPlan = [
            // Budi Santoso (user normal, cukup investasi)
            ['type' => 'saving',     'instrument_key' => 'saving',     'amount' => 2000000,  'return' =>  2.5, 'note' => 'Dana darurat'],
            ['type' => 'investment', 'instrument_key' => 'emas',        'amount' => 1000000,  'return' =>  8.3, 'note' => 'Emas Antam 1gr'],
            ['type' => 'investment', 'instrument_key' => 'reksadana',   'amount' => 1500000,  'return' =>  12.4,'note' => 'Reksa dana saham'],
            ['type' => 'saving',     'instrument_key' => 'deposito',    'amount' => 3000000,  'return' =>  3.5, 'note' => 'Deposito 6 bulan'],

            // Siti Rahma (caution mode, investasi lebih kecil)
            ['type' => 'saving',     'instrument_key' => 'saving',     'amount' => 1000000,  'return' =>  2.0, 'note' => 'Dana darurat'],
            ['type' => 'investment', 'instrument_key' => 'reksadana',   'amount' => 500000,   'return' =>  5.2, 'note' => 'Reksa dana pasar uang'],

            // Ahmad Fauzi (survive mode, sedikit investasi)
            ['type' => 'saving',     'instrument_key' => 'saving',     'amount' => 800000,   'return' =>  1.5, 'note' => 'Tabungan darurat'],
            ['type' => 'investment', 'instrument_key' => 'emas',        'amount' => 500000,   'return' => -2.1, 'note' => 'Emas Antam (sedikit turun)'],

            // Dewi Kartika (critical mode, perlu withdrawal suggestion)
            ['type' => 'saving',     'instrument_key' => 'deposito',    'amount' => 2000000,  'return' =>  3.0, 'note' => 'Deposito yang bisa dicairkan'],
            ['type' => 'investment', 'instrument_key' => 'saham',       'amount' => 1500000,  'return' => -8.5, 'note' => 'Saham BBRI (sedang turun)'],
            ['type' => 'investment', 'instrument_key' => 'reksadana',   'amount' => 750000,   'return' =>  4.2, 'note' => 'Reksa dana pasar uang'],

            // Rudi Hermawan (legend, investasi terbesar dan beragam)
            ['type' => 'saving',     'instrument_key' => 'saving',     'amount' => 5000000,  'return' =>  3.2, 'note' => 'Dana darurat 6 bulan'],
            ['type' => 'investment', 'instrument_key' => 'emas',        'amount' => 3000000,  'return' =>  15.7,'note' => 'Emas Antam 5gr'],
            ['type' => 'investment', 'instrument_key' => 'saham',       'amount' => 4000000,  'return' =>  22.3,'note' => 'Portofolio saham blue chip'],
            ['type' => 'investment', 'instrument_key' => 'reksadana',   'amount' => 2500000,  'return' =>  18.1,'note' => 'Reksa dana campuran'],
            ['type' => 'investment', 'instrument_key' => 'obligasi',    'amount' => 2000000,  'return' =>  7.5, 'note' => 'ORI023'],
            ['type' => 'investment', 'instrument_key' => 'obligasi',    'amount' => 1500000,  'return' =>  6.8, 'note' => 'SBR012'],
            ['type' => 'saving',     'instrument_key' => 'deposito',    'amount' => 5000000,  'return' =>  4.0, 'note' => 'Deposito 12 bulan'],
            ['type' => 'investment', 'instrument_key' => 'emas',        'amount' => 1000000,  'return' =>  12.0,'note' => 'Emas digital Tokomas'],
            ['type' => 'investment', 'instrument_key' => 'saham',       'amount' => 3000000,  'return' =>  -3.2,'note' => 'Saham TLKM (sedikit turun)'],
        ];

        // Map user ke plan berdasarkan urutan (5 user × data di atas)
        $userList = $users->values();

        // Mapping plan ke user berdasarkan index kelompok
        $userPlanMap = [
            0 => [0, 1, 2, 3],         // Budi: index 0-3
            1 => [4, 5],               // Siti: index 4-5
            2 => [6, 7],               // Ahmad: index 6-7
            3 => [8, 9, 10],           // Dewi: index 8-10
            4 => [11, 12, 13, 14, 15, 16, 17, 18, 19], // Rudi: index 11-19
        ];

        foreach ($userPlanMap as $userIndex => $planIndexes) {
            if (!isset($userList[$userIndex])) {
                continue;
            }

            $user = $userList[$userIndex];

            foreach ($planIndexes as $planIdx) {
                $plan = $investmentPlan[$planIdx];

                $instrument = $this->resolveInstrument($plan['instrument_key']);
                $initial    = $plan['amount'];
                $returnPct  = $plan['return'];
                $current    = round($initial * (1 + $returnPct / 100), 2);

                InvestmentEntry::create([
                    'user_id'         => $user->id,
                    'allocation_type' => $plan['type'],
                    'instrument'      => $instrument,
                    'initial_amount'  => $initial,
                    'current_value'   => $current,
                    'return_pct'      => $returnPct,
                    'note'            => $plan['note'],
                ]);

                $count++;
            }
        }

        $this->command->info("✅ InvestmentSeeder: {$count} investment entries created.");
    }

    private function resolveInstrument(string $key): string
    {
        return match ($key) {
            'saving'    => $this->savingInstruments[array_rand($this->savingInstruments)],
            'emas'      => 'Emas Antam',
            'reksadana' => $this->investmentInstruments[array_rand(array_filter($this->investmentInstruments, fn($i) => str_contains($i, 'Reksa')))],
            'saham'     => $this->investmentInstruments[array_rand(array_filter($this->investmentInstruments, fn($i) => str_contains($i, 'Saham')))],
            'deposito'  => $this->savingInstruments[array_rand(array_filter($this->savingInstruments, fn($i) => str_contains($i, 'Deposito')))],
            'obligasi'  => $this->investmentInstruments[array_rand(array_filter($this->investmentInstruments, fn($i) => str_contains($i, 'ORI') || str_contains($i, 'SBR')))],
            default     => 'Lainnya',
        };
    }
}
