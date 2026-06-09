<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Models\FinancialGoal;
use App\Models\GoalContribution;
use Illuminate\Http\Request;

class FinancialGoalController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}
    public function index()
    {
        $goals = FinancialGoal::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('goals.index', compact('goals'));
    }

    public function create()
{
    return view('goals.create');
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'target_amount' => 'required|numeric|min:1000',
        ]);

        FinancialGoal::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'target_amount' => $request->target_amount,
            'target_date' => $request->target_date,
        ]);

        return redirect()->back()
            ->with('success', 'Goal berhasil dibuat');
    }

public function showContributionForm(FinancialGoal $goal)
{
    $balance = $this->dashboardService
        ->getAvailableBalance(auth()->user());

    return view(
        'goals.contribute',
        compact('goal', 'balance')
    );
}

public function storeContribution(
    Request $request,
    FinancialGoal $goal
)
{
    $request->validate([
        'amount' => 'required|numeric|min:1000'
    ]);

    $availableBalance = $this->dashboardService
        ->getAvailableBalance(auth()->user());

    if ($request->amount > $availableBalance) {

        return back()
            ->withErrors([
                'amount' => 'Saldo tidak mencukupi'
            ])
            ->withInput();
    }

    GoalContribution::create([
        'goal_id' => $goal->id,
        'amount'  => $request->amount,
        'note'    => $request->note,
    ]);

    return redirect()
        ->route('goals.index')
        ->with('success', 'Dana berhasil ditambahkan');
}
}