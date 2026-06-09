<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: UserSeeder
 *
 * 5 user Indonesia dengan profil finansial yang berbeda-beda.
 * Ini memungkinkan demo survive mode, streak, badge, dll
 * pada kondisi yang berbeda.
 *
 * Semua password: 'password' (untuk testing)
 *
 * Password untuk demo: password
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // User 1: Kondisi baik — streak tinggi, normal survive level
            [
                'name'           => 'Budi Santoso',
                'email'          => 'budi@fintrack.test',
                'password'       => Hash::make('password'),
                'current_streak' => 14,
                'best_streak'    => 21,
                'grace_days'     => 0,
                'survive_level'  => 'normal',
            ],

            // User 2: Kondisi sedang — streak medium, caution mode
            [
                'name'           => 'Siti Rahma',
                'email'          => 'siti@fintrack.test',
                'password'       => Hash::make('password'),
                'current_streak' => 7,
                'best_streak'    => 14,
                'grace_days'     => 0,
                'survive_level'  => 'caution',
            ],

            // User 3: Kondisi buruk — streak rendah, survive mode
            [
                'name'           => 'Ahmad Fauzi',
                'email'          => 'ahmad@fintrack.test',
                'password'       => Hash::make('password'),
                'current_streak' => 2,
                'best_streak'    => 10,
                'grace_days'     => 1,
                'survive_level'  => 'survive',
            ],

            // User 4: Kondisi kritis — no streak, critical mode
            [
                'name'           => 'Dewi Kartika',
                'email'          => 'dewi@fintrack.test',
                'password'       => Hash::make('password'),
                'current_streak' => 0,
                'best_streak'    => 7,
                'grace_days'     => 0,
                'survive_level'  => 'critical',
            ],

            // User 5: Legend — streak sangat tinggi, sudah punya semua badge
            [
                'name'           => 'Rudi Hermawan',
                'email'          => 'rudi@fintrack.test',
                'password'       => Hash::make('password'),
                'current_streak' => 30,
                'best_streak'    => 62,
                'grace_days'     => 0,
                'survive_level'  => 'normal',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('✅ UserSeeder: 5 users created.');
        $this->command->line('   Login: budi@fintrack.test | password: password');
        $this->command->line('   Login: siti@fintrack.test | password: password');
        $this->command->line('   Login: ahmad@fintrack.test | password: password');
        $this->command->line('   Login: dewi@fintrack.test | password: password');
        $this->command->line('   Login: rudi@fintrack.test | password: password');
    }
}
