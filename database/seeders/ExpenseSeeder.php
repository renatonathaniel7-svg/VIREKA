<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExpenseSeeder extends Seeder
{
    // Deskripsi realistis per kategori
    private array $descriptions = [
        'want' => [
            'Makan di restoran dengan teman', 'Nonton bioskop XXI', 'Beli game online Steam',
            'Langganan Netflix bulanan', 'Kopi di Starbucks', 'Beli baju di Uniqlo',
            'Makan bersama keluarga', 'Langganan Spotify Premium', 'Beli aksesori HP baru',
            'Jajan snack minimarket', 'Main bowling bersama', 'Beli novel terbaru',
            'Nongkrong di kafe', 'Beli tanaman hias', 'Langganan Disney+',
            'Beli skincare', 'Makan sushi', 'Beli headphone baru',
            'Karaoke bersama teman', 'Beli merchandise idol',
        ],
        'need' => [
            'Transportasi Gojek ke kantor', 'Bayar tagihan listrik PLN', 'Belanja bahan makanan di Indomaret',
            'Bayar internet Indihome', 'Beli obat di apotek', 'Naik KRL ke kantor',
            'Bayar air PDAM', 'Belanja sembako Alfamart', 'Beli pulsa Telkomsel',
            'Bensin motor Pertamax', 'Bayar sewa kos bulanan', 'Laundry pakaian',
            'Beli beras 5kg', 'Bayar BPJS Kesehatan', 'Konsultasi dokter umum',
            'Parkir motor bulanan', 'Beli sabun & shampo', 'Bayar tagihan gas',
            'Transport ojek ke pasar', 'Beli minyak goreng',
        ],
        'saving' => [
            'Transfer ke rekening tabungan', 'Setoran tabungan rutin bulanan',
            'Nabung untuk dana darurat', 'Simpanan akhir bulan',
            'Transfer ke tabungan berjangka', 'Setoran ke celengan digital',
            'Nabung untuk DP rumah', 'Tabungan pendidikan anak',
        ],
        'investment' => [
            'Top-up Bibit reksa dana saham', 'Beli emas Antam 0.5gr',
            'Setoran deposito BCA', 'Beli ORI (Obligasi Ritel Indonesia)',
            'Top-up reksa dana Bareksa', 'Beli saham BBCA di Ajaib',
            'Beli emas digital Tokomas', 'Beli SBR (Saving Bond Ritel)',
            'Top-up reksa dana pasar uang', 'Setoran investasi rutin Bibit',
        ],
    ];

    public function run(): void
    {
        $users      = User::all();
        $categories = Category::all()->groupBy('type');

        $allExpenses = [];

        $targetPerTypePerUser = 12;

        foreach ($users as $user) {
            foreach (['want', 'need', 'saving', 'investment'] as $type) {
                $typeCats = $categories->get($type, collect());

                if ($typeCats->isEmpty()) {
                    continue;
                }

                for ($i = 0; $i < $targetPerTypePerUser; $i++) {
                    // Tanggal random dalam 3 bulan terakhir
                    $date     = Carbon::now()->subDays(rand(1, 90));
                    $category = $typeCats->random();

                    $amount = $this->getRandomAmount($type);
                    $desc   = $this->descriptions[$type][array_rand($this->descriptions[$type])];

                    // ~70% verified, 30% mix lainnya
                    $statusRoll      = rand(1, 10);
                    $verifiedStatus  = match (true) {
                        $statusRoll <= 7 => 'verified',
                        $statusRoll == 8 => 'pending',
                        $statusRoll == 9 => 'draft',
                        default          => 'unverified',
                    };

                    $allExpenses[] = [
                        'user_id'         => $user->id,
                        'category_id'     => $category->id,
                        'amount'          => $amount,
                        'description'     => $desc,
                        'date'            => $date->format('Y-m-d'),
                        'verified_status' => $verifiedStatus,
                        'verification_id' => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];
                }
            }
        }

        // Insert dalam batch
        $chunks = array_chunk($allExpenses, 100);
        foreach ($chunks as $chunk) {
            Expense::insert($chunk);
        }

        $total = count($allExpenses);
        $this->command->info("✅ ExpenseSeeder: {$total} expense entries created (~{$targetPerTypePerUser} per type per user).");
    }

    private function getRandomAmount(string $type): int
    {
        return match ($type) {
            'want'       => rand(1, 30) * 10000,   // 10rb - 300rb
            'need'       => rand(2, 50) * 10000,   // 20rb - 500rb
            'saving'     => rand(10, 100) * 10000, // 100rb - 1jt
            'investment' => rand(20, 200) * 10000, // 200rb - 2jt
            default      => rand(1, 50) * 10000,
        };
    }
}
