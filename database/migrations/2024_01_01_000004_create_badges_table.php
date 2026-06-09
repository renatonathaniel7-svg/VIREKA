<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_badges_table
 *
 * Tabel master badge yang tersedia dalam sistem.
 * Ini adalah catalog badge — data statis yang di-seed sekali.
 *
 * Badge milestones berdasarkan streak:
 * - 3  hari  → STARTER  (Starter Saver)
 * - 7  hari  → SMART    (Smart Spender)
 * - 14 hari  → MASTER   (Budget Master)
 * - 30 hari  → ELITE    (Financial Discipline)
 * - 60 hari  → LEGEND   (Money Legend)
 *
 * ARSITEKTUR NOTE:
 * badge_key adalah identifier unik yang digunakan di kode
 * untuk lookup badge tanpa hardcode ID numerik.
 * Ini adalah best practice untuk seed data yang stabil —
 * ID bisa berubah di environment berbeda, badge_key tidak.
 *
 * Badge bersifat PERMANEN — tidak hilang meskipun streak reset.
 * Ini adalah keputusan desain psikologis:
 * memberikan sense of achievement yang lasting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('badge_key', 50)->unique()
                  ->comment('Identifier unik: STARTER, SMART, MASTER, ELITE, LEGEND');
            $table->string('name', 100)->comment('Nama badge yang ditampilkan ke user');
            $table->text('description')->comment('Deskripsi pencapaian badge');
            $table->unsignedInteger('streak_required')
                  ->comment('Jumlah hari streak berturut-turut yang dibutuhkan');
            $table->string('icon', 50)->comment('Nama icon (Heroicons/emoji key)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
