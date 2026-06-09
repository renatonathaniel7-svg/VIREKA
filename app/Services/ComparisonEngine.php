<?php

namespace App\Services;

/**
 * ComparisonResult
 *
 * Value object yang merepresentasikan hasil perbandingan
 * antara data yang diekstrak AI dengan input user.
 */
class ComparisonResult
{
    public function __construct(
        public readonly string  $status,
        public readonly float   $deltaPct,
        public readonly bool    $dateMatch,
        public readonly ?string $flagReason = null,
    ) {}
}

/**
 * ComparisonEngine
 *
 * Menghitung selisih nominal dan kecocokan tanggal antara
 * data AI-extracted dengan data yang diinput user.
 *
 * FORMULA (sesuai spesifikasi FinTrack):
 *   delta_amount = |extracted.amount - user.amount|
 *   delta_pct    = delta_amount / user.amount * 100
 *
 * STATUS:
 *   delta_pct <= 5% AND date_match  → 'verified'
 *   delta_pct > 5%  OR !date_match  → 'flagged'
 *
 * Toleransi 5% mengakomodasi: pembulatan bank, potongan pajak/biaya admin.
 */
class ComparisonEngine
{
    /**
     * Tolerance dalam persen, diambil dari config.
     */
    private float $tolerancePct;

    public function __construct()
    {
        $this->tolerancePct = (float) config('services.gemini.tolerance', 5);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Bandingkan data yang diekstrak AI dengan input user.
     *
     * @param  array $extracted Data dari Gemini/Tesseract: ['amount', 'date', ...]
     * @param  array $userInput Data dari transaksi: ['amount', 'date']
     * @return ComparisonResult
     */
    public function compare(array $extracted, array $userInput): ComparisonResult
    {
        $extractedAmount = (float) ($extracted['amount'] ?? 0);
        $userAmount      = (float) ($userInput['amount']  ?? 0);
        $extractedDate   = $extracted['date']  ?? null;
        $userDate        = $userInput['date']   ?? null;

        // Hitung delta percentage
        $deltaPct  = $this->calculateDeltaPct($extractedAmount, $userAmount);
        $dateMatch = $this->checkDateMatch($extractedDate, $userDate);

        $status     = $this->getStatus($deltaPct, $dateMatch);
        $flagReason = $status === 'flagged'
            ? $this->getFlagReason($deltaPct, $dateMatch, $extractedAmount, $userAmount, $extractedDate, $userDate)
            : null;

        return new ComparisonResult(
            status:     $status,
            deltaPct:   $deltaPct,
            dateMatch:  $dateMatch,
            flagReason: $flagReason,
        );
    }

    /**
     * Tentukan status berdasarkan delta percentage dan kecocokan tanggal.
     *
     * @param  float $deltaPct  Selisih nominal dalam persen
     * @param  bool  $dateMatch Apakah tanggal cocok
     * @return string 'verified' atau 'flagged'
     */
    public function getStatus(float $deltaPct, bool $dateMatch): string
    {
        if ($deltaPct <= $this->tolerancePct && $dateMatch) {
            return 'verified';
        }

        return 'flagged';
    }

    /**
     * Generate pesan flag_reason yang human-readable dalam Bahasa Indonesia.
     * Memberikan detail spesifik tentang penyebab discrepancy.
     *
     * @param  float       $deltaPct        Selisih dalam persen
     * @param  bool        $dateMatch       Apakah tanggal cocok
     * @param  float       $extractedAmount Nominal dari AI
     * @param  float       $userAmount      Nominal input user
     * @param  string|null $extractedDate   Tanggal dari AI
     * @param  string|null $userDate        Tanggal input user
     * @return string
     */
    public function getFlagReason(
        float   $deltaPct,
        bool    $dateMatch,
        float   $extractedAmount,
        float   $userAmount,
        ?string $extractedDate,
        ?string $userDate
    ): string {
        $reasons = [];

        // Cek discrepancy nominal
        if ($deltaPct > $this->tolerancePct) {
            $formattedUser      = 'Rp ' . number_format($userAmount, 0, ',', '.');
            $formattedExtracted = 'Rp ' . number_format($extractedAmount, 0, ',', '.');
            $deltaPctFormatted  = number_format($deltaPct, 1);

            $reasons[] = "Nominal berbeda {$deltaPctFormatted}% (input: {$formattedUser}, terdeteksi: {$formattedExtracted})";
        }

        // Cek discrepancy tanggal
        if (! $dateMatch) {
            $userDateDisplay      = $userDate      ?? 'tidak tersedia';
            $extractedDateDisplay = $extractedDate ?? 'tidak terdeteksi';

            // Format tanggal ke format yang lebih mudah dibaca
            if ($userDate) {
                try {
                    $userDateDisplay = \Carbon\Carbon::parse($userDate)->isoFormat('D MMMM YYYY');
                } catch (\Exception $e) {
                    // Gunakan raw string jika parse gagal
                }
            }
            if ($extractedDate) {
                try {
                    $extractedDateDisplay = \Carbon\Carbon::parse($extractedDate)->isoFormat('D MMMM YYYY');
                } catch (\Exception $e) {
                    // Gunakan raw string jika parse gagal
                }
            }

            $reasons[] = "Tanggal tidak cocok (input: {$userDateDisplay}, terdeteksi: {$extractedDateDisplay})";
        }

        return implode('; ', $reasons);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hitung persentase selisih antara dua nominal.
     * Menggunakan user.amount sebagai denominator (baseline).
     *
     * Edge case: jika user.amount = 0, return 100% (flagged).
     */
    private function calculateDeltaPct(float $extractedAmount, float $userAmount): float
    {
        if ($userAmount == 0) {
            return 100.0;
        }

        return abs($extractedAmount - $userAmount) / $userAmount * 100;
    }

    /**
     * Bandingkan tanggal dari AI dengan tanggal input user.
     *
     * Jika salah satu null (tanggal tidak terdeteksi), dianggap tidak cocok.
     * Toleransi: exact match pada komponen year-month-day (timezone-insensitive).
     */
    private function checkDateMatch(?string $extractedDate, ?string $userDate): bool
    {
        if ($extractedDate === null || $userDate === null) {
            return false;
        }

        try {
            $d1 = \Carbon\Carbon::parse($extractedDate)->format('Y-m-d');
            $d2 = \Carbon\Carbon::parse($userDate)->format('Y-m-d');
            return $d1 === $d2;
        } catch (\Exception $e) {
            return false;
        }
    }
}
