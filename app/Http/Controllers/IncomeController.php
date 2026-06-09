<?php

namespace App\Http\Controllers;

use App\Models\IncomeEntry;
use App\Models\IncomeSource;
use App\Http\Requests\IncomeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = IncomeEntry::with(['source'])
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter: income source
        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }

        // Filter: verification status
        if ($request->filled('status')) {
            $query->where('verified_status', $request->status);
        }

        // Filter: month
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $incomes = $query->paginate(15)->withQueryString();
        $sources = IncomeSource::orderBy('name')->get();

        // ── Verified income summary for current month ─────────────────────────
        // This is the only figure used in balance calculations.
        $currentMonth = $request->get('month', now()->format('Y-m'));
        [$yr, $mo]    = explode('-', $currentMonth);

        $verifiedMonthlyTotal = IncomeEntry::where('user_id', Auth::id())
            ->where('verified_status', 'verified')
            ->whereYear('date', $yr)
            ->whereMonth('date', $mo)
            ->sum('amount');

        return view('income.index', compact(
            'incomes',
            'sources',
            'currentMonth',
            'verifiedMonthlyTotal'
        ));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create()
    {
        $sources = IncomeSource::orderBy('name')->get();
        return view('income.create', compact('sources'));
    }

    public function store(IncomeRequest $request)
    {
        IncomeEntry::create([
            'user_id'         => Auth::id(),
            'source_id'       => $request->source_id,
            'amount'          => $request->amount,
            'date'            => $request->date,
            'note'            => $request->note,
            'verified_status' => 'draft',
        ]);

        return redirect()->route('income.index')
            ->with('success', 'Pendapatan berhasil ditambahkan. Status: Draft — upload bukti untuk verifikasi.');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $income = IncomeEntry::with(['source', 'verification'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('income.show', compact('income'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(int $id)
    {
        $income  = IncomeEntry::where('user_id', Auth::id())->findOrFail($id);
        $sources = IncomeSource::orderBy('name')->get();

        return view('income.edit', compact('income', 'sources'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    /**
     * Same verified → draft reset logic as ExpenseController.
     * Data integrity is non-negotiable for the running balance.
     */
    public function update(IncomeRequest $request, int $id)
    {
        $income = IncomeEntry::where('user_id', Auth::id())->findOrFail($id);

        $wasVerified = $income->verified_status === 'verified';

        $income->update([
            'source_id'       => $request->source_id,
            'amount'          => $request->amount,
            'date'            => $request->date,
            'note'            => $request->note,
            'verified_status' => $wasVerified ? 'draft' : $income->verified_status,
        ]);

        if ($wasVerified) {
            return redirect()->route('income.show', $income->id)
                ->with('warning', '⚠️ Data berubah pada pendapatan yang sudah terverifikasi. Status direset ke Draft — upload ulang bukti verifikasi.');
        }

        return redirect()->route('income.show', $income->id)
            ->with('success', 'Pendapatan berhasil diperbarui.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    /**
     * Verified income entries cannot be deleted.
     * Deleting verified income would corrupt the running balance downward
     * without any audit trace — equally dangerous as deleting verified expenses.
     */
    public function destroy(int $id)
    {
        $income = IncomeEntry::where('user_id', Auth::id())->findOrFail($id);

        if ($income->verified_status === 'verified') {
            return back()
                ->with('error', 'Pendapatan terverifikasi tidak dapat dihapus.');
        }

        $income->delete();

        return redirect()->route('income.index')
            ->with('success', 'Pendapatan berhasil dihapus.');
    }
}
