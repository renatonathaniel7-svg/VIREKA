<?php

namespace App\Http\Controllers;

use App\Services\BadgeService;
use Illuminate\View\View;

/**
 * BadgeController
 *
 * Menampilkan halaman badge — semua badge yang tersedia dan
 * mana yang sudah/belum diraih user.
 *
 * Single responsibility: hanya halaman tampilan badge.
 * Logic earned/locked ada di BadgeService::getBadgesWithStatus().
 */
class BadgeController extends Controller
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    /**
     * Tampilkan halaman badge user.
     *
     * Mengirimkan ke view:
     * - badges: Collection badge dengan property earned, earned_at, dll
     * - earnedCount: jumlah badge yang sudah diraih
     * - totalCount: total badge yang tersedia
     * - user: auth user (untuk current_streak, best_streak)
     */
    public function index(): View
    {
        $user = auth()->user();

        // getBadgesWithStatus() mengembalikan Collection badge objects
        // dengan property tambahan: earned, earned_at, remaining_streak, dll
        $badges = $this->badgeService->getBadgesWithStatus($user);

        $earnedCount = $badges->filter(fn($b) => $b->earned)->count();
        $totalCount  = $badges->count();

        return view('badges.index', [
            'badges'      => $badges,
            'earnedCount' => $earnedCount,
            'totalCount'  => $totalCount,
            'user'        => $user,
        ]);
    }
}
