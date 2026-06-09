<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'investment_entry_id',
        'amount_requested',   // yang diminta user
        'amount_received',    // aktual yang diterima (dari verifikasi)
        'verification_id',    // FK ke verifications (screenshot)
        'status',             // pending | verified | completed | rejected
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
        'amount_received'  => 'decimal:2',
    ];

    // ── Local Scope ─────────────────────────────────────────────────────────
    public function scopeForUser(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    // ── Relationships ────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function investmentEntry()
    {
        return $this->belongsTo(InvestmentEntry::class);
    }

    public function verification()
    {
        return $this->belongsTo(Verification::class);
    }
}
