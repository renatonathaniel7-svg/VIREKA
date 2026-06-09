<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalContribution extends Model
{
    protected $fillable = [
        'goal_id',
        'amount',
        'note',
    ];

    public function goal()
    {
        return $this->belongsTo(FinancialGoal::class);
    }
}