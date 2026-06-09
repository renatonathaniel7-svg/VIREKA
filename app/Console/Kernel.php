<?php

namespace App\Console;

use App\Services\AppreciationService;
use App\Services\StreakService;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Proses streak semua user setiap tengah malam
        $schedule->call(function () {
            // TODO: ganti dengan chunk() untuk production
            User::all()->each(function ($user) {
                app(StreakService::class)
                    ->processDailyStreak($user, now()->subDay());
            });
        })->dailyAt('00:05');

        // Generate monthly summary hari pertama tiap bulan
        $schedule->call(function () {
            $lastMonth = now()->subMonth();
            User::all()->each(function ($user) use ($lastMonth) {
                app(AppreciationService::class)->generateMonthlySummary(
                    $user,
                    $lastMonth->month,
                    $lastMonth->year
                );
            });
        })->monthlyOn(1, '00:30');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
