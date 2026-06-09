<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Http\Requests\ExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ExpenseController extends Controller
{

    public function index(Request $request)
    {
        $query = Expense::with(['category'])
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter: category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter: verification status
        if ($request->filled('status')) {
            $query->where('verified_status', $request->status);
        }

        // Filter: month — uses whereYear + whereMonth because the column is DATE
        // NOT whereDate() — that's for matching a specific full date
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }

        $expenses   = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        // Default month display
        $currentMonth = $request->get('month', now()->format('Y-m'));

        return view('expenses.index', compact('expenses', 'categories', 'currentMonth'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────


    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(ExpenseRequest $request)
    {
        Expense::create([
            'user_id'         => Auth::id(),
            'category_id'     => $request->category_id,
            'amount'          => $request->amount,
            'description'     => $request->description,
            'date'            => $request->date,
            'verified_status' => 'draft',
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil ditambahkan. Status: Draft — upload bukti untuk verifikasi.');
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $expense = Expense::with(['category', 'verification'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('expenses.show', compact('expense'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function edit(int $id)
    {
        $expense    = Expense::where('user_id', Auth::id())->findOrFail($id);
        $categories = Category::orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(ExpenseRequest $request, int $id)
    {
        $expense = Expense::where('user_id', Auth::id())->findOrFail($id);

        $wasVerified = $expense->verified_status === 'verified';

        $expense->update([
            'category_id' => $request->category_id,
            'amount'      => $request->amount,
            'description' => $request->description,
            'date'        => $request->date,
            // Reset status if data changed on a verified entry
            'verified_status' => $wasVerified ? 'draft' : $expense->verified_status,
        ]);

        if ($wasVerified) {
            return redirect()->route('expenses.show', $expense->id)
                ->with('warning', '⚠️ Data berubah pada transaksi yang sudah terverifikasi. Status direset ke Draft — upload ulang bukti verifikasi.');
        }

        return redirect()->route('expenses.show', $expense->id)
            ->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        $expense = Expense::where('user_id', Auth::id())->findOrFail($id);

        if ($expense->verified_status === 'verified') {
            return back()
                ->with('error', 'Transaksi terverifikasi tidak dapat dihapus. Hubungi admin jika terjadi kesalahan data.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
