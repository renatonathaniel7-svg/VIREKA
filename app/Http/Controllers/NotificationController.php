<?php

namespace App\Http\Controllers;

use App\Models\AppreciationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * NotificationController
 *
 * Mengelola halaman dan aksi notifikasi in-app.
 * Notifikasi bersumber dari tabel appreciation_logs.
 *
 * WHY APPRECIATION_LOGS AS NOTIFICATIONS:
 * Daripada membuat tabel notifications terpisah, kita reuse appreciation_logs
 * yang sudah berisi semua event penting (streak, badge, monthly summary,
 * income growth). Ini menghindari redundansi data dan menjaga konsistensi.
 * Field read_at yang baru ditambahkan cukup untuk membedakan status baca.
 *
 * PAGINATION: 20 per halaman — cukup informatif tanpa overwhelming.
 */
class NotificationController extends Controller
{
    /**
     * Tampilkan semua notifikasi user, paginated 20 per halaman.
     * Unread ditampilkan lebih prominent (background berbeda via CSS).
     */
    public function index(): View
    {
        $user = auth()->user();

        $notifications = AppreciationLog::where('user_id', $user->id)
            ->orderByRaw('read_at IS NOT NULL') // Unread first
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = AppreciationLog::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     *
     * Security: WHERE user_id = auth()->id() memastikan user hanya bisa
     * menandai miliknya sendiri. Tanpa ini, user bisa menandai notif orang lain.
     */
    public function markRead(int $id): RedirectResponse
    {
        AppreciationLog::where('id', $id)
            ->where('user_id', auth()->id()) // Security: pastikan milik user ini
            ->whereNull('read_at')           // Hanya update jika belum dibaca
            ->update(['read_at' => now()]);

        return back()->with('success', 'Notifikasi ditandai sebagai dibaca.');
    }

    /**
     * Tandai SEMUA notifikasi user sebagai sudah dibaca sekaligus.
     * Menggunakan mass update yang lebih efisien daripada loop.
     */
    public function markAllRead(): RedirectResponse
    {
        AppreciationLog::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}
