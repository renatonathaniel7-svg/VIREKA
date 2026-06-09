<?php

namespace App\Services;

use App\Models\AppreciationLog;
use App\Models\IncomeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * AppreciationService
 *
 * WHY THIS EXISTS SEPARATELY:
 * AppreciationService menangani "lapisan apresiasi" — semua event yang
 * memberikan umpan balik positif kepada user di luar streak harian.
 * Ini mencakup:
 * 1. Monthly summary → snapshot skor bulanan
 * 2. Income growth detection → reinforcement positif saat income naik
 *
 * Pemisahan dari StreakService karena trigger-nya berbeda:
 * - StreakService: dipicu tiap tengah malam (setiap hari)
 * - AppreciationService: dipicu bulanan ATAU saat income verified
 *
 * BEHAVIORAL PSYCHOLOGY NOTE:
 * Monthly summary menggunakan positive framing bahkan untuk skor rendah.
 * Tujuannya bukan menghukum, tapi membuat user sadar dan termotivasi
 * untuk memperbaiki di bulan berikutnya.
 */
class AppreciationService
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * Generate ringkasan bulanan untuk user.
     *
     * Dipanggil oleh scheduler pada hari pertama tiap bulan (untuk bulan lalu).
     * Menghitung Financial Health Score bulan yang baru selesai dan menyimpan
     * log sebagai notifikasi yang bisa dilihat user.
     *
     * WHY WE USE DashboardService:
     * Score calculation sudah ada di DashboardService (dari Chat 4 — dashboard).
     * Kita reuse logic tersebut untuk menghindari duplikasi formula.
     * DRY principle: satu sumber kebenaran untuk formula skor.
     *
     * @param User $user
     * @param int  $month Bulan yang di-summarize (1-12)
     * @param int  $year  Tahun yang di-summarize
     */
    public function generateMonthlySummary(User $user, int $month, int $year): void
    {
        try {
            // Delegasikan kalkulasi ke DashboardService
            // DashboardService sudah handle: BAR, SR, SC, dan skor final
            $scoreData = $this->dashboardService->getFinancialHealthScore($user, $month, $year);

            $score     = $scoreData['score'] ?? 0;
            $tierLabel = $scoreData['tier']['label'] ?? 'Belum ada data';
            $tierCode  = $scoreData['tier']['code'] ?? 'D';

            // Format nama bulan dalam Bahasa Indonesia
            $monthName = \Carbon\Carbon::create($year, $month, 1)
                ->locale('id')
                ->isoFormat('MMMM YYYY');

            // Buat pesan yang informatif namun tetap motivating
            $message = $this->buildMonthlySummaryMessage($score, $tierCode, $tierLabel, $monthName, $scoreData);

            AppreciationLog::create([
                'user_id'       => $user->id,
                'type'          => 'monthly_summary',
                'trigger_value' => round($score, 2),
                'streak_count'  => $user->best_streak,
                'badge_earned'  => null,
                'message'       => $message,
            ]);

        } catch (\Throwable $e) {
            Log::error("AppreciationService::generateMonthlySummary failed for user {$user->id}: {$e->getMessage()}", [
                'user_id' => $user->id,
                'month'   => $month,
                'year'    => $year,
            ]);
        }
    }

    /**
     * Bangun pesan monthly summary yang informatif dan motivating.
     *
     * Pesan disesuaikan dengan tier agar tidak terasa generik.
     * Tier S/A → celebratory; Tier B/C → encouraging; Tier D → alarming but constructive
     */
    private function buildMonthlySummaryMessage(
        float  $score,
        string $tierCode,
        string $tierLabel,
        string $monthName,
        array  $scoreData
    ): string {
        $scoreRounded = round($score, 1);
        $bar = isset($scoreData['components']['bar']) ? round($scoreData['components']['bar'], 1) : '-';
        $sr  = isset($scoreData['components']['sr'])  ? round($scoreData['components']['sr'], 1)  : '-';
        $sc  = isset($scoreData['components']['sc'])  ? round($scoreData['components']['sc'], 1)  : '-';

        $intro = match(true) {
            $tierCode === 'S' => "🏆 Luar biasa!",
            $tierCode === 'A' => "⭐ Kerja bagus!",
            $tierCode === 'B' => "📊 Lumayan baik!",
            $tierCode === 'C' => "💪 Masih bisa lebih baik!",
            default           => "⚠️ Perlu perhatian lebih!",
        };

        return "{$intro} Laporan {$monthName}: "
            . "Score {$scoreRounded}/100 — Tier {$tierCode} ({$tierLabel}). "
            . "Rincian: Budget Adherence {$bar}%, Saving Rate {$sr}%, Streak Consistency {$sc}%.";
    }

    /**
     * Cek pertumbuhan income user dan beri apresiasi jika ada peningkatan.
     *
     * Dipanggil setiap kali income baru diverifikasi (dari VerificationService
     * atau IncomeController setelah status berubah ke 'verified').
     *
     * LOGIC:
     * Bandingkan total income bulan ini dengan rata-rata 3 bulan sebelumnya.
     * Jika naik >= 10% → log income_growth appreciation.
     * Threshold 10% dipilih untuk menghindari false positive dari fluktuasi kecil.
     *
     * WHY 3-MONTH AVERAGE:
     * Lebih stabil daripada bulan sebelumnya saja. Mencerminkan baseline yang
     * lebih representatif dari pola income user.
     *
     * @param User $user
     */
    public function checkIncomeGrowth(User $user): void
    {
        try {
            $now = now();

            // Total income verified bulan ini
            $currentMonthTotal = IncomeEntry::where('user_id', $user->id)
                ->whereMonth('date', $now->month)
                ->whereYear('date', $now->year)
                ->where('verified_status', 'verified')
                ->sum('amount');

            // Tidak ada income bulan ini → skip
            if ($currentMonthTotal <= 0) {
                return;
            }

            // Ambil total income 3 bulan sebelumnya
            $monthlyTotals = [];
            for ($i = 1; $i <= 3; $i++) {
                $pastDate = $now->copy()->subMonths($i);
                $monthlyTotals[] = IncomeEntry::where('user_id', $user->id)
                    ->whereMonth('date', $pastDate->month)
                    ->whereYear('date', $pastDate->year)
                    ->where('verified_status', 'verified')
                    ->sum('amount');
            }

            // Filter bulan yang memang ada datanya (tidak semua 0)
            $nonZeroMonths = array_filter($monthlyTotals, fn($v) => $v > 0);

            // Butuh minimal 1 bulan data historis untuk perbandingan
            if (empty($nonZeroMonths)) {
                return;
            }

            $avgPastIncome = array_sum($nonZeroMonths) / count($nonZeroMonths);

            // Hitung persentase pertumbuhan
            $growthPct = (($currentMonthTotal - $avgPastIncome) / $avgPastIncome) * 100;

            // Threshold: naik >= 10% dianggap pertumbuhan signifikan
            if ($growthPct < 10.0) {
                return;
            }

            // Hindari double-notification: cek apakah sudah pernah log bulan ini
            $alreadyLogged = AppreciationLog::where('user_id', $user->id)
                ->where('type', 'income_growth')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->exists();

            if ($alreadyLogged) {
                return;
            }

            $growthFormatted = round($growthPct, 1);
            $avgFormatted    = number_format($avgPastIncome, 0, ',', '.');
            $currentFormatted = number_format($currentMonthTotal, 0, ',', '.');

            AppreciationLog::create([
                'user_id'       => $user->id,
                'type'          => 'income_growth',
                'trigger_value' => round($growthPct, 2),
                'streak_count'  => $user->current_streak,
                'badge_earned'  => null,
                'message'       => "📈 Income bulan ini (Rp {$currentFormatted}) naik {$growthFormatted}% dibanding rata-rata 3 bulan sebelumnya (Rp {$avgFormatted}). Teruskan!",
            ]);

        } catch (\Throwable $e) {
            Log::error("AppreciationService::checkIncomeGrowth failed for user {$user->id}: {$e->getMessage()}");
        }
    }

    /**
     * Hitung jumlah notifikasi yang belum dibaca untuk user.
     * Digunakan oleh View Composer untuk badge merah di navbar.
     *
     * PERFORMANCE NOTE:
     * Query ini ringan karena hanya COUNT dengan index (user_id, read_at).
     * Aman dipanggil tiap request via View Composer.
     *
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return AppreciationLog::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
