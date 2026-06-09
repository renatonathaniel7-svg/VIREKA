<?php

namespace Database\Seeders;

use App\Models\InvestmentEntry;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Seeder;

/**
 * Seeder: WithdrawalRequestSeeder
 *
 * Withdrawal requests untuk user dalam survive mode.
 * Fokus pada Dewi (critical) dan Ahmad (survive) yang membutuhkan demo.
 *
 * ATURAN BISNIS YANG DISIMULASIKAN:
 * - Hanya user yang pernah di critical/survive yang punya withdrawal request
 * - amount_received bisa berbeda dari amount_requested (simulasi loss/fee)
 * - Status mix: pending, verified, completed, rejected
 */
class WithdrawalRequestSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        // Ambil user yang relevan
        $dewi  = User::where('email', 'dewi@fintrack.test')->first();
        $ahmad = User::where('email', 'ahmad@fintrack.test')->first();
        $rudi  = User::where('email', 'rudi@fintrack.test')->first();

        // ── Dewi: Critical mode — sudah request withdrawal ────────
        if ($dewi) {
            $dewiBankInvestment = InvestmentEntry::where('user_id', $dewi->id)
                                                 ->where('allocation_type', 'saving')
                                                 ->first();

            if ($dewiBankInvestment) {
                // Request 1: Completed (sudah selesai)
                WithdrawalRequest::create([
                    'user_id'             => $dewi->id,
                    'investment_entry_id' => $dewiBankInvestment->id,
                    'amount_requested'    => 1000000,
                    'amount_received'     => 980000, // Minus fee bank 20rb
                    'verification_id'     => null,
                    'status'              => 'completed',
                ]);
                $count++;

                // Request 2: Pending (baru diajukan)
                WithdrawalRequest::create([
                    'user_id'             => $dewi->id,
                    'investment_entry_id' => $dewiBankInvestment->id,
                    'amount_requested'    => 500000,
                    'amount_received'     => null, // Belum cair
                    'verification_id'     => null,
                    'status'              => 'pending',
                ]);
                $count++;
            }

            // Saham Dewi yang sedang turun
            $dewiSaham = InvestmentEntry::where('user_id', $dewi->id)
                                         ->where('allocation_type', 'investment')
                                         ->first();

            if ($dewiSaham) {
                // Request 3: Rejected (saham tidak bisa dicairkan segera)
                WithdrawalRequest::create([
                    'user_id'             => $dewi->id,
                    'investment_entry_id' => $dewiSaham->id,
                    'amount_requested'    => 1500000,
                    'amount_received'     => null,
                    'verification_id'     => null,
                    'status'              => 'rejected',
                ]);
                $count++;
            }
        }

        // ── Ahmad: Survive mode — sedang proses verifikasi ────────
        if ($ahmad) {
            $ahmadInvestment = InvestmentEntry::where('user_id', $ahmad->id)->first();

            if ($ahmadInvestment) {
                WithdrawalRequest::create([
                    'user_id'             => $ahmad->id,
                    'investment_entry_id' => $ahmadInvestment->id,
                    'amount_requested'    => 400000,
                    'amount_received'     => null,
                    'verification_id'     => null,
                    'status'              => 'verified', // Sudah diverifikasi, menunggu dana masuk
                ]);
                $count++;
            }
        }

        // ── Rudi: Normal mode — pernah withdrawal untuk rebalancing ─
        if ($rudi) {
            $rudiEmas = InvestmentEntry::where('user_id', $rudi->id)
                                        ->where('instrument', 'like', '%Emas%')
                                        ->first();

            if ($rudiEmas) {
                // Historical: sudah selesai, untuk demonstrasi alur lengkap
                WithdrawalRequest::create([
                    'user_id'             => $rudi->id,
                    'investment_entry_id' => $rudiEmas->id,
                    'amount_requested'    => 3000000,
                    'amount_received'     => 3470000, // Profit dari kenaikan emas
                    'verification_id'     => null,
                    'status'              => 'completed',
                ]);
                $count++;
            }
        }

        $this->command->info("✅ WithdrawalRequestSeeder: {$count} withdrawal requests created.");
    }
}
