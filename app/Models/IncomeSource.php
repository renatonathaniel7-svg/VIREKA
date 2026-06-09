<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * IncomeSource Model
 *
 * Kategori sumber pendapatan.
 * Contoh: Gaji, Freelance, Bonus, Dividen, Bisnis, Hadiah.
 *
 * Relasi 1-N: satu income source bisa punya banyak income_entries.
 * Normalisasi: nama sumber tidak disimpan redundan di income_entries.
 */
class IncomeSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',    // null = sumber system/default
        'name',
        'icon',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function incomeEntries()
    {
        return $this->hasMany(IncomeEntry::class);
    }
}
