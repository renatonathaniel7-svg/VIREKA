<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentRequest;
use App\Models\InvestmentEntry;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function index(): View
    {
        $investments = InvestmentEntry::forUser()
            ->orderByDesc('created_at')
            ->get();

        // Summary calculations
        $totalInitial = $investments->sum('initial_amount');
        $totalCurrent = $investments->sum('current_value');
        $totalReturn  = $totalCurrent - $totalInitial;
        $totalReturnPct = $totalInitial > 0
            ? round(($totalReturn / $totalInitial) * 100, 2)
            : 0;

        // Withdrawal yang masih pending
        $pendingWithdrawals = WithdrawalRequest::forUser()
            ->where('status', 'pending')
            ->with('investmentEntry')
            ->get();

        $totalPending = $pendingWithdrawals->sum('amount_requested');

        return view('investments.index', compact(
            'investments',
            'totalInitial',
            'totalCurrent',
            'totalReturn',
            'totalReturnPct',
            'pendingWithdrawals',
            'totalPending',
        ));
    }

    /**
     * Form tambah investment baru.
     */
    public function create(): View
    {
        return view('investments.create');
    }

    /**
     * Simpan investment baru.
     * current_value diset sama dengan initial_amount saat pertama dibuat.
     * return_pct = 0 (belum ada return saat baru dibuat).
     */
    public function store(InvestmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        InvestmentEntry::create([
            'user_id'         => auth()->id(),
            'allocation_type' => $validated['allocation_type'],
            'instrument'      => $validated['instrument'] ?? null,
            'initial_amount'  => $validated['initial_amount'],
            'current_value'   => $validated['initial_amount'], // Default = initial saat create
            'return_pct'      => 0.00,
            'note'            => $validated['note'] ?? null,
            'invested_at'     => now(),
        ]);

        return redirect()
            ->route('investments.index')
            ->with('success', 'Investasi berhasil ditambahkan ke portfolio.');
    }

    /**
     * Detail satu investment + withdrawal history.
     */
    public function show(int $id): View
    {
        $investment = InvestmentEntry::forUser()->findOrFail($id);

        $withdrawals = WithdrawalRequest::where('investment_entry_id', $id)
            ->orderByDesc('created_at')
            ->get();

        $hasPendingWithdrawal = $withdrawals->where('status', 'pending')->isNotEmpty();

        $returnAmount  = $investment->current_value - $investment->initial_amount;
        $progressPct   = $investment->initial_amount > 0
            ? min(200, ($investment->current_value / $investment->initial_amount) * 100)
            : 0;

        return view('investments.show', compact(
            'investment',
            'withdrawals',
            'hasPendingWithdrawal',
            'returnAmount',
            'progressPct',
        ));
    }

    public function edit(int $id): View
    {
        $investment = InvestmentEntry::forUser()->findOrFail($id);
        return view('investments.edit', compact('investment'));
    }

    public function update(InvestmentRequest $request, int $id): RedirectResponse
    {
        $investment = InvestmentEntry::forUser()->findOrFail($id);
        $validated  = $request->validated();

        $currentValue = isset($validated['current_value'])
            ? (float) $validated['current_value']
            : $investment->current_value;

        // Auto-recalculate return_pct
        $returnPct = $investment->initial_amount > 0
            ? (($currentValue - $investment->initial_amount) / $investment->initial_amount) * 100
            : 0;

        $investment->update([
            'current_value' => $currentValue,
            'return_pct'    => round($returnPct, 4),
            'instrument'    => $validated['instrument'] ?? $investment->instrument,
            'note'          => $validated['note'] ?? $investment->note,
            // initial_amount TIDAK di-update
        ]);

        return redirect()
            ->route('investments.show', $investment->id)
            ->with('success', 'Nilai investasi berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $investment = InvestmentEntry::forUser()->findOrFail($id);

        $hasWithdrawals = WithdrawalRequest::where('investment_entry_id', $id)->exists();

        if ($hasWithdrawals) {
            return redirect()
                ->route('investments.show', $id)
                ->with('error', 'Investasi tidak dapat dihapus karena memiliki riwayat permintaan pencairan. Hubungi admin jika diperlukan.');
        }

        $investment->delete();

        return redirect()
            ->route('investments.index')
            ->with('success', 'Investasi berhasil dihapus dari portfolio.');
    }
}
