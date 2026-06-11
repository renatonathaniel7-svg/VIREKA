<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\IncomeEntry;
use App\Models\InvestmentEntry;
use App\Models\UserBadge;
use App\Models\User;
use App\Models\FinancialGoal;
use App\Models\GoalContribution;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{

    public function getBalanceSummary(User $user): array
    {
        // Verified income (all time, cumulative — running balance model)
        $verifiedIncome = IncomeEntry::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->sum('amount');

        // Verified expense (all time, cumulative)
        $verifiedExpense = Expense::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->sum('amount');

        // Total balance = running balance lintas bulan
        $totalBalance = $verifiedIncome - $verifiedExpense;

        // Investment pool = total current_value semua investment aktif
        $investmentPool = InvestmentEntry::where('user_id', $user->id)
            ->sum('current_value');

        // Liquid balance = uang yang tersedia untuk pengeluaran harian
        $liquidBalance = $totalBalance - $investmentPool;

        // Shadow balance = unverified transactions (tidak mempengaruhi apapun)
        $shadowIncome = IncomeEntry::where('user_id', $user->id)
            ->whereIn('verified_status', ['pending', 'draft', 'unverified'])
            ->sum('amount');

        $shadowExpense = Expense::where('user_id', $user->id)
            ->whereIn('verified_status', ['pending', 'draft', 'unverified'])
            ->sum('amount');

        $shadowBalance = $shadowIncome - $shadowExpense;

        // Monthly stats (bulan ini saja, untuk dashboard widget)
        $thisMonth = now();
        $monthlyIncome = IncomeEntry::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('created_at', $thisMonth->year)
            ->whereMonth('created_at', $thisMonth->month)
            ->sum('amount');

        $monthlyExpense = Expense::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $thisMonth->year)
            ->whereMonth('date', $thisMonth->month)
            ->sum('amount');

        return [
            'total_balance'    => $totalBalance,
            'liquid_balance'   => $liquidBalance,
            'investment_pool'  => $investmentPool,
            'shadow_balance'   => $shadowBalance,
            'verified_income'  => $verifiedIncome,
            'verified_expense' => $verifiedExpense,
            'monthly_income'   => $monthlyIncome,
            'monthly_expense'  => $monthlyExpense,
            'monthly_saving'   => $monthlyIncome - $monthlyExpense,
        ];
    }

    public function getDailyBudgetStatus(User $user): Collection
    {
        $today         = now()->toDateString();
        $surviveLevel  = $user->survive_level ?? 'normal';
        $surviveService = app(SurviveModeService::class);

        // Ambil semua budget user dengan kategori
        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->get();

        return $budgets->map(function (Budget $budget) use ($user, $today, $surviveLevel, $surviveService) {
            // Pengeluaran hari ini untuk kategori ini (verified only)
            $todaySpent = Expense::where('user_id', $user->id)
                ->where('category_id', $budget->category_id)
                ->where('verified_status', 'verified')
                ->whereDate('date', $today)
                ->sum('amount');

            $categoryType = strtolower($budget->category->type ?? '');
            $isFrozen     = false;
            $adjLimit     = (float) $budget->daily_limit;

            //  SURVIVE MODE LOGIC 
            // Cek apakah kategori 'want' perlu dibekukan
            if (in_array($surviveLevel, ['survive', 'critical']) && $categoryType === 'want') {
                // Want category frozen di survive/critical level
                $adjLimit = 0;
                $isFrozen = true;
            } else {
                // Apply budget multiplier untuk semua kategori berdasarkan level
                $adjLimit = $surviveService->applyBudgetMultiplier($budget->daily_limit, $surviveLevel);
            }

            $percentage  = $adjLimit > 0 ? ($todaySpent / $adjLimit) * 100 : 100;
            $remaining   = max(0, $adjLimit - $todaySpent);

            // Tentukan status berdasarkan percentage spending
            $status = match (true) {
                $isFrozen       => 'frozen',
                $percentage < 50  => 'excellent',
                $percentage < 75  => 'good',
                $percentage < 100 => 'warning',
                default           => 'danger',
            };

            $progressColor = match (true) {
              $isFrozen          => 'bg-gray-400',
                $percentage >= 100 => 'bg-red-500',
               $percentage >= 75  => 'bg-orange-500',
               $percentage >= 50  => 'bg-yellow-400',
               default            => 'bg-green-500',
            };

            return [
                'category_id'   => $budget->category_id,
                'category_name' => $budget->category->name,
                'category_type' => $categoryType,
                'daily_limit'   => $budget->daily_limit,    // Original limit
                'adj_limit'     => $adjLimit,                // Adjusted by survive
                'today_spent'   => $todaySpent,
                'remaining'     => $remaining,
                'percentage'    => round($percentage, 1),
                'status'        => $status,
                'progress_color' => $progressColor,
                'frozen'        => $isFrozen,
                'survive_level' => $surviveLevel,
            ];
        });
    }


    public function getHealthScore(User $user): array
    {
        $thisMonth    = now();
        $daysInMonth  = $thisMonth->daysInMonth;
        $currentDay   = min($thisMonth->day, $daysInMonth);

        // ── BAR: Budget Adherence Rate ──────────────────────────────────────
        // Hari dengan total spending < 75% dari total daily budget
        $goodDays  = 0;
        $validDays = 0;

        for ($d = 1; $d <= $currentDay; $d++) {
            $date        = $thisMonth->copy()->setDay($d)->toDateString();
            $totalBudget = Budget::where('user_id', $user->id)->sum('daily_limit');

            if ($totalBudget <= 0) continue;

            $dailySpent = Expense::where('user_id', $user->id)
                ->where('verified_status', 'verified')
                ->whereDate('date', $date)
                ->sum('amount');

            $validDays++;
            if (($dailySpent / $totalBudget) < 0.75) {
                $goodDays++;
            }
        }

        $bar = $validDays > 0 ? ($goodDays / $validDays) * 100 : 0;

        // ── SR: Saving Rate ─────────────────────────────────────────────────
        // (Total saving + investment) / total income × 100
        $monthlyIncome = IncomeEntry::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('created_at', $thisMonth->year)
            ->whereMonth('created_at', $thisMonth->month)
            ->sum('amount');

        $savingExpense = Expense::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $thisMonth->year)
            ->whereMonth('date', $thisMonth->month)
            ->whereHas('category', fn ($q) => $q->whereIn('type', ['saving', 'investment']))
            ->sum('amount');

        $sr = $monthlyIncome > 0 ? ($savingExpense / $monthlyIncome) * 100 : 0;

        // ── SC: Streak Consistency ──────────────────────────────────────────
        // Best streak bulan ini / 30 × 100
        $sc = min(100, (($user->best_streak ?? 0) / 30) * 100);

        // ── Final Score ─────────────────────────────────────────────────────
        $score = (0.40 * $bar) + (0.35 * $sr) + (0.25 * $sc);
        $score = round(min(100, max(0, $score)), 1);

        // Determine tier
        $tier = match (true) {
            $score >= 85 => ['code' => 'S', 'label' => 'Financial Champion', 'color' => 'text-purple-600'],
            $score >= 70 => ['code' => 'A', 'label' => 'Smart Saver',        'color' => 'text-blue-600'],
            $score >= 55 => ['code' => 'B', 'label' => 'On Track',           'color' => 'text-green-600'],
            $score >= 40 => ['code' => 'C', 'label' => 'Needs Attention',    'color' => 'text-yellow-600'],
            default      => ['code' => 'D', 'label' => 'Budget Alert',       'color' => 'text-red-600'],
        };

        return [
            'score'      => $score,
            'tier'       => $tier,
            'components' => [
                'bar' => round($bar, 1),
                'sr'  => round($sr, 1),
                'sc'  => round($sc, 1),
            ],
            'weights'    => ['bar' => 0.40, 'sr' => 0.35, 'sc' => 0.25],
        ];
    }

    public function getBehavioralStats(User $user): array
    {
        $badgeCount = UserBadge::where('user_id', $user->id)->count();

        $recentAppreciation = \App\Models\AppreciationLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        return [
            'current_streak'   => $user->current_streak ?? 0,
            'best_streak'      => $user->best_streak ?? 0,
            'badge_count'      => $badgeCount,
            'last_log'         => $recentAppreciation,
        ];
    }

    public function getChartData(User $user): array
    {
        $labels  = [];
        $income  = [];
        $expense = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $income[] = IncomeEntry::where('user_id', $user->id)
                ->where('verified_status', 'verified')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');

            $expense[] = Expense::where('user_id', $user->id)
                ->where('verified_status', 'verified')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');
        }

        return compact('labels', 'income', 'expense');
    }

    /**
     * Ambil top spending categories bulan ini (untuk pie chart / list).
     *
     * @param User $user
     * @param int  $limit
     * @return Collection
     */
    public function getTopSpendingCategories(User $user, int $limit = 5): Collection
    {
        return Expense::select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    public function getCategorySplit(User $user): array
{
    $rows = Expense::select(
            'category_id',
            DB::raw('SUM(amount) as total_spent')
        )
        ->with('category')
        ->where('user_id', $user->id)
        ->where('verified_status', 'verified')
        ->whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->groupBy('category_id')
        ->get();

    $grandTotal = $rows->sum('total_spent');

    return $rows->map(function ($row) use ($grandTotal) {
        return [
            'category_name' => $row->category->name ?? 'Tanpa Kategori',
            'category_type' => strtolower($row->category->type ?? 'other'),
            'total_spent'   => (float) $row->total_spent,
            'pct_of_total'  => $grandTotal > 0
                ? round(($row->total_spent / $grandTotal) * 100, 1)
                : 0,
        ];
    })->toArray();
}

public function getDailyTrend(User $user): array
{
    $labels = [];
    $spending = [];
    $income = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);

        $labels[] = $date->format('d M');

        $spending[] = Expense::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereDate('date', $date)
            ->sum('amount');

        $income[] = IncomeEntry::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereDate('created_at', $date)
            ->sum('amount');
    }

    return compact('labels', 'spending', 'income');
}
    public function getReportData(User $user, int $month, int $year): array
    {
        // ─── Periode target & periode sebelumnya ─────────────────────────────
        $period     = Carbon::createFromDate($year, $month, 1);
        $prevPeriod = $period->copy()->subMonth();

        // ─── 1. Financial Summary (bulan target) ─────────────────────────────
        $totalIncome = IncomeEntry::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $totalExpense = Expense::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');

        $net        = $totalIncome - $totalExpense;
        $savingRate = $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1) : 0;

        // ─── 2. Bulan sebelumnya (MoM comparison) ────────────────────────────
        $prevIncome = IncomeEntry::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $prevPeriod->year)
            ->whereMonth('date', $prevPeriod->month)
            ->sum('amount');

        $prevExpense = Expense::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $prevPeriod->year)
            ->whereMonth('date', $prevPeriod->month)
            ->sum('amount');

        $prevNet        = $prevIncome - $prevExpense;
        $prevSavingRate = $prevIncome > 0 ? round((($prevIncome - $prevExpense) / $prevIncome) * 100, 1) : 0;

        // Delta % — null jika bulan lalu tidak ada data (hindari division by zero)
        $deltaIncomePct  = $prevIncome  > 0 ? round((($totalIncome  - $prevIncome)  / $prevIncome)  * 100, 1) : null;
        $deltaExpensePct = $prevExpense > 0 ? round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1) : null;

        // ─── 3. Pengeluaran per Kategori (vs Budget Bulanan) ─────────────────
        // Semua kategori yang punya budget atau punya pengeluaran di bulan ini
        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('category_id');

        $expenseByCategory = Expense::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->keyBy('category_id');

        // Gabungkan: semua kategori yang muncul di salah satu sumber
        $allCategoryIds = $budgets->keys()->merge($expenseByCategory->keys())->unique();

        $byCategory = $allCategoryIds->map(function ($catId) use ($budgets, $expenseByCategory) {
            $budget      = $budgets->get($catId);
            $expRow      = $expenseByCategory->get($catId);
            $realisasi   = $expRow ? (float) $expRow->total : 0.0;

            // monthly_budget = daily_limit × 30 (approximation standard)
            // Jika tidak ada budget, tampilkan 0 (kolom akan menampilkan —)
            $monthlyBudget = $budget ? (float) $budget->daily_limit * 30 : 0.0;

            $selisih = $monthlyBudget > 0 ? $monthlyBudget - $realisasi : 0;
            $pct     = $monthlyBudget > 0 ? round(($realisasi / $monthlyBudget) * 100, 1) : 0;

            // Nama & tipe kategori
            $cat = $budget?->category ?? $expRow?->category;

            return [
                'category_id'    => $catId,
                'category_name'  => $cat?->name  ?? 'Tidak Diketahui',
                'category_type'  => strtolower($cat?->type ?? 'other'),
                'monthly_budget' => $monthlyBudget,
                'realisasi'      => $realisasi,
                'selisih'        => $selisih,
                'pct'            => $pct,
            ];
        })->sortByDesc('realisasi')->values()->toArray();

        // ─── 4. Top 5 Pengeluaran Terbesar (individual transactions) ─────────
        $topSpending = Expense::select('description', DB::raw('SUM(amount) as total'))
            ->where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->groupBy('description')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ─── 5. Tren Harian ──────────────────────────────────────────────────
        $daysInMonth  = $period->daysInMonth;
        $dailyLabels  = [];
        $dailyData    = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailyLabels[] = $d;
            $dateStr = Carbon::createFromDate($year, $month, $d)->toDateString();

            $dailyData[] = (float) Expense::where('user_id', $user->id)
                ->where('verified_status', 'verified')
                ->whereDate('date', $dateStr)
                ->sum('amount');
        }

        // ─── 6. Health Score (dihitung untuk bulan yang dipilih) ─────────────
        $healthScore = $this->calculateHealthScoreForMonth($user, $month, $year);

        // ─── 7. Streak & Badge Info ───────────────────────────────────────────
        $streakInfo = [
            'best_streak'   => $user->best_streak ?? 0,
            'total_badges'  => UserBadge::where('user_id', $user->id)->count(),
        ];

        // ─── 8. Nama Bulan (Indonesian locale-friendly) ──────────────────────
        Carbon::setLocale('id');
        $monthName = $period->translatedFormat('F Y');

        return [
            'month_name'        => $monthName,
            'total_income'      => $totalIncome,
            'total_expense'     => $totalExpense,
            'net'               => $net,
            'saving_rate'       => $savingRate,
            'prev_income'       => $prevIncome,
            'prev_expense'      => $prevExpense,
            'prev_net'          => $prevNet,
            'prev_saving_rate'  => $prevSavingRate,
            'delta_income_pct'  => $deltaIncomePct,
            'delta_expense_pct' => $deltaExpensePct,
            'by_category'       => $byCategory,
            'top_spending'      => $topSpending,
            'daily_labels'      => $dailyLabels,
            'daily_data'        => $dailyData,
            'health_score'      => $healthScore,
            'streak_info'       => $streakInfo,
        ];
    }

    public function getTopGoals(User $user)
    {
    return FinancialGoal::where('user_id', $user->id)
        ->latest()
        ->take(3)
        ->get();
    }

    public function getAvailableBalance(User $user): float
{
    $verifiedIncome = IncomeEntry::where('user_id', $user->id)
        ->where('verified_status', 'verified')
        ->sum('amount');

    $verifiedExpense = Expense::where('user_id', $user->id)
        ->where('verified_status', 'verified')
        ->sum('amount');

    $investmentPool = InvestmentEntry::where('user_id', $user->id)
        ->sum('current_value');

    $goalAllocation = GoalContribution::whereHas('goal', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })->sum('amount');

    return max(
        0,
        $verifiedIncome
        - $verifiedExpense
        - $investmentPool
        - $goalAllocation
    );
}

    private function calculateHealthScoreForMonth(User $user, int $month, int $year): array
    {
        $period      = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $period->daysInMonth;

        // Jika bulan yang dipilih adalah bulan berjalan, batasi ke hari ini
        $isCurrentMonth = ($month === (int) now()->month && $year === (int) now()->year);
        $lastDay        = $isCurrentMonth ? now()->day : $daysInMonth;

        // ── BAR: Budget Adherence Rate ────────────────────────────────────
        $goodDays  = 0;
        $validDays = 0;
        $totalDailyBudget = Budget::where('user_id', $user->id)->sum('daily_limit');

        if ($totalDailyBudget > 0) {
            for ($d = 1; $d <= $lastDay; $d++) {
                $dateStr    = Carbon::createFromDate($year, $month, $d)->toDateString();
                $dailySpent = Expense::where('user_id', $user->id)
                    ->where('verified_status', 'verified')
                    ->whereDate('date', $dateStr)
                    ->sum('amount');

                $validDays++;
                if (($dailySpent / $totalDailyBudget) < 0.75) {
                    $goodDays++;
                }
            }
        }

        $bar = $validDays > 0 ? round(($goodDays / $validDays) * 100, 1) : 0;

        // ── SR: Saving Rate ───────────────────────────────────────────────
        $monthIncome = IncomeEntry::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('amount');

        $savingExpense = Expense::where('user_id', $user->id)
            ->where('verified_status', 'verified')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereHas('category', fn ($q) => $q->whereIn('type', ['saving', 'investment']))
            ->sum('amount');

        $sr = $monthIncome > 0 ? round(($savingExpense / $monthIncome) * 100, 1) : 0;

        // ── SC: Streak Consistency ────────────────────────────────────────
        // Gunakan best_streak user sebagai proxy konsistensi (normalized ke 30 hari)
        $sc = min(100, round((($user->best_streak ?? 0) / 30) * 100, 1));

        // ── Final Score ───────────────────────────────────────────────────
        $score = round(min(100, max(0, (0.40 * $bar) + (0.35 * $sr) + (0.25 * $sc))), 1);

        // Tier mapping sesuai spesifikasi sistem
        [$tier, $tierLabel, $tierColor] = match (true) {
            $score >= 85 => ['S', 'Financial Champion', 'indigo'],
            $score >= 70 => ['A', 'Smart Saver',        'green'],
            $score >= 55 => ['B', 'On Track',           'blue'],
            $score >= 40 => ['C', 'Needs Attention',    'yellow'],
            default      => ['D', 'Budget Alert',       'red'],
        };

        return [
            'final_score' => $score,
            'tier'        => $tier,
            'tier_label'  => $tierLabel,
            'tier_color'  => $tierColor,
            'BAR'         => $bar,
            'SR'          => $sr,
            'SC'          => $sc,
        ];
    }
}
