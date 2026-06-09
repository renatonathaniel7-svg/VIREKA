<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'source_id',
    'amount',
    'date',
    'note',
    'verified_status',
    'verification_id',
];

    protected $casts = [
        'amount'      => 'decimal:2',
        'data' => 'date',
    ];

    // ── Local Scopes ─────────────────────────────────────────────────────────
    public function scopeForUser(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verified_status', 'verified');
    }

    public function scopeShadow(Builder $query): Builder
    {
        return $query->whereIn('verified_status', ['pending', 'draft', 'unverified']);
    }

    // ── Relationships ────────────────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }

public function incomeSource()
{
    return $this->belongsTo(IncomeSource::class);
}

public function source()
{
    return $this->belongsTo(
        IncomeSource::class,
        'source_id',
        'id'
    );
}

    public function verification()
    {
    return $this->hasOne(Verification::class, 'reference_id', 'id')
                ->where('reference_type', 'income');
    }
}
