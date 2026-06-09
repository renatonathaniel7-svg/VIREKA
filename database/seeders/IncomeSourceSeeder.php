<?php

namespace Database\Seeders;

use App\Models\IncomeSource;
use Illuminate\Database\Seeder;

/**
 * Seeder: IncomeSourceSeeder
 *
 * Master data sumber income — shared resource untuk semua user.
 * Gunakan updateOrCreate agar aman di-re-run.
 */
class IncomeSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'name'        => 'Gaji',
                'description' => 'Pendapatan tetap bulanan dari pekerjaan utama',
            ],
            [
                'name'        => 'Freelance',
                'description' => 'Pendapatan dari pekerjaan sampingan, proyek, atau konsultasi',
            ],
            [
                'name'        => 'Bonus',
                'description' => 'Bonus kinerja, THR, atau insentif dari perusahaan',
            ],
            [
                'name'        => 'Investasi Cair',
                'description' => 'Hasil pencairan investasi, dividen, atau keuntungan trading',
            ],
            [
                'name'        => 'Usaha Sampingan',
                'description' => 'Pendapatan dari bisnis kecil, jualan online, dll',
            ],
            [
                'name'        => 'Lainnya',
                'description' => 'Sumber pendapatan lain yang tidak termasuk kategori di atas',
            ],
        ];

        foreach ($sources as $source) {
            IncomeSource::updateOrCreate(
                ['name' => $source['name']],
                $source
            );
        }

        $this->command->info('✅ IncomeSourceSeeder: 6 income sources created.');
    }
}
