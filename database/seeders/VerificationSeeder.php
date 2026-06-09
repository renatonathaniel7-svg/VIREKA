<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\IncomeEntry;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Seeder;

/**
 * Seeder: VerificationSeeder
 *
 * Min 50 verification records terhubung ke income_entries dan expenses.
 *
 * FLOW yang disimulasikan:
 * 1. Ambil income/expense yang statusnya 'pending' atau 'verified'
 * 2. Buat verification record untuk masing-masing
 * 3. Update verification_id di income/expense yang bersangkutan
 *
 * ai_extracted_data mensimulasikan output Gemini Vision API.
 *
 * PENTING:
 * Seeder ini harus jalan SETELAH IncomeEntrySeeder dan ExpenseSeeder
 * karena perlu reference_id yang valid.
 */
class VerificationSeeder extends Seeder
{
    public function run(): void
    {
        $count = 0;

        // ── Verifikasi Income Entries ──────────────────────────────
        $incomes = IncomeEntry::whereIn('verified_status', ['verified', 'pending', 'flagged'])
                              ->get();

        foreach ($incomes as $income) {
            $status     = $income->verified_status;
            $confidence = $this->getConfidence($status);

            $verification = Verification::create([
                'user_id'           => $income->user_id,
                'reference_type'    => 'income',
                'reference_id'      => $income->id,
                'screenshot_path'   => 'verifications/income_' . $income->id . '_' . uniqid() . '.jpg',
                'ai_extracted_data' => $this->buildAiData($income->amount, $income->date, $status),
                'ai_confidence'     => $confidence,
                'status'            => $status,
                'flag_reason'       => $status === 'flagged'
                    ? $this->randomFlagReason()
                    : null,
            ]);

            // Link back ke income entry
            $income->update(['verification_id' => $verification->id]);
            $count++;
        }

        // ── Verifikasi Expenses ────────────────────────────────────
        $expenses = Expense::whereIn('verified_status', ['verified', 'pending', 'flagged'])
                           ->get();

        foreach ($expenses as $expense) {
            $status     = $expense->verified_status;
            $confidence = $this->getConfidence($status);

            $verification = Verification::create([
                'user_id'           => $expense->user_id,
                'reference_type'    => 'expense',
                'reference_id'      => $expense->id,
                'screenshot_path'   => 'verifications/expense_' . $expense->id . '_' . uniqid() . '.jpg',
                'ai_extracted_data' => $this->buildAiData($expense->amount, $expense->date, $status),
                'ai_confidence'     => $confidence,
                'status'            => $status,
                'flag_reason'       => $status === 'flagged'
                    ? $this->randomFlagReason()
                    : null,
            ]);

            // Link back ke expense
            $expense->update(['verification_id' => $verification->id]);
            $count++;
        }

        $this->command->info("✅ VerificationSeeder: {$count} verification records created and linked.");
    }

    /**
     * Build simulasi output Gemini Vision API.
     * Delta kecil ditambahkan untuk simulasi perbedaan OCR vs input user.
     */
    private function buildAiData(string|float $amount, $date, string $status): ?array
    {
        if ($status === 'pending') {
            return null; // Pending = belum diproses AI
        }

        $amount = (float) $amount;

        // Untuk status 'verified': delta sangat kecil (< 5%)
        // Untuk 'flagged': delta lebih besar (> 5%)
        $deltaFactor = $status === 'verified'
            ? (1 + (rand(-3, 3) / 100))   // -3% sampai +3%
            : (1 + (rand(6, 15) / 100));   // +6% sampai +15% (melebihi tolerance)

        $extractedAmount = round($amount * $deltaFactor, 0);

        $banks = ['BCA', 'BRI', 'Mandiri', 'BNI', 'BSI', 'DANA', 'GoPay', 'OVO', 'ShopeePay'];

        return [
            'amount'            => $extractedAmount,
            'date'              => is_string($date) ? $date : $date->format('Y-m-d'),
            'source'            => $banks[array_rand($banks)],
            'raw_text'          => 'Bukti Transfer - Rp ' . number_format($extractedAmount, 0, ',', '.'),
            'extraction_method' => rand(0, 9) < 8 ? 'gemini' : 'tesseract',
        ];
    }

    private function getConfidence(string $status): ?float
    {
        return match ($status) {
            'verified' => round(rand(87, 99) / 100, 2),
            'flagged'  => round(rand(40, 74) / 100, 2),
            'pending'  => null,
            default    => round(rand(60, 85) / 100, 2),
        };
    }

    private function randomFlagReason(): string
    {
        $reasons = [
            'delta_exceeded: amount difference > 5%',
            'low_confidence: AI confidence below 0.85 threshold',
            'date_mismatch: screenshot date differs from input date',
            'unreadable: screenshot resolution too low for OCR',
            'partial_match: only partial amount visible in screenshot',
        ];

        return $reasons[array_rand($reasons)];
    }
}
