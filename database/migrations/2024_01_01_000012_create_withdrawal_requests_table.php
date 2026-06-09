<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_withdrawal_requests_table
 *
 * Request pencairan dari investment pool.
 * Tabel ini hanya dibuat saat survive level = CRITICAL dan
 * user secara eksplisit memilih untuk request withdrawal.
 *
 * CRITICAL DESIGN RULE:
 * Withdrawal TIDAK PERNAH otomatis.
 * User harus:
 * 1. Melihat saran di dashboard (survive mode = CRITICAL)
 * 2. Klik "Request Withdrawal" secara manual
 * 3. Input amount_requested
 * 4. Upload bukti (screenshot konfirmasi dari platform investasi)
 * 5. Verifikasi AI memproses screenshot
 * 6. Admin/sistem approve → status = completed
 *
 * amount_received vs amount_requested:
 * Bisa berbeda karena:
 * - Investment loss (saham turun)
 * - Biaya pencairan
 * - Pajak capital gain
 * Perbedaan ini dicatat untuk akurasi running balance.
 * Yang masuk ke liquid_balance adalah amount_RECEIVED, bukan requested.
 *
 * FLOW STATUS:
 * pending → verified (screenshot OK) → completed (dana masuk)
 * pending → rejected (alasan ditolak)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('investment_entry_id')
                  ->constrained('investment_entries')
                  ->restrictOnDelete()
                  ->comment('Investment entry yang akan dicairkan');

            $table->decimal('amount_requested', 15, 2)
                  ->comment('Jumlah yang diminta user untuk dicairkan');

            $table->decimal('amount_received', 15, 2)->nullable()
                  ->comment('Jumlah actual yang diterima (bisa berbeda karena loss/fee)');

            $table->foreignId('verification_id')
                  ->nullable()
                  ->constrained('verifications')
                  ->nullOnDelete()
                  ->comment('Bukti screenshot konfirmasi pencairan dari platform');

            $table->enum('status', ['pending', 'verified', 'completed', 'rejected'])
                  ->default('pending')
                  ->comment('Flow: pending→verified→completed atau pending→rejected');

            $table->timestamps();

            // INDEX: lookup withdrawal per user + status
            $table->index(['user_id', 'status']);
            $table->index('investment_entry_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
