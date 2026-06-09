<?php

namespace App\Services;

use App\Models\AppreciationLog;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * StreakService
 *
 * WHY THIS IS A SERVICE CLASS:
 * Streak logic menyentuh banyak model (User, Expense, Budget, AppreciationLog)
 * dan memiliki side effects (update DB, trigger badge check). Menempatkannya
 * di Controller akan melanggar Single Responsibility Principle dan membuat
 * unit testing menjadi sangat sulit. Service class memungkinkan:
 * - Dependency injection
 * - Unit testing tanpa HTTP stack
 * - Reuse dari cron job, artisan command, dan event listener
 *
 * STREAK PHILOSOPHY:
 * Streak bukan hanya counter — ia adalah proxy konsistensi perilaku.
 * State machine ini dirancang agar:
 * 1. Fair: grace period 1 hari mencegah penghukuman atas hari tidak belanja
 * 2. Strict: >= 75% spending HARUS reset, tidak ada pengecualian
 * 3. Honest: hanya verified transactions yang dihitung
 */
class StreakService
{
    public function __construct(
        private readonly BadgeService $badgeService
    ) {}

    /**
     * Proses streak harian untuk satu user pada tanggal tertentu.
     *
     * PENTING: Method ini dipanggil dengan $date = yesterday (subDay()) karena
     * scheduler berjalan tengah malam — kita memproses hari yang BARU SELESAI,
     * bukan hari yang sedang berjalan. Ini mencegah false-negative saat user
     * belum sempat input transaksi di detik-detik terakhir.
     *
     * @param User   $user Objek user yang akan diproses
     * @param Carbon $date Tanggal yang diproses (biasanya kemarin)
     */
    public function processDailyStreak(User $user, Carbon $date): void
    {
        try {
            // ─────────────────────────────────────────────
            // STEP 1: Ambil total daily budget bulan ini
            // ─────────────────────────────────────────────
            // Budget disimpan per kategori dengan field daily_limit.
            // Total budget harian = SUM semua kategori aktif user bulan tsb.
            // Jika $totalBudget == 0, user belum setup budget → skip scoring.
            $totalBudget = Budget::where('user_id', $user->id)
                ->whereMonth('month', $date->month)
                ->whereYear('year', $date->year)
                ->sum('daily_limit');

            // ─────────────────────────────────────────────
            // STEP 2: Hitung total pengeluaran VERIFIED hari ini
            // ─────────────────────────────────────────────
            // CRITICAL: hanya 'verified' status. Draft, pending, flagged,
            // unverified TIDAK BOLEH masuk perhitungan streak.
            // Ini adalah constraint keras dari arsitektur verification-first.
            $totalSpent = Expense::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->where('verified_status', 'verified')
                ->sum('amount');

            $hasTransaction = Expense::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->where('verified_status', 'verified')
                ->exists();

            // ─────────────────────────────────────────────
            // STEP 3: STATE MACHINE
            // ─────────────────────────────────────────────
            $logType = null;
            $message = '';
            $tier    = null;
            $pct     = null;

            // ── CASE 1: Tidak ada transaksi verified hari ini ──
            if (!$hasTransaction) {
                if ($user->grace_days >= 1) {
                    // Grace period sudah digunakan → streak direset
                    // Logika: dua hari berturut-turut tanpa aktivitas =
                    // tidak ada usaha pencatatan = streak tidak layak dipertahankan
                    $user->current_streak = 0;
                    $user->grace_days     = 0;
                    $logType = 'daily_warning';
                    $message = 'Streak direset — tidak ada transaksi 2 hari berturut-turut.';
                } else {
                    // Grace period pertama: beri 1 hari toleransi
                    // Tidak log hari ini agar user tidak spam notif saat memang
                    // tidak belanja (misal hari libur)
                    $user->grace_days += 1;
                    $logType = null; // Tidak log — ini adalah grace period
                }
            }

            // ── CASE 2: Ada transaksi verified hari ini ──
            else {
                // Reset grace karena user aktif hari ini
                $user->grace_days = 0;

                if ($totalBudget == 0) {
                    // User belum setup budget sama sekali → tidak bisa hitung persentase
                    // Skip tanpa log agar tidak membingungkan user baru
                    $logType = null;
                } else {
                    $pct = ($totalSpent / $totalBudget) * 100;
                    $pctFormatted = round($pct, 1);

                    if ($pct < 50) {
                        // EXCELLENT: < 50% budget terpakai
                        $user->current_streak += 1;
                        $tier    = 'excellent';
                        $logType = 'daily_appreciation';
                        $message = "Luar biasa! Pengeluaran {$pctFormatted}% dari budget. Streak: {$user->current_streak} hari.";

                    } elseif ($pct < 75) {
                        // GOOD: 50–74.99% budget terpakai
                        $user->current_streak += 1;
                        $tier    = 'good';
                        $logType = 'daily_appreciation';
                        $message = "Hari yang baik! Pengeluaran {$pctFormatted}% dari budget. Streak: {$user->current_streak} hari.";

                    } elseif ($pct <= 100) {
                        // WARNING: 75–100% budget terpakai → STREAK RESET
                        // Ini adalah batas kritis dari desain behavioral:
                        // >= 75% dianggap "tidak hemat" dan streak tidak layak dilanjutkan
                        $user->current_streak = 0;
                        $tier    = 'warning';
                        $logType = 'daily_warning';
                        $message = "Pengeluaran {$pctFormatted}% dari budget. Streak direset.";

                    } else {
                        // DANGER: > 100% budget terpakai → over budget
                        $user->current_streak = 0;
                        $tier    = 'danger';
                        $logType = 'daily_warning';
                        $message = "Budget harian terlampaui! ({$pctFormatted}%). Streak direset.";
                    }
                }
            }

            // ─────────────────────────────────────────────
            // STEP 4: Update best_streak jika melampaui record
            // ─────────────────────────────────────────────
            if ($user->current_streak > $user->best_streak) {
                $user->best_streak = $user->current_streak;
            }

            $user->save();

            // ─────────────────────────────────────────────
            // STEP 5: Log ke appreciation_logs jika ada event
            // ─────────────────────────────────────────────
            if ($logType !== null) {
                AppreciationLog::create([
                    'user_id'       => $user->id,
                    'type'          => $logType,
                    'trigger_value' => $pct !== null ? round($pct, 2) : null,
                    'streak_count'  => $user->current_streak,
                    'message'       => $message,
                ]);
            }

            // ─────────────────────────────────────────────
            // STEP 6: Cek dan award badge berdasarkan streak terbaru
            // ─────────────────────────────────────────────
            // BadgeService dipanggil setelah user->save() agar
            // current_streak yang dicek sudah yang terbaru
            $this->badgeService->checkAndAward($user);

        } catch (\Throwable $e) {
            // Log error tapi jangan lempar exception agar proses
            // user lain di cron job tidak terhenti
            Log::error("StreakService::processDailyStreak failed for user {$user->id}: {$e->getMessage()}", [
                'user_id' => $user->id,
                'date'    => $date->toDateString(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Ambil 5 hari terakhir riwayat spending vs budget untuk tampilan streak page.
     * Digunakan oleh StreakController untuk timeline view.
     *
     * @param User $user
     * @param int  $days Jumlah hari yang diambil (default 5)
     * @return array<int, array{date: string, day_name: string, spent: float, budget: float, pct: float, status: string}>
     */
    public function getRecentHistory(User $user, int $days = 5): array
    {
        $history = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $totalBudget = Budget::where('user_id', $user->id)
                ->whereMonth('month', $date->month)
                ->whereYear('year', $date->year)
                ->sum('daily_limit');

            $totalSpent = Expense::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->where('verified_status', 'verified')
                ->sum('amount');

            $pct    = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : null;
            $status = $this->resolveStatus($pct, Expense::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->where('verified_status', 'verified')
                ->exists());

            $history[] = [
                'date'       => $date->toDateString(),
                'day_name'   => $date->locale('id')->isoFormat('dddd'),
                'spent'      => $totalSpent,
                'budget'     => $totalBudget,
                'pct'        => $pct !== null ? round($pct, 1) : null,
                'status'     => $status,
            ];
        }

        return $history;
    }

    /**
     * Resolve status label dari persentase spending.
     */
    private function resolveStatus(?float $pct, bool $hasTransaction): string
    {
        if (!$hasTransaction) return 'no_data';
        if ($pct === null) return 'no_budget';
        if ($pct < 50)   return 'excellent';
        if ($pct < 75)   return 'good';
        if ($pct <= 100) return 'warning';
        return 'danger';
    }

    /**
     * Hitung milestone badge berikutnya berdasarkan current_streak.
     * Digunakan untuk progress bar di halaman streak.
     *
     * @return array{milestone: int, label: string, remaining: int}
     */
    public function getNextMilestone(int $currentStreak): array
    {
        $milestones = [
            3  => 'Starter Saver',
            7  => 'Smart Spender',
            14 => 'Budget Master',
            30 => 'Financial Discipline',
            60 => 'Money Legend',
        ];

        foreach ($milestones as $days => $label) {
            if ($currentStreak < $days) {
                return [
                    'milestone' => $days,
                    'label'     => $label,
                    'remaining' => $days - $currentStreak,
                ];
            }
        }

        // Sudah melampaui semua milestone
        return [
            'milestone' => 60,
            'label'     => 'Money Legend',
            'remaining' => 0,
        ];
    }
}
