<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_verifications_table
 *
 * MENGAPA DIBUAT SEBELUM income_entries DAN expenses?
 * Karena income_entries.verification_id dan expenses.verification_id
 * adalah FK nullable ke tabel ini. Jika tabel ini belum ada,
 * migration selanjutnya akan gagal karena FK reference tidak ditemukan.
 *
 * Tabel ini adalah AI Verification Layer — inti dari
 * "friction design" FinTrack yang membedakannya dari app CRUD biasa.
 *
 * Manual Polymorphic Design (bukan Laravel morphMap):
 * Gunakan reference_type ENUM + reference_id BIGINT
 * sebagai alternatif eksplisit dari morph Laravel.
 *
 * ALASAN tidak pakai morphMap:
 * 1. Lebih eksplisit dan mudah dijelaskan di laporan TA
 * 2. Query langsung lebih readable
 * 3. ENUM membatasi nilai valid secara database-level
 *
 * Flow verifikasi:
 * user input → draft → upload screenshot → pending →
 * Gemini analysis → verified/flagged → jika flagged → manual review
 *
 * Toleransi 5%: jika AI confidence >= 0.95 DAN delta amount <= 5%,
 * status otomatis menjadi 'verified'.
 * Jika tidak, menjadi 'flagged' untuk review manual.
 *
 * PENTING (untuk laporan TA):
 * Sistem ini adalah "friction layer", bukan fraud detection.
 * Screenshot bisa dimanipulasi. Limitation ini harus
 * didokumentasikan secara eksplisit di laporan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->comment('User pemilik transaksi yang diverifikasi');

            // Manual Polymorphic: reference ke income_entries atau expenses
            $table->enum('reference_type', ['income', 'expense'])
                  ->comment('Tipe transaksi yang diverifikasi');
            $table->unsignedBigInteger('reference_id')
                  ->comment('ID dari income_entries atau expenses');

            $table->string('screenshot_path', 255)
                  ->comment('Path file screenshot di storage/app/public/verifications/');

            // AI Extraction Result
            $table->json('ai_extracted_data')->nullable()
                  ->comment('Data yang diekstrak Gemini: {amount, date, source, raw_text}');
            $table->float('ai_confidence')->nullable()
                  ->comment('Confidence score Gemini 0.0-1.0, null jika belum diproses');

            $table->enum('status', ['draft', 'pending', 'verified', 'flagged', 'unverified'])
                  ->default('draft')
                  ->comment('State machine: draft→pending→verified/flagged/unverified');

            $table->text('flag_reason')->nullable()
                  ->comment('Alasan flag jika status=flagged: delta_exceeded, low_confidence, dll');

            $table->timestamps();

            // INDEX: sering di-query untuk lookup verifikasi pending
            $table->index(['user_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
