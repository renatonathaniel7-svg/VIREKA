<?php

namespace App\Services;

use App\Models\AppreciationLog;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * SurviveModeService
 *
 * Adaptive budget protection engine. Menghitung level survive berdasarkan
 * rasio liquid_balance terhadap avg_monthly_expense (rolling 3 bulan).
 *
 * Level Mapping:
 *   NORMAL   → ratio > 0.30  (liquid > 30% avg expense)
 *   CAUTION  → ratio > 0.15  (liquid 15-30%)   → -20% daily budget
 *   SURVIVE  → ratio > 0.05  (liquid 5-15%)    → -40%, want frozen
 *   CRITICAL → ratio <= 0.05 (liquid < 5%)     → -60%, saran withdrawal
 *
 * Design Decision: Ratio threshold bukan nilai empiris — ini design decision
 * yang dapat didokumentasikan dalam laporan TA sebagai parameter yang dapat
 * disesuaikan berdasarkan preferensi pengguna di masa depan.
 */
class SurviveModeService
{
    /**
     * Hitung survive level untuk user.
     * Dipanggil setiap kali dashboard dimuat via updateUserLevel().
     *
     * @param User $user
     * @return string 'normal' | 'caution' | 'survive' | 'critical'
     */
    public function calculateLevel(User $user): string
    {
        // Ambil liquid balance dari DashboardService
        // Liquid balance = verified income - verified expense (TIDAK termasuk investment pool)
        $balanceSummary = app(DashboardService::class)->getBalanceSummary($user);
        $liquidBalance  = $balanceSummary['liquid_balance'] ?? 0;

        // Hitung avg monthly expense dari rolling 3 bulan terakhir (verified only)
        $avgMonthlyExpense = $this->getAvgMonthlyExpense($user);

        // Jika belum ada data expense sama sekali → normal
        // Edge case: user baru yang belum punya history expense
        if ($avgMonthlyExpense <= 0) {
            return 'normal';
        }

        // Jika liquid balance negatif → langsung critical
        if ($liquidBalance <= 0) {
            return 'critical';
        }

        $ratio = $liquidBalance / $avgMonthlyExpense;

        return match (true) {
            $ratio > 0.30  => 'normal',
            $ratio > 0.15  => 'caution',
            $ratio > 0.05  => 'survive',
            default        => 'critical',
        };
    }

    /**
     * Hitung rata-rata pengeluaran bulanan dari 3 bulan terakhir.
     * Hanya menggunakan expense dengan status 'verified'.
     *
     * Jika data kurang dari 3 bulan → pakai data yang ada (tidak memaksa 3 bulan).
     * Ini penting untuk user baru yang belum punya history 3 bulan.
     *
     * @param User $user
     * @return float Rata-rata bulanan verified expense
     */
    public function getAvgMonthlyExpense(User $user): float
    {
        $months = collect();

        for ($i = 1; $i <= 3; $i++) {
            $date  = now()->subMonths($i);
            $total = Expense::where('user_id', $user->id)
                ->where('verified_status', 'verified')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            // Hanya push bulan yang punya data expense
            // Jika tidak ada expense bulan itu, tidak dimasukkan avg
            if ($total > 0) {
                $months->push($total);
            }
        }

        return $months->isEmpty() ? 0 : $months->avg();
    }

    /**
     * Apply budget multiplier berdasarkan survive level.
     * Digunakan di DashboardService untuk adjust daily budget burndown.
     *
     * Untuk kategori 'want' di level survive/critical:
     * DashboardService akan set limit = 0 dan flag 'frozen' = true.
     *
     * @param float  $dailyLimit Daily limit dari tabel budgets
     * @param string $level      Survive level saat ini
     * @return float Adjusted daily limit
     */
    public function applyBudgetMultiplier(float $dailyLimit, string $level): float
    {
        return match ($level) {
            'caution'  => $dailyLimit * 0.80, // -20% → mulai waspada
            'survive'  => $dailyLimit * 0.60, // -40% → potong signifikan
            'critical' => $dailyLimit * 0.40, // -60% → hanya essential
            default    => $dailyLimit,         // normal → tidak ada perubahan
        };
    }

    /**
     * Kalkulasi + simpan survive_level ke users table.
     * Hanya update jika level berubah (efisiensi query).
     * Log perubahan ke appreciation_logs jika level bukan normal.
     *
     * Dipanggil di DashboardController@index setiap kali dashboard dimuat.
     *
     * @param User $user
     * @return void
     */
    public function updateUserLevel(User $user): void
    {
        try {
            $level = $this->calculateLevel($user);

            // Hanya update & log jika level berubah
            if ($user->survive_level !== $level) {
                $previousLevel      = $user->survive_level;
                $user->survive_level = $level;
                $user->save();

                // Log perubahan level ke appreciation_logs
                // Hanya jika level bukan normal, atau transisi dari non-normal ke normal
                $shouldLog = ($level !== 'normal') ||
                             ($previousLevel !== 'normal' && $level === 'normal');

                if ($shouldLog) {
                    AppreciationLog::create([
                        'user_id'       => $user->id,
                        'type'          => 'daily_warning',
                        'message'       => $this->getLevelMessage($level),
                        'trigger_value' => $this->getCurrentRatio($user),
                        'streak_count'  => $user->current_streak ?? 0,
                        'badge_earned'  => null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Jangan crash dashboard hanya karena survive mode gagal hitung
            Log::error('SurviveModeService::updateUserLevel failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cek apakah kategori 'want' dibekukan untuk user saat ini.
     * Dipakai di ExpenseController saat user mencoba tambah expense want.
     *
     * @param User $user
     * @return bool
     */
    public function isWantCategoryFrozen(User $user): bool
    {
        return in_array($user->survive_level, ['survive', 'critical']);
    }

    /**
     * Cek apakah survive mode aktif (level bukan normal).
     *
     * @param User $user
     * @return bool
     */
    public function isActive(User $user): bool
    {
        return $user->survive_level !== 'normal';
    }

    /**
     * Ambil pesan notifikasi berdasarkan level.
     * Digunakan untuk appreciation_logs dan UI banner.
     *
     * @param string $level
     * @return string
     */
    public function getLevelMessage(string $level): string
    {
        return match ($level) {
            'caution'  => 'Liquid balance mulai menipis. Budget harian dikurangi 20%. Pertimbangkan mengurangi pengeluaran Want.',
            'survive'  => 'Survive Mode aktif! Kategori Want dibekukan sementara. Budget harian dikurangi 40%. Fokus pada kebutuhan esensial.',
            'critical' => 'Kondisi keuangan kritis! Liquid balance hampir habis. Hanya pengeluaran esensial yang diizinkan. Pertimbangkan pencairan investasi.',
            'normal'   => 'Kondisi keuangan kembali normal. Survive Mode dinonaktifkan.',
            default    => 'Status keuangan diperbarui.',
        };
    }

    /**
     * Hitung ratio saat ini untuk logging.
     * liquid_balance / avg_monthly_expense
     *
     * @param User $user
     * @return float
     */
    private function getCurrentRatio(User $user): float
    {
        $balanceSummary    = app(DashboardService::class)->getBalanceSummary($user);
        $liquidBalance     = $balanceSummary['liquid_balance'] ?? 0;
        $avgMonthlyExpense = $this->getAvgMonthlyExpense($user);

        if ($avgMonthlyExpense <= 0) {
            return 1.0; // Default: kondisi normal jika tidak ada data
        }

        return round($liquidBalance / $avgMonthlyExpense, 4);
    }

    /**
     * Mendapatkan warna badge berdasarkan level untuk UI.
     *
     * @param string $level
     * @return array ['bg' => '...', 'text' => '...', 'border' => '...']
     */
    public function getLevelColors(string $level): array
    {
        return match ($level) {
            'caution'  => [
                'bg'     => 'bg-yellow-50 dark:bg-yellow-900/20',
                'text'   => 'text-yellow-800 dark:text-yellow-300',
                'border' => 'border-yellow-400',
                'icon'   => '⚠️',
            ],
            'survive'  => [
                'bg'     => 'bg-orange-50 dark:bg-orange-900/20',
                'text'   => 'text-orange-800 dark:text-orange-300',
                'border' => 'border-orange-500',
                'icon'   => '🔴',
            ],
            'critical' => [
                'bg'     => 'bg-red-50 dark:bg-red-900/20',
                'text'   => 'text-red-800 dark:text-red-300',
                'border' => 'border-red-600',
                'icon'   => '🚨',
            ],
            default    => [
                'bg'     => 'bg-green-50 dark:bg-green-900/20',
                'text'   => 'text-green-800 dark:text-green-300',
                'border' => 'border-green-400',
                'icon'   => '✅',
            ],
        };
    }
}
