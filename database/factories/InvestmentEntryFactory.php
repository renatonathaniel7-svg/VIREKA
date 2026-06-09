<?php

namespace Database\Factories;

use App\Models\InvestmentEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: InvestmentEntryFactory
 *
 * Instrumen investasi Indonesia yang realistis:
 * Emas, Reksa Dana, Saham, Deposito, ORI
 */
class InvestmentEntryFactory extends Factory
{
    protected $model = InvestmentEntry::class;

    private array $instruments = [
        'Emas Antam',
        'Reksa Dana Pasar Uang',
        'Reksa Dana Saham',
        'Saham BBCA',
        'Saham BBRI',
        'Saham TLKM',
        'Deposito BCA',
        'Deposito Mandiri',
        'ORI (Obligasi Ritel Indonesia)',
        'SBR (Saving Bond Ritel)',
    ];

    public function definition(): array
    {
        $type          = $this->faker->randomElement(['saving', 'saving', 'investment', 'investment']);
        $initialAmount = $this->faker->numberBetween(500000, 10000000);

        // Simulasi return: -10% sampai +25%
        $returnPct    = $this->faker->randomFloat(2, -10, 25);
        $currentValue = $initialAmount * (1 + $returnPct / 100);

        return [
            'user_id'         => User::factory(),
            'allocation_type' => $type,
            'instrument'      => $type === 'investment'
                ? $this->faker->randomElement($this->instruments)
                : $this->faker->randomElement(['Tabungan BCA', 'Tabungan BRI', 'Tabungan Mandiri']),
            'initial_amount'  => $initialAmount,
            'current_value'   => round($currentValue, 2),
            'return_pct'      => $returnPct,
            'note'            => $this->faker->optional(0.5)->randomElement([
                'Dana darurat',
                'Tabungan pernikahan',
                'Investasi jangka panjang',
                'Dana pendidikan',
                'Cicilan DP rumah',
                null,
            ]),
        ];
    }

    public function saving(): static
    {
        return $this->state(fn (array $attributes) => [
            'allocation_type' => 'saving',
            'instrument'      => $this->faker->randomElement(['Tabungan BCA', 'Tabungan BRI', 'Tabungan Mandiri']),
            'return_pct'      => $this->faker->randomFloat(2, 0.5, 3.5),
        ]);
    }

    public function investment(): static
    {
        return $this->state(fn (array $attributes) => [
            'allocation_type' => 'investment',
            'instrument'      => $this->faker->randomElement($this->instruments),
            'return_pct'      => $this->faker->randomFloat(2, -10, 25),
        ]);
    }
}
