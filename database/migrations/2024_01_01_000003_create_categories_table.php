<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_categories_table
 *
 * Tabel master kategori pengeluaran dengan 4 type yang mencerminkan
 * filosofi finansial FinTrack:
 *
 * - 'want'       : Pengeluaran keinginan (hiburan, makan enak, dll)
 *                  Target survive mode — yang pertama dipangkas saat CAUTION
 * - 'need'       : Pengeluaran kebutuhan (transport, listrik, makanan pokok)
 *                  Dilindungi di SURVIVE level
 * - 'saving'     : Alokasi tabungan
 *                  Masuk ke investment pool sebagai liquid saving
 * - 'investment' : Alokasi investasi aktif (saham, reksa dana, emas)
 *                  Masuk ke investment pool, butuh withdrawal request
 *
 * ARSITEKTUR NOTE:
 * Type ENUM ini adalah inti dari survive mode logic.
 * Cron job survive mode membaca type ini untuk menentukan
 * kategori mana yang di-freeze atau di-block.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Nama kategori: Makanan, Transport, Hiburan, dll');
            $table->enum('type', ['want', 'need', 'saving', 'investment'])
                  ->comment('Klasifikasi finansial — digunakan survive mode engine');
            $table->text('description')->nullable();
            $table->timestamps();

            // INDEX: type sering di-query untuk filter survive mode
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
