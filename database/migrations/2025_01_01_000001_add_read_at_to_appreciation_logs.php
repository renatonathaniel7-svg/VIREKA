<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: add_read_at_to_appreciation_logs
 *
 * WHY:
 * Kolom read_at diperlukan untuk fitur notifikasi — membedakan antara
 * notifikasi yang sudah dibaca vs belum dibaca tanpa harus membuat
 * tabel pivot terpisah. NULL = belum dibaca, timestamp = sudah dibaca.
 *
 * Pendekatan nullable timestamp lebih efisien dari boolean karena:
 * 1. Bisa diquery dengan whereNull / whereNotNull
 * 2. Menyimpan kapan user membaca (audit trail ringan)
 * 3. IS NULL index friendly di MySQL 8
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appreciation_logs', function (Blueprint $table) {
            // Ditambahkan setelah created_at agar urutan kolom logis
            $table->timestamp('read_at')->nullable()->after('created_at');

            // Index partial agar query "belum dibaca" tetap cepat
            // MySQL tidak support partial index langsung, tapi index biasa
            // pada nullable column sudah efisien karena NULL rows dikecualikan
            // secara internal oleh optimizer
            $table->index(['user_id', 'read_at'], 'idx_appreciation_user_read');
        });
    }

    public function down(): void
    {
        Schema::table('appreciation_logs', function (Blueprint $table) {
            $table->dropIndex('idx_appreciation_user_read');
            $table->dropColumn('read_at');
        });
    }
};
