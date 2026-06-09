<?php

namespace Database\Factories;

use App\Models\IncomeSource;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeSourceFactory extends Factory
{
    protected $model = IncomeSource::class;

    private array $sources = [
        ['name' => 'Gaji', 'description' => 'Pendapatan tetap bulanan dari pekerjaan'],
        ['name' => 'Freelance', 'description' => 'Pendapatan dari pekerjaan sampingan / proyek'],
        ['name' => 'Bonus', 'description' => 'Bonus dari pekerjaan atau insentif kinerja'],
        ['name' => 'Investasi Cair', 'description' => 'Hasil pencairan investasi atau tabungan'],
        ['name' => 'Lainnya', 'description' => 'Sumber pendapatan lain-lain'],
    ];

    public function definition(): array
    {
        $source = $this->faker->randomElement($this->sources);

        return [
            'name'        => $source['name'],
            'description' => $source['description'],
        ];
    }
}
