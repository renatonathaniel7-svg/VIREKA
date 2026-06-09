<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\SurviveModeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService   $dashboardService,
        private SurviveModeService $surviveModeService,
    ) {}


    public function index(): View
    {
        $user = auth()->user();

        //  Step 1: Update survive level
        $this->surviveModeService->updateUserLevel($user);

        // Step 2: Balance Summary
        $balanceSummary = $this->dashboardService->getBalanceSummary($user);

        // Step 3: Daily Budget Status (dengan survive multiplier) 
        $dailyBudgetStatus = $this->dashboardService->getDailyBudgetStatus($user);

        //  Step 4: Financial Health Score
        $healthScore = $this->dashboardService->getHealthScore($user);

        //  Step 5: Behavioral Stats (streak, badges, appreciation) 
        $behavioralStats = $this->dashboardService->getBehavioralStats($user);

        //  Step 6: Chart Data 
        $chartData = $this->dashboardService->getChartData($user);

        //  Step 7: Top Spending Categories (bulan ini) 
        $topCategories = $this->dashboardService->getTopSpendingCategories($user);

$categorySplit = $this->dashboardService->getCategorySplit($user);

$dailyTrend = $this->dashboardService->getDailyTrend($user);
$topGoals = $this->dashboardService->getTopGoals($user);

        $incomeGrowth = null;

        // Step 8: Survive Mode Info 
        $surviveInfo = [
            'level'         => $user->fresh()->survive_level, // fresh() untuk level terbaru
            'message'       => $this->surviveModeService->getLevelMessage($user->survive_level),
            'colors'        => $this->surviveModeService->getLevelColors($user->survive_level),
            'is_active'     => $this->surviveModeService->isActive($user),
            'want_frozen'   => $this->surviveModeService->isWantCategoryFrozen($user),
            'avg_expense'   => $this->surviveModeService->getAvgMonthlyExpense($user),
        ];

        return view('dashboard.index', compact(
            'balanceSummary',
            'dailyBudgetStatus',
            'healthScore',
            'behavioralStats',
            'chartData',
            'topCategories',
            'categorySplit',
            'dailyTrend',
            'topGoals',
            'incomeGrowth',
            'surviveInfo',
        ));
    }

    public function showContributionForm(FinancialGoal $goal)
{
    $balance = $this->dashboardService
        ->getAvailableBalance(auth()->user());

    return view(
        'goals.contribute',
        compact('goal', 'balance')
    );
}
}
