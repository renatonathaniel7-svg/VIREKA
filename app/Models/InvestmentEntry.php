<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'allocation_type',  // saving | investment
        'instrument',       // nama instrumen (opsional)
        'initial_amount',   // modal awal — IMMUTABLE setelah create
        'current_value',    // nilai terkini — mutable
        'return_pct',       // persentase return — auto-calculated
        'note',
    ];

    protected $casts = [
        'initial_amount' => 'decimal:2',
        'current_value'  => 'decimal:2',
        'return_pct'     => 'decimal:4',
    ];

    // ── Local Scope ─────────────────────────────────────────────────────────
    /**
     * Filter investment entry milik user yang sedang login.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeForUser(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    // ── Relationships ────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────
    /**
     * Hitung return amount secara langsung dari model.
     */
    public function getReturnAmountAttribute(): float
    {
        return (float) $this->current_value - (float) $this->initial_amount;
    }
}
