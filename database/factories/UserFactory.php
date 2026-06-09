<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory: UserFactory
 *
 * Menghasilkan data user Indonesia yang realistis.
 * Password default: 'password' (untuk testing)
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // Nama-nama Indonesia realistis
        $namaIndonesia = [
            'Budi Santoso', 'Siti Rahma', 'Ahmad Fauzi', 'Dewi Kartika',
            'Rudi Hermawan', 'Rina Wulandari', 'Agus Setiawan', 'Maya Putri',
            'Eko Prasetyo', 'Fitri Handayani', 'Hendra Kusuma', 'Lestari Indah',
            'Doni Firmansyah', 'Novia Sari', 'Wahyu Hidayat', 'Sri Mulyani',
            'Bambang Wijaya', 'Yuni Astuti', 'Fajar Nugroho', 'Ratna Dewi',
        ];

        return [
            'name'              => $this->faker->randomElement($namaIndonesia),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'current_streak'    => $this->faker->numberBetween(0, 15),
            'best_streak'       => $this->faker->numberBetween(5, 45),
            'grace_days'        => $this->faker->numberBetween(0, 1),
            'survive_level'     => $this->faker->randomElement(['normal', 'normal', 'normal', 'caution', 'survive']),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * State: User dalam kondisi CRITICAL survive mode
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'survive_level' => 'critical',
        ]);
    }

    /**
     * State: User dengan streak tinggi (untuk test badge logic)
     */
    public function highStreak(int $streak = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'current_streak' => $streak,
            'best_streak'    => max($attributes['best_streak'], $streak),
        ]);
    }

    /**
     * State: User baru (streak = 0)
     */
    public function fresh(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_streak' => 0,
            'best_streak'    => 0,
            'grace_days'     => 0,
            'survive_level'  => 'normal',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
