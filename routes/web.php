<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StreakController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\FinancialGoalController;
use Illuminate\Support\Facades\Route;

// ── Root redirect ──────────────────────────────────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('auth.login');
});

// ── Auth (guest only) ──────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'login'])->name('auth.login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->name('auth.login.post');
    Route::get('/register',  [AuthController::class, 'register'])->name('auth.register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('auth.register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// ── Authenticated routes ───────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard & Laporan
    Route::get('/dashboard',        [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/report', [DashboardController::class, 'report'])->name('dashboard.report');
    Route::get('/dashboard/report/pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.report.pdf');

    // Expenses
    Route::get('/expenses',             [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create',      [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses',            [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{id}',        [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('/expenses/{id}/edit',   [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{id}',        [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{id}',     [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Income
    Route::get('/income',             [IncomeController::class, 'index'])->name('income.index');
    Route::get('/income/create',      [IncomeController::class, 'create'])->name('income.create');
    Route::post('/income',            [IncomeController::class, 'store'])->name('income.store');
    Route::get('/income/{id}',        [IncomeController::class, 'show'])->name('income.show');
    Route::get('/income/{id}/edit',   [IncomeController::class, 'edit'])->name('income.edit');
    Route::put('/income/{id}',        [IncomeController::class, 'update'])->name('income.update');
    Route::delete('/income/{id}',     [IncomeController::class, 'destroy'])->name('income.destroy');

    // Budgets
    Route::get('/budgets',       [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('/budgets',      [BudgetController::class, 'store'])->name('budgets.store');
    Route::put('/budgets/{id}',  [BudgetController::class, 'update'])->name('budgets.update');

    Route::get('/goals', [FinancialGoalController::class, 'index'])->name('goals.index');
     Route::post('/goals', [FinancialGoalController::class, 'store'])->name('goals.store');
     Route::delete('/goals/{goal}', [FinancialGoalController::class, 'destroy'])->name('goals.destroy');
     Route::get('/goals/{goal}/contribute',[FinancialGoalController::class, 'showContributionForm'])->name('goals.contribute');
     Route::post('/goals/{goal}/contribute',[FinancialGoalController::class, 'storeContribution'])->name('goals.contribute.store');
     Route::get('/goals/create', [FinancialGoalController::class, 'create'])->name('goals.create');

    // Verifications
    Route::get('/verify/{type}/{id}',[VerificationController::class, 'create'])
          ->name('verifications.create')->where('type', 'expense|income|withdrawal');
    Route::post('/verify/{type}/{id}',  [VerificationController::class, 'store'])
         ->name('verifications.store')->where('type', 'expense|income|withdrawal');
    Route::get('/verifications/{id}',   [VerificationController::class, 'show'])
         ->name('verifications.show');
    Route::post('/verifications/{id}/retry', [VerificationController::class, 'retry'])
         ->name('verifications.retry');
    Route::post('/verify/{type}/{id}/cash', [VerificationController::class, 'markAsCash'])
         ->name('verifications.cash')->where('type', 'expense|income|withdrawal');
     Route::post('/verifications/{id}/confirm',[VerificationController::class, 'confirm']
)         ->name('verifications.confirm');
     Route::get('/verifications/{id}/reupload',[VerificationController::class, 'reupload']
)         ->name('verifications.reupload');
     Route::post('/verifications/{id}/flag',[VerificationController::class, 'flag']
)         ->name('verifications.flag');

    // Streak
    Route::get('/streak', [StreakController::class, 'index'])->name('streak.index');

    // Badges
    Route::get('/badges', [BadgeController::class, 'index'])->name('badges.index');

    // Notifications
    Route::get('/notifications',            [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Investments
    Route::resource('investments', InvestmentController::class);

    // Withdrawals
    Route::get('investments/{investment}/withdraw',  [WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('investments/{investment}/withdraw', [WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::post('withdrawals/{withdrawal}/complete', [WithdrawalController::class, 'complete'])->name('withdrawals.complete');
});
