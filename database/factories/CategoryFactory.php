<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $categories = [
            ['name' => 'Hiburan & Gaya Hidup', 'type' => 'want', 'description' => 'Nonton, makan di luar, hobi'],
            ['name' => 'Makanan & Minuman', 'type' => 'need', 'description' => 'Kebutuhan makan sehari-hari'],
            ['name' => 'Transportasi', 'type' => 'need', 'description' => 'Ojek, bensin, bus, KRL'],
            ['name' => 'Tabungan Rutin', 'type' => 'saving', 'description' => 'Setoran tabungan bulanan'],
            ['name' => 'Investasi Aktif', 'type' => 'investment', 'description' => 'Reksa dana, saham, emas'],
        ];

        $cat = $this->faker->randomElement($categories);

        return [
            'name'        => $cat['name'],
            'type'        => $cat['type'],
            'description' => $cat['description'],
        ];
    }

    public function want(): static
    {
        return $this->state(fn () => [
            'name' => 'Hiburan & Gaya Hidup',
            'type' => 'want',
            'description' => 'Pengeluaran keinginan, pertama dipangkas saat survive mode',
        ]);
    }

    public function need(): static
    {
        return $this->state(fn () => [
            'name' => 'Kebutuhan Pokok',
            'type' => 'need',
            'description' => 'Pengeluaran esensial yang dilindungi',
        ]);
    }

    public function saving(): static
    {
        return $this->state(fn () => [
            'name' => 'Tabungan',
            'type' => 'saving',
            'description' => 'Alokasi tabungan',
        ]);
    }

    public function investment(): static
    {
        return $this->state(fn () => [
            'name' => 'Investasi',
            'type' => 'investment',
            'description' => 'Alokasi investasi aktif',
        ]);
    }
}
