<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_appreciation_logs_table
 *
 * Audit trail dari semua behavioral feedback yang diberikan sistem ke user.
 * Ini adalah "memory" dari behavioral engine — setiap notifikasi,
 * apresiasi, peringatan, dan summary bulanan tersimpan di sini.
 *
 * TYPE ENUM dijelaskan:
 * - 'daily_warning'     : Spending >= 75% budget hari itu
 *                         trigger_value = spending_percentage (e.g., 82.5)
 * - 'daily_appreciation': Spending < 50% budget — excellent day
 *                         trigger_value = spending_percentage (e.g., 34.2)
 * - 'streak_badge'      : User mencapai milestone streak → dapat badge
 *                         streak_count = streak saat itu, badge_earned = badge key
 * - 'monthly_summary'   : Ringkasan bulanan dengan financial health score
 *                         trigger_value = score (e.g., 72.4)
 * - 'income_growth'     : Income bulan ini lebih besar dari bulan lalu
 *                         trigger_value = growth percentage
 *
 * ARSITEKTUR NOTE:
 * Tabel ini append-only — tidak ada UPDATE, hanya INSERT.
 * Untuk laporan "riwayat notifikasi" tinggal query ORDER BY created_at DESC.
 * Ini juga berguna untuk analisis behavioral pattern user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appreciation_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->enum('type', [
                'daily_warning',
                'daily_appreciation',
                'streak_badge',
                'monthly_summary',
                'income_growth'
            ])->comment('Tipe event behavioral yang memicu log ini');

            $table->float('trigger_value')->nullable()
                  ->comment('Nilai pemicu: spending_pct, score, growth_pct, dll');

            $table->unsignedInteger('streak_count')->default(0)
                  ->comment('Streak user pada saat log ini dibuat');

            $table->string('badge_earned', 50)->nullable()
                  ->comment('Badge key jika type=streak_badge, null untuk tipe lain');

            $table->text('message')
                  ->comment('Pesan yang ditampilkan ke user di notification center');

            $table->timestamps();

            // INDEX: dashboard notification query
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appreciation_logs');
    }
};
