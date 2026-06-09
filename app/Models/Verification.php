<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference_type',
        'reference_id',
        'screenshot_path',   
        'status',           
        'extracted_amount',  
        'extracted_date',   
        'extracted_source',  
        'confidence_score', 
        'delta_pct',        
        'method',            
        'raw_response',      
        'reviewer_note',      
    ];

    protected $casts = [
        'extracted_amount'  => 'decimal:2',
        'extracted_date'    => 'date',
        'confidence_score'  => 'float',
        'delta_pct'         => 'float',
    ];

    // ── Relationships ────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }
public function expense()
{
    return $this->belongsTo(
        Expense::class,
        'reference_id'
    )->where(
        'reference_type',
        'expense'
    );
}

public function income()
{
    return $this->belongsTo(
        IncomeEntry::class,
        'reference_id'
    )->where(
        'reference_type',
        'income'
    );
}
    // ── Helpers ──────────────────────────────────────────────────────────────
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isFlagged(): bool
    {
        return $this->status === 'flagged';
    }
}
