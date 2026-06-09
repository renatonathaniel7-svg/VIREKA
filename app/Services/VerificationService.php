<?php

namespace App\Services;

use App\Models\Verification;
use App\Models\AppreciationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class VerificationService
{
    /**
     * Tolerance discrepancy amount dalam persen (5% sesuai spesifikasi).
     */
    private int $tolerancePct;

    public function __construct(private ComparisonEngine $comparisonEngine)
    {
        $this->tolerancePct = (int) config('services.gemini.tolerance', 5);
    }

    public function process(Verification $verification): void
    {
        // Resolve absolute path dari screenshot yang sudah tersimpan di storage
        $imagePath = Storage::disk('local')->path(
            str_replace('storage/', 'public/', $verification->screenshot_path)
        );

        // Jika path mengandung 'public/verifications', gunakan path storage langsung
        $storagePath = storage_path('app/public/verifications/' . basename($verification->screenshot_path));
        if (file_exists($storagePath)) {
            $imagePath = $storagePath;
        }

        $extracted = null;
        $method    = null;

        // ── Layer 1: Gemini 1.5 Flash ──────────────────────────────────────
        try {
            $geminiResult = $this->callGemini($imagePath);

            if ($geminiResult !== null && $geminiResult['confidence'] >= 0.7) {
                $extracted = $geminiResult;
                $method    = 'gemini';
            } elseif ($geminiResult !== null && $geminiResult['confidence'] < 0.7) {
                // Gemini berhasil tapi confidence rendah → fallback Tesseract
                Log::info("FinTrack Verification [{$verification->id}]: Gemini confidence {$geminiResult['confidence']} < 0.7, falling back to Tesseract.");
                $extracted = null;
            }
        } catch (\Exception $e) {
            // Gemini gagal (exception, quota habis, network error) → fallback
            Log::warning("FinTrack Verification [{$verification->id}]: Gemini failed — {$e->getMessage()}");
        }

        // ── Layer 2: Tesseract OCR (jika Gemini tidak memadai) ────────────
        if ($extracted === null) {
            try {
                $tesseractResult = $this->callTesseract($imagePath);

                if ($tesseractResult !== null) {
                    $extracted = $tesseractResult;
                    $method    = 'tesseract';
                }
            } catch (\Exception $e) {
                Log::warning("FinTrack Verification [{$verification->id}]: Tesseract failed — {$e->getMessage()}");
            }
        }

        // ── Layer 3: Manual review flag (jika semua AI gagal) ─────────────
        if ($extracted === null) {
            $verification->update([
                'status'           => 'pending',
                'flag_reason'      => 'Tidak dapat membaca screenshot secara otomatis. Menunggu review manual.',
                'ai_confidence'    => null,
                'ai_extracted_data'=> null,
            ]);

            $this->updateTransactionStatus($verification, 'pending');
            return;
        }

        // ── Comparison Engine ─────────────────────────────────────────────
        $userInput = $this->getUserInputFromVerification($verification);

        $result = $this->comparisonEngine->compare($extracted, $userInput);

        // ── Persist hasil ke database ─────────────────────────────────────
        $verification->update([
            'status'            => $result->status,
            'ai_extracted_data' => json_encode($extracted),
            'ai_confidence'     => $extracted['confidence'] ?? null,
            'flag_reason'       => $result->flagReason,
        ]);

        $this->updateTransactionStatus($verification, $result->status);

        // ── Simpan ke appreciation_logs jika verified ─────────────────────
        if ($result->status === 'verified') {
            $this->logAppreciation($verification);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: GEMINI 1.5 FLASH INTEGRATION
    // ─────────────────────────────────────────────────────────────────────────

    public function callGemini(string $imagePath): ?array
    {
        if (! file_exists($imagePath)) {
            Log::error("FinTrack Gemini: File tidak ditemukan — {$imagePath}");
            return null;
        }

        $apiKey   = config('services.gemini.key');
        $model    = config('services.gemini.model', 'gemini-1.5-flash');
        $endpoint = config('services.gemini.url') . "{$model}:generateContent?key={$apiKey}";

        // Deteksi MIME type dari ekstensi file
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $mimeType  = match ($extension) {
            'png'           => 'image/png',
            'jpg', 'jpeg'   => 'image/jpeg',
            default         => 'image/jpeg',
        };

        // Encode gambar ke base64
        $imageData = base64_encode(file_get_contents($imagePath));

        // Prompt yang dikirim ke Gemini — SESUAI SPESIFIKASI
$prompt = <<<PROMPT
Return VALID JSON only.

Schema:
{
  "amount": 0,
  "date": "YYYY-MM-DD",
  "source": "",
  "transaction_type": "credit",
  "confidence": 0.0
}

Semua field wajib ada.
Jangan gunakan markdown.
Jangan gunakan backticks.
Jangan menambahkan penjelasan apapun.
PROMPT;

        $payload = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    [
                        'inline_data' => [
                            'mime_type' => $mimeType,
                            'data'      => $imageData,
                        ],
                    ],
                ],
            ]],
        ];

        $response = Http::retry(3, 2000)
    ->timeout(30)
    ->withHeaders([
        'Content-Type' => 'application/json'
    ])
    ->post($endpoint, $payload);

        if (! $response->successful()) {
            Log::error("FinTrack Gemini API Error: HTTP {$response->status()} — {$response->body()}");
            return null;
        }

        $responseBody = $response->json();

        // Parse response dari Gemini
        $responseText = $responseBody['candidates'][0]['content']['parts'][0]['text'] ?? null;

        Log::info('GEMINI FULL RESPONSE', [
    'response' => $responseBody
]);
        if (empty($responseText)) {
            Log::warning("FinTrack Gemini: Response text kosong.");
            return null;
        }

        // Bersihkan JSON dari kemungkinan backtick atau karakter lain
        $cleanText = preg_replace('/```(?:json)?\s*/', '', $responseText);
        $cleanText = trim($cleanText);

        Log::info('GEMINI RAW RESPONSE', [
    'response' => $cleanText
    ]);
        $extracted = json_decode($cleanText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("FinTrack Gemini: Gagal decode JSON — '{$cleanText}'");
            return null;
        }

        // Pastikan field wajib ada
        if (! isset($extracted['amount'], $extracted['date'], $extracted['confidence'])) {
            Log::warning("FinTrack Gemini: Field wajib (amount/date/confidence) tidak ada dalam response.");
            return null;
        }

        return [
            'amount'           => (int) $extracted['amount'],
            'date'             => $extracted['date'] ?? null,
            'source'           => $extracted['source'] ?? 'Tidak terdeteksi',
            'transaction_type' => $extracted['transaction_type'] ?? 'unknown',
            'confidence'       => (float) $extracted['confidence'],
        ];
    }


    public function callTesseract(string $imagePath): ?array
    {
        if (! $this->tesseractAvailable()) {
            Log::info("FinTrack Tesseract: Binary tidak ditemukan di sistem.");
            return null;
        }

        if (! file_exists($imagePath)) {
            Log::error("FinTrack Tesseract: File tidak ditemukan — {$imagePath}");
            return null;
        }

        $ocrText = $this->runTesseract($imagePath);

        if (empty(trim($ocrText))) {
            Log::warning("FinTrack Tesseract: Output OCR kosong untuk {$imagePath}");
            return null;
        }

        $amount = $this->parseAmount($ocrText);
        $date   = $this->parseDate($ocrText);

        // Jika amount tidak berhasil di-parse, Tesseract dianggap gagal
        if ($amount === null) {
            Log::info("FinTrack Tesseract: Tidak bisa parse amount dari teks OCR.");
            return null;
        }

        return [
            'amount'           => (int) $amount,
            'date'             => $date,
            'source'           => 'OCR Lokal (Tesseract)',
            'transaction_type' => 'unknown',
            'confidence'       => 0.6, // Default confidence Tesseract
        ];
    }

    /**
     * Cek apakah binary Tesseract tersedia di sistem.
     */
    private function tesseractAvailable(): bool
    {
        $output = shell_exec('which tesseract 2>/dev/null');
        return ! empty(trim($output ?? ''));
    }

    /**
     * Jalankan binary Tesseract dan kembalikan teks hasil OCR.
     * Menggunakan bahasa Indonesia (-l ind) untuk akurasi lebih baik.
     */
    private function runTesseract(string $imagePath): string
    {
        $outputBase = storage_path('app/temp/ocr_' . uniqid());

        // Pastikan direktori temp ada
        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Escape path untuk keamanan shell
        $escapedInput  = escapeshellarg($imagePath);
        $escapedOutput = escapeshellarg($outputBase);

        shell_exec("tesseract {$escapedInput} {$escapedOutput} -l ind 2>/dev/null");

        $textFile = $outputBase . '.txt';
        $text     = '';

        if (file_exists($textFile)) {
            $text = file_get_contents($textFile);
            @unlink($textFile);
        }

        return $text ?: '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: PARSING UTILITIES
    // ─────────────────────────────────────────────────────────────────────────


    public function parseAmount(string $text): ?float
    {
        // Normalisasi teks: lowercase dan strip whitespace berlebih
        $normalized = strtolower(trim($text));

        // Pattern 1: Format Indonesia dengan "Rp" diikuti angka dengan titik sebagai separator ribuan
        // Contoh: "Rp 150.000", "Rp1.500.000", "rp 2.350.000,50"
        if (preg_match('/rp\.?\s*([\d.,]+)/i', $normalized, $matches)) {
            $raw = $matches[1];
            // Indonesia: titik = separator ribuan, koma = desimal
            // Hapus titik (separator ribuan) lalu ganti koma dengan titik (desimal)
            $cleaned = str_replace('.', '', $raw);
            $cleaned = str_replace(',', '.', $cleaned);
            $value   = (float) $cleaned;
            if ($value > 0) {
                return $value;
            }
        }

        // Pattern 2: Angka murni dengan titik sebagai separator ribuan (tanpa Rp)
        // Contoh: "150.000", "1.500.000"
        if (preg_match('/\b(\d{1,3}(?:\.\d{3})+)\b/', $text, $matches)) {
            $cleaned = str_replace('.', '', $matches[1]);
            $value   = (float) $cleaned;
            if ($value > 0) {
                return $value;
            }
        }

        // Pattern 3: Angka bulat tanpa separator
        // Contoh: "150000", "1500000"
        if (preg_match('/\b(\d{5,12})\b/', $text, $matches)) {
            $value = (float) $matches[1];
            if ($value > 0) {
                return $value;
            }
        }

        return null;
    }


    public function parseDate(string $text): ?string
    {
        // Mapping nama bulan Indonesia ke angka
        $bulanIndonesia = [
            'januari' => '01', 'februari' => '02', 'maret' => '03',
            'april'   => '04', 'mei'      => '05', 'juni'  => '06',
            'juli'    => '07', 'agustus'  => '08', 'september' => '09',
            'oktober' => '10', 'november' => '11', 'desember'  => '12',
            // Singkatan umum
            'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04',
            'jun' => '06', 'jul' => '07', 'ags' => '08', 'agt' => '08',
            'sep' => '09', 'okt' => '10', 'nov' => '11', 'des' => '12',
        ];

        // Pattern 1: ISO format YYYY-MM-DD
        if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $text, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]}";
        }

        // Pattern 2: DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $text, $m)) {
            $day   = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            return "{$m[3]}-{$month}-{$day}";
        }

        // Pattern 3: DD Bulan YYYY (format Indonesia)
        // Contoh: "10 Mei 2025", "5 Januari 2025"
        $bulanPattern = implode('|', array_keys($bulanIndonesia));
        if (preg_match('/\b(\d{1,2})\s+(' . $bulanPattern . ')\s+(\d{4})\b/i', $text, $m)) {
            $day   = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = $bulanIndonesia[strtolower($m[2])];
            return "{$m[3]}-{$month}-{$day}";
        }

        // Pattern 4: DD/MM/YY (dua digit tahun)
        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})\b/', $text, $m)) {
            $day   = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year  = '20' . $m[3];
            return "{$year}-{$month}-{$day}";
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Ambil data input user (amount & date) dari record transaksi terkait.
     * Mendukung polymorphic reference: 'expense' atau 'income'.
     */
    private function getUserInputFromVerification(Verification $verification): array
    {
        $model = match ($verification->reference_type) {
            'expense' => \App\Models\Expense::find($verification->reference_id),
            'income'  => \App\Models\IncomeEntry::find($verification->reference_id),
            default   => null,
        };

        if (! $model) {
            return ['amount' => 0, 'date' => null];
        }

        return [
            'amount' => (int) $model->amount,
            'date'   => $model->date instanceof \Carbon\Carbon
                ? $model->date->format('Y-m-d')
                : (string) $model->date,
        ];
    }

    /**
     * Update verified_status di tabel expenses atau income_entries
     * sesuai hasil verifikasi.
     */
    private function updateTransactionStatus(Verification $verification, string $status): void
    {
        match ($verification->reference_type) {
            'expense' => \App\Models\Expense::where('id', $verification->reference_id)
                ->update(['verified_status' => $status]),
            'income' => \App\Models\IncomeEntry::where('id', $verification->reference_id)
                ->update(['verified_status' => $status]),
            default => null,
        };
    }

    /**
     * Simpan log apresiasi ke tabel appreciation_logs setelah transaksi terverifikasi.
     * Hanya dipanggil ketika status = 'verified'.
     */
    private function logAppreciation(Verification $verification): void
    {
        AppreciationLog::create([
            'user_id'       => $verification->user_id,
            'type'          => 'daily_appreciation',
            'trigger_value' => $verification->ai_confidence ?? 0,
            'streak_count'  => 0,
            'badge_earned'  => null,
            'message'       => "Transaksi berhasil diverifikasi oleh sistem AI dengan confidence " .
                               number_format(($verification->ai_confidence ?? 0) * 100, 0) . "%.",
        ]);
    }
}
