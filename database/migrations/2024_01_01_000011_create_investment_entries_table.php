<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_investment_entries_table
 *
 * Investment Pool — bagian dari liquid balance yang "dikunci" untuk
 * tujuan finansial jangka panjang.
 *
 * POOL SEPARATION PHILOSOPHY:
 * Liquid Balance dan Investment Pool HARUS dipisah.
 * Alasan:
 * 1. Likuiditas berbeda — investment tidak bisa dipakai langsung
 * 2. Return/loss perlu di-track terpisah
 * 3. Withdrawal butuh approval flow (withdrawal_requests)
 * 4. Survive mode hanya saran withdrawal di level CRITICAL,
 *    bukan otomatis liquidate
 *
 * allocation_type:
 * - 'saving'    : Tabungan rutin — bisa lebih mudah dicairkan
 * - 'investment': Investasi aktif (saham, reksa dana, emas, ORI)
 *                 Return bersifat estimatif (input manual user)
 *
 * return_pct:
 * Return percentage adalah INPUT MANUAL USER, bukan dari API real-time.
 * Ini adalah keputusan desain untuk scope TA — harus dilabel
 * sebagai "estimasi" di UI.
 *
 * current_value vs initial_amount:
 * current_value = initial_amount × (1 + return_pct/100)
 * Tapi disimpan terpisah agar user bisa update current_value
 * tanpa mengubah initial_amount (audit trail).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->enum('allocation_type', ['saving', 'investment'])
                  ->comment('Tipe: tabungan biasa atau investasi aktif');

            $table->string('instrument', 100)->nullable()
                  ->comment('Instrumen investasi: Emas, Reksa Dana, Saham, Deposito, ORI');

            $table->decimal('initial_amount', 15, 2)
                  ->comment('Jumlah awal yang dialokasikan');

            $table->decimal('current_value', 15, 2)
                  ->comment('Nilai saat ini — diupdate manual oleh user');

            $table->float('return_pct')->default(0)
                  ->comment('Estimasi return % — INPUT MANUAL, bukan real-time API');

            $table->text('note')->nullable()
                  ->comment('Catatan: tujuan investasi, platform, dll');

            $table->timestamps();

            // INDEX: total investment pool per user
            $table->index(['user_id', 'allocation_type']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_entries');
    }
};
