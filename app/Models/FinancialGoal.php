<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FinancialGoal extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'target_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contributions()
    {
        return $this->hasMany(GoalContribution::class, 'goal_id');
    }

    public function getCollectedAmountAttribute()
    {
        return $this->contributions()->sum('amount');
    }

    public function getProgressAttribute()
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return min(
            100,
            round(($this->collected_amount / $this->target_amount) * 100)
        );
    }
    
}