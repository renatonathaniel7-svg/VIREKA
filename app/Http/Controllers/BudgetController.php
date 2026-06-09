<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Http\Requests\BudgetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * BudgetController
 *
 * Manages daily budget limits per category per user per month.
 *
 * ARCHITECTURE NOTES:
 * - Budget is per-user, per-category, per-month (unique constraint in DB)
 * - Today's spending is calculated from VERIFIED expenses only
 * - Progress bar thresholds match the behavioral engine spec:
 *     <50%  → excellent (green)
 *     50–75% → good (yellow)
 *     75–100% → warning (orange)
 *     >100%  → danger (red)
 *
 * WHY daily_limit not monthly_limit?
 * FinTrack's behavioral engine operates on a daily spend comparison.
 * The streak system, survive mode triggers, and health score all
 * use daily spending vs daily limit — not a monthly bucket that
 * resets once empty. Daily granularity creates more frequent
 * behavioral feedback loops.
 */
class BudgetController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    /**
     * Show all budgets for the current month + inline form for adding new ones.
     *
     * WHY load today's spending here?
     * The budget index doubles as the live budget monitor. Users need to
     * see current-day progress instantly — this is the behaviorally active
     * feedback surface of the app.
     */
    public function index()
    {
        $userId = Auth::id();
        $year   = now()->year;
        $month  = now()->month;

        // Budgets set for this month
        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        // Today's verified spending per category
        // ONLY verified — shadow balance must not pollute budget display
        $todaySpending = Expense::where('user_id', $userId)
            ->where('verified_status', 'verified')
            ->whereDate('date', now()->toDateString())
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        // Enrich budgets with today's progress data
        $budgets = $budgets->map(function ($budget) use ($todaySpending) {
            $spent      = $todaySpending[$budget->category_id] ?? 0;
            $percentage = $budget->daily_limit > 0
                ? round(($spent / $budget->daily_limit) * 100, 1)
                : 0;

            $budget->today_spent  = $spent;
            $budget->percentage   = $percentage;
            $budget->remaining    = max(0, $budget->daily_limit - $spent);
            $budget->status_class = $this->resolveProgressClass($percentage);

            return $budget;
        });

        // Categories that don't yet have a budget this month (for the add form)
        $budgetCategoryIds  = $budgets->pluck('category_id')->toArray();
        $remainingCategories = Category::whereNotIn('id', $budgetCategoryIds)
            ->orderBy('name')
            ->get();

        return view('budgets.index', compact(
            'budgets',
            'remainingCategories',
            'year',
            'month'
        ));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    /**
     * Create a new budget for a category this month.
     *
     * WHY enforce 1 budget per category per user per month?
     * Multiple budgets for the same category would create ambiguity in
     * the behavioral engine — which limit does the streak system compare against?
     * The DB has a unique constraint; we check here for a user-friendly error.
     */
    public function store(BudgetRequest $request)
    {
        $userId = Auth::id();
        $year   = now()->year;
        $month  = now()->month;

        $exists = Budget::where('user_id', $userId)
            ->where('category_id', $request->category_id)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();

        if ($exists) {
            return redirect()->route('budgets.index')
                ->with('error', 'Budget untuk kategori ini sudah ada di bulan ini. Gunakan tombol Edit untuk mengubahnya.');
        }

        Budget::create([
            'user_id'     => $userId,
            'category_id' => $request->category_id,
            'daily_limit' => $request->daily_limit,
            'year'        => $year,
            'month'       => $month,
        ]);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget berhasil ditambahkan.');
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    /**
     * Update daily_limit only.
     *
     * WHY only daily_limit?
     * Category, year, and month define the budget's identity.
     * Changing them would be semantically equivalent to creating a new budget.
     * Inline edit in the table only needs to adjust the spending limit.
     */
    public function update(BudgetRequest $request, int $id)
    {
        $budget = Budget::where('user_id', Auth::id())->findOrFail($id);

        $budget->update([
            'daily_limit' => $request->daily_limit,
        ]);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget berhasil diperbarui.');
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Resolve Tailwind color class based on spending percentage.
     * Thresholds mirror the behavioral engine spec exactly.
     */
    private function resolveProgressClass(float $percentage): string
    {
        if ($percentage < 50) {
            return 'bg-emerald-500'; // Excellent
        } elseif ($percentage < 75) {
            return 'bg-yellow-400';  // Good
        } elseif ($percentage <= 100) {
            return 'bg-orange-500';  // Warning
        } else {
            return 'bg-red-600';     // Danger
        }
    }
}
