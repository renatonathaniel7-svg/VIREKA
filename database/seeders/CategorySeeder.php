<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeder: CategorySeeder
 *
 * 4 kategori utama yang mencerminkan filosofi finansial FinTrack.
 * Masing-masing punya perilaku berbeda di survive mode dan budget engine.
 *
 * Ditambah sub-kategori opsional untuk lebih detail di laporan.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // ── WANT: Pengeluaran keinginan ──────────────────────
            [
                'name'        => 'Hiburan & Gaya Hidup',
                'type'        => 'want',
                'description' => 'Nonton, makan di restoran, hobi, langganan streaming. Pertama dipangkas di survive mode.',
            ],
            [
                'name'        => 'Belanja & Fashion',
                'type'        => 'want',
                'description' => 'Beli pakaian, aksesori, gadget, dan barang non-esensial.',
            ],

            // ── NEED: Pengeluaran kebutuhan ──────────────────────
            [
                'name'        => 'Makanan & Minuman',
                'type'        => 'need',
                'description' => 'Kebutuhan makan sehari-hari, bahan pokok, dan minuman. Dilindungi di survive mode.',
            ],
            [
                'name'        => 'Transportasi',
                'type'        => 'need',
                'description' => 'Ojek online, bensin, bus, KRL, parkir, dan biaya transport lainnya.',
            ],
            [
                'name'        => 'Tagihan & Utilitas',
                'type'        => 'need',
                'description' => 'Bayar listrik, air, internet, pulsa, dan tagihan bulanan wajib.',
            ],
            [
                'name'        => 'Kesehatan',
                'type'        => 'need',
                'description' => 'Obat-obatan, konsultasi dokter, dan biaya kesehatan.',
            ],

            // ── SAVING: Alokasi tabungan ─────────────────────────
            [
                'name'        => 'Tabungan Darurat',
                'type'        => 'saving',
                'description' => 'Dana darurat yang disimpan terpisah. Target: 3-6x pengeluaran bulanan.',
            ],
            [
                'name'        => 'Tabungan Tujuan',
                'type'        => 'saving',
                'description' => 'Tabungan untuk tujuan spesifik: DP rumah, liburan, pernikahan, dll.',
            ],

            // ── INVESTMENT: Alokasi investasi ────────────────────
            [
                'name'        => 'Investasi Reksa Dana',
                'type'        => 'investment',
                'description' => 'Alokasi untuk reksa dana pasar uang, pendapatan tetap, atau saham.',
            ],
            [
                'name'        => 'Investasi Emas & Obligasi',
                'type'        => 'investment',
                'description' => 'Pembelian emas Antam, ORI, SBR, atau instrumen obligasi lainnya.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $this->command->info('✅ CategorySeeder: 10 categories created (4 types covered).');
    }
}
