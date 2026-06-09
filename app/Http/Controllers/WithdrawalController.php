<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawalFormRequest;
use App\Models\AppreciationLog;
use App\Models\InvestmentEntry;
use App\Models\Verification;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function create(int $investmentId): View
    {
        $investment = InvestmentEntry::forUser()->findOrFail($investmentId);

        // Cek apakah sudah ada withdrawal pending untuk investment ini
        $pendingWithdrawal = WithdrawalRequest::where('investment_entry_id', $investmentId)
            ->where('status', 'pending')
            ->first();

        return view('investments.withdrawal.create', compact(
            'investment',
            'pendingWithdrawal',
        ));
    }

    public function store(WithdrawalFormRequest $request, int $investmentId): RedirectResponse
    {
        $investment = InvestmentEntry::forUser()->findOrFail($investmentId);
        $validated  = $request->validated();

        // Validasi amount tidak melebihi current_value
        if ($validated['amount_requested'] > $investment->current_value) {
            return redirect()
                ->back()
                ->withErrors(['amount_requested' => 'Jumlah pencairan tidak boleh melebihi nilai investasi saat ini (Rp ' . number_format($investment->current_value, 0, ',', '.') . ').'])
                ->withInput();
        }

        // Cek tidak ada pending withdrawal untuk investment ini
        $existingPending = WithdrawalRequest::where('investment_entry_id', $investmentId)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return redirect()
                ->back()
                ->with('error', 'Sudah ada permintaan pencairan yang sedang diproses untuk investasi ini. Tunggu hingga selesai sebelum mengajukan permintaan baru.')
                ->withInput();
        }

        $withdrawal = WithdrawalRequest::create([
            'user_id'             => auth()->id(),
            'investment_entry_id' => $investmentId,
            'amount_requested'    => $validated['amount_requested'],
            'amount_received'     => null, // Diisi saat complete()
            'verification_id'     => null, // Diisi setelah upload screenshot
            'status'              => 'pending',
            'note'                => $validated['note'] ?? null,
        ]);

        // Redirect ke halaman upload screenshot verifikasi pencairan
        // VerificationController@createForWithdrawal akan handle upload
        return redirect()
            ->route('verifications.create', ['type' => 'withdrawal','id' => $withdrawal->ids])
            ->with('info', 'Permintaan pencairan dibuat. Silakan upload screenshot bukti pencairan untuk melanjutkan.');
    }

    public function complete(int $id): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::where('user_id', auth()->id())
            ->with(['investmentEntry', 'verification'])
            ->findOrFail($id);

        // Validasi pre-condition
        if ($withdrawal->status !== 'pending') {
            return redirect()
                ->route('investments.show', $withdrawal->investment_entry_id)
                ->with('error', 'Permintaan pencairan ini tidak dalam status pending.');
        }

        // Verifikasi screenshot harus sudah verified
        if (!$withdrawal->verification || $withdrawal->verification->status !== 'verified') {
            return redirect()
                ->route('investments.show', $withdrawal->investment_entry_id)
                ->with('error', 'Screenshot bukti pencairan belum diverifikasi. Proses verifikasi terlebih dahulu.');
        }

        $amountReceived = $withdrawal->verification->extracted_amount
            ?? $withdrawal->amount_requested;

        // ── ATOMIC TRANSACTION ──────────────────────────────────────────────
        // Dua operasi ini HARUS berhasil bersamaan atau keduanya rollback.
        // Ini adalah DB Transaction wajib dalam syarat TA (proses transaksi).
        try {
            DB::transaction(function () use ($withdrawal, $amountReceived) {
                // Update withdrawal record
                $withdrawal->update([
                    'status'          => 'completed',
                    'amount_received' => $amountReceived,
                ]);

                // Kurangi investment current_value
                // decrement() adalah atomic operation di MySQL — aman untuk concurrent access
                $withdrawal->investmentEntry->decrement('current_value', $amountReceived);

                // Recalculate return_pct setelah current_value berubah
                $investment   = $withdrawal->investmentEntry->fresh();
                $newReturnPct = $investment->initial_amount > 0
                    ? (($investment->current_value - $investment->initial_amount) / $investment->initial_amount) * 100
                    : 0;

                $investment->update(['return_pct' => round($newReturnPct, 4)]);
            });

            // Log withdrawal ke appreciation_logs (di luar transaction — non-critical)
            AppreciationLog::create([
                'user_id'       => auth()->id(),
                'type'          => 'daily_warning',
                'message'       => 'Pencairan investasi sebesar Rp ' . number_format($amountReceived, 0, ',', '.') . ' berhasil. Dana telah masuk ke liquid balance.',
                'trigger_value' => $amountReceived,
                'streak_count'  => auth()->user()->current_streak ?? 0,
                'badge_earned'  => null,
            ]);

            return redirect()
                ->route('investments.show', $withdrawal->investment_entry_id)
                ->with('success', 'Pencairan berhasil. Dana Rp ' . number_format($amountReceived, 0, ',', '.') . ' telah masuk ke liquid balance.');

        } catch (\Exception $e) {
            return redirect()
                ->route('investments.show', $withdrawal->investment_entry_id)
                ->with('error', 'Terjadi kesalahan saat memproses pencairan. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Reject withdrawal (admin action atau user cancel).
     * Status → 'rejected', investment tidak berubah.
     */
    public function reject(int $id): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->findOrFail($id);

        $withdrawal->update(['status' => 'rejected']);

        return redirect()
            ->route('investments.show', $withdrawal->investment_entry_id)
            ->with('info', 'Permintaan pencairan dibatalkan.');
    }
}
