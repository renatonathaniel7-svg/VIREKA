<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Category Model
 *
 * Kategori pengeluaran dengan tipe:
 *   - need       : kebutuhan primer (makan, transport, utilitas)
 *   - want       : keinginan (hiburan, fashion, dining out)
 *   - saving     : tabungan konvensional
 *   - investment : instrumen investasi
 *
 * Tipe 'want' akan dibekukan saat survive_level = 'survive' atau 'critical'.
 * Ini adalah inti dari Survive Mode adaptive budgeting.
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',   // null = kategori system/default; ada nilai = kategori custom user
        'name',
        'type',      // need | want | saving | investment
        'icon',
        'color',
        'is_system', // true = tidak bisa dihapus user
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}
