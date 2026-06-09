<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_user_badges_table
 *
 * TABEL PIVOT M-N: users ↔ badges
 *
 * Ini memenuhi requirement akademik "relasi Many-to-Many"
 * yang diwajibkan di tugas akhir.
 *
 * MENGAPA M-N dan bukan 1-N dengan kolom di users?
 * - Satu user bisa punya BANYAK badge (earned over time)
 * - Satu badge bisa dimiliki BANYAK user
 * - Data earned_at per-kombinasi user-badge tidak bisa disimpan
 *   di salah satu tabel induk tanpa denormalisasi
 *
 * COMPOSITE UNIQUE (user_id, badge_id):
 * Memastikan seorang user tidak bisa mendapat badge yang sama dua kali.
 * Ini mencerminkan filosofi badge "permanent achievement" FinTrack.
 *
 * earned_at vs created_at:
 * Kita punya keduanya. earned_at adalah timestamp spesifik
 * kapan badge diraih (bisa di-set secara explicit dari cron job),
 * sedangkan created_at adalah kapan record pivot dibuat.
 * Dalam praktiknya akan sama, tapi memisahkan ini
 * memberikan fleksibilitas untuk retroactive badge assignment.
 *
 * Relasi Eloquent di User model:
 * $this->belongsToMany(Badge::class, 'user_badges')
 *      ->withPivot('earned_at')
 *      ->withTimestamps();
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('badge_id')
                  ->constrained('badges')
                  ->cascadeOnDelete();

            $table->timestamp('earned_at')
                  ->comment('Kapan badge ini diraih — explicit timestamp dari behavioral engine');

            $table->timestamps();

            // COMPOSITE UNIQUE: seorang user tidak bisa punya badge yang sama dua kali
            $table->unique(['user_id', 'badge_id'], 'user_badges_unique');

            // INDEX untuk query "semua badge milik user X"
            $table->index('user_id');
            $table->index('badge_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
