<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\IncomeEntry;
use App\Policies\ExpensePolicy;
use App\Policies\IncomePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * AuthServiceProvider
 *
 * Mendaftarkan semua Policy ke model yang berkaitan.
 * Laravel akan otomatis resolve policy saat authorize() dipanggil di controller.
 *
 * Convention: ModelName → ModelNamePolicy (auto-discovery juga bisa dipakai,
 * tapi explicit registration lebih mudah dipahami untuk keperluan TA).
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model → Policy mapping.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Expense::class     => ExpensePolicy::class,
        IncomeEntry::class => IncomePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
