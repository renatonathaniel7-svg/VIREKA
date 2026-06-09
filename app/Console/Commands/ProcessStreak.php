<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\StreakService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * ProcessStreak Artisan Command
 *
 * WHY THIS EXISTS:
 * Cron job berjalan otomatis tiap tengah malam, tapi selama development
 * dan testing kita perlu bisa trigger proses streak secara manual.
 * Command ini memungkinkan:
 * 1. Testing streak logic tanpa menunggu tengah malam
 * 2. Re-processing jika cron gagal suatu malam
 * 3. Testing badge award untuk milestone tertentu
 *
 * USAGE:
 *   php artisan streak:process           → proses semua user (hari kemarin)
 *   php artisan streak:process 1         → proses user ID 1 (hari kemarin)
 *   php artisan streak:process 1 --date=2025-01-15  → proses tanggal tertentu
 *   php artisan streak:process --date=2025-01-15    → semua user, tanggal tertentu
 */
class ProcessStreak extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'streak:process
                            {user_id? : ID user yang ingin diproses (opsional, default: semua user)}
                            {--date= : Tanggal yang diproses dalam format Y-m-d (default: kemarin)}';

    /**
     * The console command description.
     */
    protected $description = 'Proses streak harian untuk semua user atau user tertentu. ' .
                             'Default memproses transaksi hari kemarin.';

    public function __construct(
        private readonly StreakService $streakService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Tentukan tanggal yang akan diproses
        $dateOption = $this->option('date');

        if ($dateOption) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $dateOption);
                $this->info("📅 Memproses streak untuk tanggal: {$date->toDateString()}");
            } catch (\Exception $e) {
                $this->error("Format tanggal tidak valid. Gunakan format Y-m-d (contoh: 2025-01-15)");
                return Command::FAILURE;
            }
        } else {
            // Default: proses hari kemarin
            // Sama dengan logika scheduler (dijalankan tengah malam, proses hari kemarin)
            $date = now()->subDay();
            $this->info("📅 Memproses streak untuk tanggal: {$date->toDateString()} (kemarin)");
        }

        $userId = $this->argument('user_id');

        // ── Mode: Single User ──
        if ($userId !== null) {
            $user = User::find($userId);

            if (!$user) {
                $this->error("❌ User dengan ID {$userId} tidak ditemukan.");
                return Command::FAILURE;
            }

            $this->info("👤 Memproses streak untuk: {$user->name} (ID: {$user->id})");
            $this->processUser($user, $date);
            $this->displayUserStatus($user);

            return Command::SUCCESS;
        }

        // ── Mode: Semua User ──
        $this->info("👥 Memproses streak untuk semua user...");

        // TODO: ganti dengan chunk() untuk production agar tidak OOM
        // User::chunk(100, function ($users) use ($date) { ... })
        $users    = User::all();
        $total    = $users->count();
        $processed = 0;
        $errors   = 0;

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($users as $user) {
            try {
                $this->processUser($user, $date);
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->warn("⚠️  Error pada user {$user->id} ({$user->name}): {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Selesai! Processed: {$processed}, Errors: {$errors}, Total: {$total}");

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Proses streak untuk satu user dan refresh dari DB setelah proses.
     */
    private function processUser(User $user, Carbon $date): void
    {
        $beforeStreak = $user->current_streak;

        $this->streakService->processDailyStreak($user, $date);

        // Refresh dari DB untuk mendapatkan nilai terbaru
        $user->refresh();

        $afterStreak = $user->current_streak;

        if ($this->getOutput()->isVerbose()) {
            $this->line("   {$user->name}: streak {$beforeStreak} → {$afterStreak}");
        }
    }

    /**
     * Tampilkan status user setelah proses (untuk single user mode).
     */
    private function displayUserStatus(User $user): void
    {
        $user->refresh();

        $this->newLine();
        $this->table(
            ['Atribut', 'Nilai'],
            [
                ['Current Streak', "🔥 {$user->current_streak} hari"],
                ['Best Streak',    "⭐ {$user->best_streak} hari"],
                ['Grace Days',     "{$user->grace_days} hari"],
            ]
        );
    }
}
