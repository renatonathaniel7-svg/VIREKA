<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_users_table
 *
 * Tabel users adalah tabel pusat sistem FinTrack.
 * Semua entitas lain (income, expense, budget, badge, dll)
 * berelasi 1-N atau M-N ke tabel ini.
 *
 * Kolom tambahan dari Laravel default:
 * - current_streak, best_streak: untuk behavioral engine
 * - grace_days: allowance 1 hari tanpa transaksi tidak reset streak
 * - survive_level: state machine survive mode (NORMAL → CRITICAL)
 *
 * ARSITEKTUR NOTE:
 * survive_level disimpan di users (bukan dihitung runtime)
 * agar cron job bisa update sekali per hari dan query dashboard
 * langsung baca dari kolom ini tanpa rekalkulasi berat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Behavioral Engine Fields
            $table->unsignedInteger('current_streak')->default(0)
                  ->comment('Jumlah hari berturut-turut spending < 75% budget');
            $table->unsignedInteger('best_streak')->default(0)
                  ->comment('Streak terpanjang sepanjang masa — tidak pernah berkurang');
            $table->unsignedTinyInteger('grace_days')->default(0)
                  ->comment('Counter hari tanpa transaksi yang masih dimaafkan (max 1)');

            // Survive Mode State
            $table->enum('survive_level', ['normal', 'caution', 'survive', 'critical'])
                  ->default('normal')
                  ->comment('Level survive mode, diupdate harian via cron job');

            $table->rememberToken();
            $table->timestamps();

            // INDEX: survive_level untuk filter dashboard & cron job
            $table->index('survive_level');
            // email sudah unique — otomatis ada index
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
