<?php

namespace Database\Factories;

use App\Models\IncomeEntry;
use App\Models\IncomeSource;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Factory: IncomeEntryFactory
 *
 * Data income realistis Indonesia:
 * - Nominal: 500.000 - 8.000.000 per entry
 * - Tersebar dalam 3 bulan terakhir
 * - Mix status ~70% verified, 30% lainnya
 */
class IncomeEntryFactory extends Factory
{
    protected $model = IncomeEntry::class;

    public function definition(): array
    {
        // Tanggal random dalam 3 bulan terakhir
        $date = Carbon::now()->subDays($this->faker->numberBetween(1, 90));

        return [
            'user_id'         => User::factory(),
            'source_id'       => IncomeSource::factory(),
            'amount'          => $this->faker->numberBetween(500000, 8000000),
            'date'            => $date->format('Y-m-d'),
            'verified_status' => $this->faker->randomElement([
                'verified', 'verified', 'verified', 'verified',  // 70% verified
                'pending', 'draft', 'unverified',                 // 30% lainnya
            ]),
            'verification_id' => null,
            'note'            => $this->faker->optional(0.4)->randomElement([
                'Gaji bulan ini',
                'Transfer dari klien',
                'Bonus proyek',
                'Pembayaran invoice',
                'Pendapatan freelance',
                'Hasil penjualan',
                null,
            ]),
        ];
    }

    /**
     * State: verified income dengan verification record
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_status' => 'verified',
        ]);
    }

    /**
     * State: unverified (cash) tanpa verification
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_status' => 'unverified',
            'verification_id' => null,
        ]);
    }

    /**
     * State: income bulan ini
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => Carbon::now()->subDays($this->faker->numberBetween(0, now()->day - 1))->format('Y-m-d'),
        ]);
    }

    /**
     * State: nominal gaji bulanan (3jt - 8jt)
     */
    public function salary(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->numberBetween(3000000, 8000000),
            'note'   => 'Gaji bulanan',
        ]);
    }

    /**
     * State: nominal freelance (500rb - 3jt)
     */
    public function freelance(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->numberBetween(500000, 3000000),
            'note'   => 'Pendapatan freelance',
        ]);
    }
}
