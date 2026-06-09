<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Factory: ExpenseFactory
 *
 * Data pengeluaran realistis Indonesia:
 * - Nominal: 10.000 - 500.000 per transaksi
 * - Tersebar dalam 3 bulan terakhir
 * - Deskripsi realistis per kategori
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    // Deskripsi realistis per tipe kategori
    private array $wantDescriptions = [
        'Makan di restoran', 'Nonton bioskop', 'Beli game online',
        'Langganan Netflix', 'Kopi kekinian', 'Beli baju diskon',
        'Makan bersama teman', 'Langganan Spotify', 'Beli aksesori HP',
        'Jajan snack', 'Main bowling', 'Beli novel',
    ];

    private array $needDescriptions = [
        'Transportasi ojek online', 'Bayar listrik', 'Belanja bahan makanan',
        'Bayar internet', 'Beli obat', 'Transportasi bus',
        'Bayar air PDAM', 'Belanja sembako', 'Beli pulsa',
        'Bensin motor', 'Bayar kos/sewa', 'Laundry pakaian',
    ];

    private array $savingDescriptions = [
        'Transfer ke tabungan', 'Setoran tabungan rutin',
        'Nabung darurat fund', 'Simpanan akhir bulan',
    ];

    private array $investmentDescriptions = [
        'Beli reksa dana pasar uang', 'Top-up Bibit',
        'Beli emas Antam', 'Setoran deposito',
        'Beli ORI/SBR', 'Investasi saham blue chip',
    ];

    public function definition(): array
    {
        $date = Carbon::now()->subDays($this->faker->numberBetween(1, 90));

        return [
            'user_id'         => User::factory(),
            'category_id'     => Category::factory(),
            'amount'          => $this->faker->numberBetween(10000, 500000),
            'description'     => $this->faker->randomElement(array_merge(
                $this->wantDescriptions,
                $this->needDescriptions
            )),
            'date'            => $date->format('Y-m-d'),
            'verified_status' => $this->faker->randomElement([
                'verified', 'verified', 'verified', 'verified',  // 70% verified
                'pending', 'draft', 'unverified',                 // 30% lainnya
            ]),
            'verification_id' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_status' => 'verified',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_status' => 'unverified',
            'verification_id' => null,
        ]);
    }

    public function flagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_status' => 'flagged',
        ]);
    }

    /**
     * Expense untuk kategori 'want'
     */
    public function want(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount'      => $this->faker->numberBetween(15000, 300000),
            'description' => $this->faker->randomElement($this->wantDescriptions),
        ]);
    }

    /**
     * Expense untuk kategori 'need'
     */
    public function need(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount'      => $this->faker->numberBetween(20000, 500000),
            'description' => $this->faker->randomElement($this->needDescriptions),
        ]);
    }

    /**
     * Expense untuk kategori 'saving'
     */
    public function saving(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount'      => $this->faker->numberBetween(100000, 1000000),
            'description' => $this->faker->randomElement($this->savingDescriptions),
        ]);
    }

    /**
     * Expense untuk kategori 'investment'
     */
    public function investment(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount'      => $this->faker->numberBetween(200000, 2000000),
            'description' => $this->faker->randomElement($this->investmentDescriptions),
        ]);
    }

    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => Carbon::now()->subDays($this->faker->numberBetween(0, now()->day - 1))->format('Y-m-d'),
        ]);
    }
}
