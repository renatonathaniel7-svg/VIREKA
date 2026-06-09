<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — Master orchestrator untuk semua seeder FinTrack.
 *
 * URUTAN SEEDING SANGAT PENTING karena foreign key dependency:
 *
 * Dependency chain:
 * badges           (no deps)
 * income_sources   (no deps)
 * categories       (no deps)
 * users            (no deps)
 *   └→ budgets     (needs: users, categories)
 *   └→ income_entries (needs: users, income_sources)
 *   └→ expenses    (needs: users, categories)
 *   └→ verifications (needs: users, income_entries, expenses)
 *   └→ investment_entries (needs: users)
 *   └→ withdrawal_requests (needs: users, investment_entries)
 *   └→ appreciation_logs (needs: users)
 *   └→ user_badges (needs: users, badges)
 *
 * Jalankan dengan: php artisan db:seed
 * Atau fresh:      php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║     FinTrack Database Seeder           ║');
        $this->command->info('║     Personal Finance Tracing System    ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->info('');

        $this->call([
            // ── Layer 1: Master Data (no dependencies) ──────────────
            BadgeSeeder::class,
            IncomeSourceSeeder::class,
            CategorySeeder::class,

            // ── Layer 2: Users ───────────────────────────────────────
            UserSeeder::class,

            // ── Layer 3: Dependent on users + master data ────────────
            BudgetSeeder::class,
            IncomeEntrySeeder::class,
            ExpenseSeeder::class,

            // ── Layer 4: Verification (dependent on income + expense) ─
            // VerificationSeeder::class,

            // ── Layer 5: Investment pool ─────────────────────────────
            InvestmentSeeder::class,

            // ── Layer 6: Behavioral + badge data ────────────────────
            AppreciationSeeder::class,
            UserBadgeSeeder::class,

            // ── Layer 7: Withdrawal (dependent on investment) ────────
            WithdrawalRequestSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════╗');
        $this->command->info('║  ✅ Seeding Complete!                  ║');
        $this->command->info('╚════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->line('  Test accounts:');
        $this->command->line('  ┌─────────────────────────────────────────────────────┐');
        $this->command->line('  │ Email                    │ Password │ Survive Level │');
        $this->command->line('  ├─────────────────────────────────────────────────────┤');
        $this->command->line('  │ budi@fintrack.test       │ password │ normal        │');
        $this->command->line('  │ siti@fintrack.test       │ password │ caution       │');
        $this->command->line('  │ ahmad@fintrack.test      │ password │ survive       │');
        $this->command->line('  │ dewi@fintrack.test       │ password │ critical      │');
        $this->command->line('  │ rudi@fintrack.test       │ password │ normal        │');
        $this->command->line('  └─────────────────────────────────────────────────────┘');
        $this->command->info('');
        $this->command->line('  Run: php artisan migrate:fresh --seed');
    }
}
