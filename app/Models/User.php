<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'survive_level',     // normal | caution | survive | critical
        'current_streak',    // int: hari berturut-turut di bawah threshold
        'best_streak',       // int: streak terbaik all-time
        'last_active_date',  // date: tanggal terakhir ada verified expense
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_active_date'  => 'date',
        'password'          => 'hashed',
        'current_streak'    => 'integer',
        'best_streak'       => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function incomeEntries()
    {
        return $this->hasMany(IncomeEntry::class);
    }

    public function investmentEntries()
    {
        return $this->hasMany(InvestmentEntry::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function appreciationLogs()
    {
        return $this->hasMany(AppreciationLog::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function financialGoals()
    {
    return $this->hasMany(FinancialGoal::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot(['earned_at'])
            ->withTimestamps();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    public function hasBadge(string $badgeSlug): bool
    {
        return $this->badges()->where('slug', $badgeSlug)->exists();
    }

    public function isInSurviveMode(): bool
    {
        return $this->survive_level !== 'normal';
    }

    public function isWantFrozen(): bool
    {
        return in_array($this->survive_level, ['survive', 'critical']);
    }
}
