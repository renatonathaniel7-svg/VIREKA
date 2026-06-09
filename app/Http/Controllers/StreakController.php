<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use Illuminate\View\View;

/**
 * StreakController
 *
 * Controller ini HANYA bertugas:
 * 1. Menerima request HTTP
 * 2. Memanggil Service
 * 3. Meneruskan data ke View
 *
 * TIDAK ada business logic di sini. Semua kalkulasi ada di StreakService.
 * Ini adalah Fat Service, Thin Controller pattern.
 *
 * WHY THIN CONTROLLER:
 * Controller yang menyimpan business logic menjadi sulit di-test,
 * sulit di-reuse, dan melanggar Single Responsibility Principle.
 */
class StreakController extends Controller
{
    public function __construct(
        private readonly StreakService $streakService
    ) {}

    /**
     * Tampilkan halaman streak user.
     *
     * Data yang dikirim ke view:
     * - user: auth user dengan current_streak, best_streak, grace_days
     * - recentHistory: 5 hari terakhir (date, spent, budget, pct, status)
     * - nextMilestone: info milestone badge berikutnya
     * - surviveLevel: level survive mode saat ini (dari user atau session)
     */
    public function index(): View
    {
        $user = auth()->user();

        // Ambil riwayat 5 hari terakhir untuk timeline
        $recentHistory = $this->streakService->getRecentHistory($user, 5);

        // Info milestone berikutnya untuk progress bar
        $nextMilestone = $this->streakService->getNextMilestone($user->current_streak);

        // Progress ke milestone (0-100%)
        $prevMilestone = $this->getPreviousMilestone($user->current_streak);
        $progressPct = $nextMilestone['remaining'] === 0
            ? 100
            : (($user->current_streak - $prevMilestone) / ($nextMilestone['milestone'] - $prevMilestone)) * 100;

        return view('streak.index', [
            'user'          => $user,
            'recentHistory' => $recentHistory,
            'nextMilestone' => $nextMilestone,
            'progressPct'   => round(min(100, max(0, $progressPct)), 1),
            'surviveLevel'  => $user->survive_level ?? 'NORMAL',
        ]);
    }

    /**
     * Dapatkan milestone sebelumnya berdasarkan current streak.
     * Digunakan untuk menghitung progress bar percentage.
     */
    private function getPreviousMilestone(int $currentStreak): int
    {
        $milestones = [3, 7, 14, 30, 60];

        $prev = 0;
        foreach ($milestones as $m) {
            if ($currentStreak >= $m) {
                $prev = $m;
            } else {
                break;
            }
        }

        return $prev;
    }
}
