<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_income_entries_table
 *
 * Setiap baris = satu entri pendapatan.
 * Model income FinTrack adalah "add-on bebas" — user bisa input
 * income berapa kali pun dalam sebulan (gaji + freelance + bonus, dll).
 * Ini berbeda dari model "budget income bulanan tetap".
 *
 * RUNNING BALANCE LOGIC:
 * Total Balance = Σ(income_entries WHERE verified_status = 'verified')
 *               - Σ(expenses WHERE verified_status = 'verified')
 *
 * SHADOW BALANCE:
 * Transaksi dengan status selain 'verified' masuk ke shadow balance
 * dan TIDAK mempengaruhi: health score, streak, survive mode.
 * Shadow balance hanya ditampilkan sebagai "pending" di dashboard.
 *
 * FOREIGN KEY KE VERIFICATIONS (nullable):
 * Nullable karena income bisa diinput dulu (status=draft)
 * sebelum screenshot di-upload untuk verifikasi.
 * Alur: user input → draft → upload → verification record dibuat →
 * income_entries.verification_id diisi → status update ke pending/verified
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('source_id')
                  ->constrained('income_sources')
                  ->restrictOnDelete()
                  ->comment('FK ke income_sources — tipe sumber pendapatan');

            $table->decimal('amount', 15, 2)
                  ->comment('Nominal income dalam Rupiah, DECIMAL(15,2) untuk presisi');

            $table->date('date')
                  ->comment('Tanggal income diterima (bukan created_at)');

            $table->enum('verified_status', ['draft', 'pending', 'verified', 'flagged', 'unverified'])
                  ->default('draft')
                  ->comment('State verifikasi — hanya verified yang masuk running balance');

            // Nullable FK ke verifications
            $table->foreignId('verification_id')
                  ->nullable()
                  ->constrained('verifications')
                  ->nullOnDelete()
                  ->comment('Linked verification record, null jika belum diverifikasi');

            $table->text('note')->nullable()
                  ->comment('Catatan opsional dari user');

            $table->timestamps();

            // INDEX: query running balance paling sering filter ini
            $table->index(['user_id', 'verified_status']);
            $table->index(['user_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_entries');
    }
};
