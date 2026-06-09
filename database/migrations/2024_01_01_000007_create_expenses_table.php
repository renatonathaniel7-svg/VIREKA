<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_expenses_table
 *
 * Tabel pengeluaran — tabel dengan volume transaksi tertinggi.
 * Setiap pengeluaran terhubung ke category (want/need/saving/investment)
 * yang menentukan perilaku survive mode dan laporan.
 *
 * BEHAVIORAL ENGINE CONSEQUENCE:
 * Setiap expense yang 'verified' akan:
 * 1. Mengurangi liquid_balance (running balance calculation)
 * 2. Dibandingkan dengan daily budget → trigger appreciation_log
 * 3. Jika total hari spending >= 75% budget → reset streak
 *
 * DAILY THRESHOLD CHECK (cron/event):
 * Total expenses hari ini / daily_budget untuk kategori tersebut:
 * - < 50%     → excellent → daily_appreciation log
 * - 50-75%    → good
 * - 75-100%   → warning → daily_warning log
 * - > 100%    → danger + streak reset
 *
 * SHADOW BALANCE untuk cash transaction:
 * User boleh input expense tanpa screenshot (cash).
 * Status akan tetap 'unverified' dan masuk shadow balance.
 * Shadow expenses tetap dicatat untuk tracking tapi tidak
 * mempengaruhi health score dan streak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->restrictOnDelete()
                  ->comment('FK ke categories — menentukan want/need/saving/investment');

            $table->decimal('amount', 15, 2)
                  ->comment('Nominal pengeluaran dalam Rupiah');

            $table->string('description', 255)
                  ->comment('Deskripsi wajib untuk setiap pengeluaran');

            $table->date('date')
                  ->comment('Tanggal pengeluaran terjadi');

            $table->enum('verified_status', ['draft', 'pending', 'verified', 'flagged', 'unverified'])
                  ->default('draft')
                  ->comment('Sama dengan income — hanya verified yang masuk running balance');

            $table->foreignId('verification_id')
                  ->nullable()
                  ->constrained('verifications')
                  ->nullOnDelete()
                  ->comment('Linked verification, null jika cash/belum verifikasi');

            $table->timestamps();

            // INDEX: paling sering di-query untuk dashboard harian & running balance
            $table->index(['user_id', 'verified_status']);
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'category_id']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
