<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificationRequest;
use App\Models\Expense;
use App\Models\IncomeEntry;
use App\Models\Verification;
use App\Services\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function __construct(private VerificationService $verificationService)
    {
    }


    public function create(string $type, int $id): View|RedirectResponse
    {
        // Validasi type hanya boleh 'expense' atau 'income'
        if (! in_array($type, ['expense', 'income'])) {
            abort(404);
        }

        // Resolve model berdasarkan type
        $transaction = $this->resolveTransaction($type, $id);

        // Guard: pastikan record milik auth user
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Jika sudah verified, redirect ke show dengan informasi
        if ($transaction->verified_status === 'verified') {
            $verification = Verification::where('reference_type', $type)
                ->where('reference_id', $id)
                ->where('user_id', Auth::id())
                ->latest()
                ->first();

            return redirect()
                ->route('verifications.show', $verification?->id ?? 0)
                ->with('info', 'Transaksi ini sudah berhasil diverifikasi.');
        }

        return view('verifications.create', [
            'type'        => $type,
            'transaction' => $transaction,
        ]);
    }


    public function store(VerificationRequest $request, string $type, int $id): RedirectResponse
    {
        if (! in_array($type, ['expense', 'income'])) {
            abort(404);
        }

        $transaction = $this->resolveTransaction($type, $id);

        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $file = $request->file('screenshot');

        // ── Validasi MIME type dari konten file (bukan hanya ekstensi) ────
        $detectedMime = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];

        if (! in_array($detectedMime, $allowedMimes)) {
            return back()->withErrors([
                'screenshot' => 'Format file tidak valid. Hanya JPG, PNG, atau PDF yang diterima.',
            ]);
        }

        // ── Handle PDF: konversi ke JPEG jika ImageMagick tersedia ────────
        // Jika ImageMagick tidak ada, tolak PDF dan minta JPG/PNG
        if ($detectedMime === 'application/pdf') {
            if ($this->imageMagickAvailable()) {
                $file = $this->convertPdfToJpeg($file);
                if ($file === null) {
                    return back()->withErrors([
                        'screenshot' => 'Gagal memproses file PDF. Silakan upload dalam format JPG atau PNG.',
                    ]);
                }
            } else {
                return back()->withErrors([
                    'screenshot' => 'Upload PDF belum didukung di server ini. Silakan upload screenshot dalam format JPG atau PNG.',
                ]);
            }
        }

        // ── Simpan file ke storage/app/public/verifications/ ─────────────
        $filename = 'verif_' . Auth::id() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(
            'verifications',
                $filename,
                'public'
        );
        // ── Buat record verifications dengan status 'pending' ─────────────
        $verification = Verification::create([
            'user_id'        => Auth::id(),
            'reference_type' => $type,
            'reference_id'   => $id,
            'screenshot_path'=> 'storage/verifications/' . $filename,
            'status'         => 'pending',
            'ai_confidence'  => null,
            'flag_reason'    => null,
        ]);

        // ── Update expense/income → verified_status = 'pending' ──────────
        $transaction->update(['verified_status' => 'pending']);

        // ── Jalankan VerificationService@process (synchronous) ────────────
        try {
            $this->verificationService->process($verification);
            $verification->refresh();
        } catch (\Exception $e) {
            // Jika service crash, tetap lanjutkan — verifikasi sudah di-save sebagai 'pending'
            \Illuminate\Support\Facades\Log::error("VerificationService crashed: {$e->getMessage()}");
        }

        // ── Redirect ke halaman hasil ─────────────────────────────────────
        $flashMessage = match ($verification->status) {
            'verified' => 'success|Transaksi berhasil diverifikasi oleh sistem AI.',
            'flagged'  => 'warning|Terdapat perbedaan data. Silakan periksa detail verifikasi.',
            default    => 'info|Screenshot sedang diproses. Mohon tunggu review manual.',
        };

        [$flashType, $flashText] = explode('|', $flashMessage, 2);

        return redirect()
            ->route('verifications.show', $verification->id)
            ->with($flashType, $flashText);
    }

    public function show(int $id): View
    {
        $verification = Verification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Resolve data transaksi terkait
        $transaction = $this->resolveTransaction(
            $verification->reference_type,
            $verification->reference_id
        );

        // Decode JSON ai_extracted_data
        $aiData = null;
        if (! empty($verification->ai_extracted_data)) {
            $aiData = is_string($verification->ai_extracted_data)
                ? json_decode($verification->ai_extracted_data, true)
                : $verification->ai_extracted_data;
        }

        return view('verifications.show', [
            'verification' => $verification,
            'transaction'  => $transaction,
            'aiData'       => $aiData,
        ]);
    }

    public function confirm(int $id): RedirectResponse
{
    $verification = Verification::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    $verification->update([
        'status' => 'verified',
    ]);

    $transaction = $this->resolveTransaction(
        $verification->reference_type,
        $verification->reference_id
    );

    $transaction->update([
        'verified_status' => 'verified',
    ]);

    return redirect()
        ->route('verifications.show', $verification->id)
        ->with('success', 'Transaksi berhasil dikonfirmasi.');
}

    public function flag(int $id): RedirectResponse
{
    $verification = Verification::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    $verification->update([
        'status' => 'flagged',
        'flag_reason' => 'Ditandai bermasalah oleh pengguna.',
    ]);

    $transaction = $this->resolveTransaction(
        $verification->reference_type,
        $verification->reference_id
    );

    $transaction->update([
        'verified_status' => 'flagged',
    ]);

    return redirect()
        ->route('verifications.show', $verification->id)
        ->with('warning', 'Verifikasi ditandai bermasalah.');
}

    public function reupload(int $id): RedirectResponse
{
    $verification = Verification::where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    return redirect()->route(
        'verifications.create',
        [
            $verification->reference_type,
            $verification->reference_id
        ]
    );
}

    public function retry(int $id): RedirectResponse
    {
        $verification = Verification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Guard: hanya bisa retry jika status 'flagged'
        if ($verification->status !== 'flagged') {
            return back()->with('error', 'Verifikasi hanya bisa diulang jika statusnya flagged.');
        }

        // Reset ke 'pending'
        $verification->update([
            'status'            => 'pending',
            'flag_reason'       => null,
            'ai_extracted_data' => null,
            'ai_confidence'     => null,
        ]);

        // Update status transaksi ke pending
        $transaction = $this->resolveTransaction(
            $verification->reference_type,
            $verification->reference_id
        );
        $transaction->update(['verified_status' => 'pending']);

        // Jalankan ulang proses
        try {
            $this->verificationService->process($verification);
            $verification->refresh();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("VerificationService retry crashed: {$e->getMessage()}");
        }

        $flashMessage = match ($verification->status) {
            'verified' => 'success|Transaksi berhasil diverifikasi.',
            'flagged'  => 'warning|Masih terdapat perbedaan data setelah diulang.',
            default    => 'info|Verifikasi sedang diproses manual.',
        };

        [$flashType, $flashText] = explode('|', $flashMessage, 2);

        return redirect()
            ->route('verifications.show', $verification->id)
            ->with($flashType, $flashText);
    }


    public function markAsCash(string $type, int $id): RedirectResponse
    {
        if (! in_array($type, ['expense', 'income'])) {
            abort(404);
        }

        $transaction = $this->resolveTransaction($type, $id);

        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Buat/update verification record dengan status unverified
        $verification = Verification::updateOrCreate(
            [
                'user_id'        => Auth::id(),
                'reference_type' => $type,
                'reference_id'   => $id,
            ],
            [
                'screenshot_path'   => null,
                'status'            => 'unverified',
                'ai_extracted_data' => null,
                'ai_confidence'     => null,
                'flag_reason'       => 'Ditandai sebagai transaksi tunai oleh pengguna.',
            ]
        );

        // Update status transaksi
        $transaction->update(['verified_status' => 'unverified']);

        $transactionLabel = $type === 'expense' ? 'Pengeluaran' : 'Pendapatan';

        return redirect()
            ->route($type === 'expense' ? 'expenses.show' : 'income.show', $id)
            ->with('info', "{$transactionLabel} ditandai sebagai transaksi tunai dan masuk ke shadow balance.");
    }


    private function resolveTransaction(string $type, int $id): Expense|IncomeEntry
    {
        return match ($type) {
            'expense' => Expense::findOrFail($id),
            'income'  => IncomeEntry::findOrFail($id),
            default   => abort(404),
        };
    }

    /**
     * Cek apakah binary ImageMagick (convert) tersedia di sistem.
     */
    private function imageMagickAvailable(): bool
    {
        $output = shell_exec('which convert 2>/dev/null');
        return ! empty(trim($output ?? ''));
    }

    private function convertPdfToJpeg(\Illuminate\Http\UploadedFile $file): ?\Illuminate\Http\UploadedFile
    {
        $pdfPath  = $file->getPathname();
        $jpegPath = storage_path('app/temp/pdf_convert_' . uniqid() . '.jpg');

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Konversi hanya halaman pertama PDF ([0])
        $escapedPdf  = escapeshellarg($pdfPath . '[0]');
        $escapedJpeg = escapeshellarg($jpegPath);

        shell_exec("convert {$escapedPdf} -flatten -quality 90 {$escapedJpeg} 2>/dev/null");

        if (! file_exists($jpegPath) || filesize($jpegPath) === 0) {
            return null;
        }

        return new \Illuminate\Http\UploadedFile(
            $jpegPath,
            'converted.jpg',
            'image/jpeg',
            null,
            true // testMode: true agar tidak divalidasi ulang sebagai upload
        );
    }
}
