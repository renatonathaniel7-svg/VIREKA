<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_income_sources_table
 *
 * Tabel lookup/master untuk sumber pendapatan.
 * Dipisah dari income_entries karena:
 * 1. Normalisasi — nama sumber tidak direplikasi di setiap entri
 * 2. Satu user bisa punya banyak income dari sumber yang sama
 * 3. Memudahkan laporan agregasi per sumber (e.g., total dari Freelance)
 *
 * ARSITEKTUR NOTE:
 * Ini adalah shared master data — tidak per-user.
 * Semua user pakai income_sources yang sama (Gaji, Freelance, dll).
 * Jika perlu per-user custom sources, tambahkan kolom user_id nullable
 * di fase pengembangan selanjutnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nama sumber income: Gaji, Freelance, Bonus, dll');
            $table->text('description')->nullable()->comment('Deskripsi opsional sumber income');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_sources');
    }
};
