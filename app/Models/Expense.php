<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'description',
        'date',
        'verified_status',   // draft | pending | verified | flagged | unverified
        'verification_id',
        'payment_method',    // cash | transfer | qris | card
        'screenshot_path',
        'note',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function verification()
{
    return $this->hasOne(
        Verification::class,
        'reference_id',
        'id'
    )->where(
        'reference_type',
        'expense'
    );
}
}
