<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_budgets_table
 *
 * Budget harian per kategori per user per bulan.
 *
 * MENGAPA DAILY LIMIT, BUKAN MONTHLY?
 * Karena behavioral engine bekerja per-hari.
 * Daily threshold check (< 50%, 50-75%, dll) membutuhkan
 * baseline harian, bukan bulanan.
 * User yang mau set monthly budget bisa dibagi 30 di UI layer.
 *
 * MENGAPA ADA month + year?
 * Budget bisa berubah tiap bulan — survive mode memangkas
 * budget 'want' secara dinamis saat level CAUTION/SURVIVE.
 * Dengan month+year, perubahan budget tersimpan sebagai record baru
 * (audit trail) bukan overwrite.
 *
 * COMPOSITE UNIQUE:
 * (user_id, category_id, month, year) harus unique —
 * satu budget per kategori per bulan per user.
 * Jika perlu update, lakukan UPDATE bukan INSERT baru
 * (kecuali survive mode adjustment yang bisa disimpan terpisah).
 *
 * SURVIVE MODE INTEGRATION:
 * Cron job survive mode akan membaca daily_limit dari tabel ini
 * dan membandingkan dengan total expenses hari itu.
 * Jika survive level CAUTION: effective_limit = daily_limit * 0.80
 * Jika SURVIVE: effective_limit = daily_limit * 0.60 (khusus 'want')
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete();

            $table->decimal('daily_limit', 15, 2)
                  ->comment('Batas pengeluaran harian dalam Rupiah untuk kategori ini');

            $table->unsignedTinyInteger('month')
                  ->comment('Bulan 1-12');
            $table->unsignedSmallInteger('year')
                  ->comment('Tahun: 2024, 2025, dll');

            $table->timestamps();

            // COMPOSITE UNIQUE: satu budget per user per kategori per bulan
            $table->unique(['user_id', 'category_id', 'month', 'year'],
                           'budgets_user_category_period_unique');

            // INDEX: query budget aktif bulan ini
            $table->index(['user_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
