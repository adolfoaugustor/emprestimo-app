<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Client;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\FinancialTransactionService;

class HomeController extends Controller
{
    protected $financialTransactionService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FinancialTransactionService $financialTransactionService)
    {
        $this->middleware('auth');
        $this->financialTransactionService = $financialTransactionService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $charger = $totalPaid = $totalReceivable = $totalClients = 0;
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $totalPaid = Installment::whereNotNull('payment_date')
                    ->sum('amount');
        $totalReceivable = Installment::whereNull('payment_date')
                        ->whereBetween('due_date', [$currentMonthStart, $currentMonthEnd])
                        ->sum('amount');
        $charger = Charge::all()->sum('total_amount');
        $clients = Client::with('charges')->get();
        $totalClients = count(Client::with('charges')->get());

        return view('home', compact('clients', 'totalClients', 'totalPaid', 'totalReceivable', 'charger'));
    }

    public function addInvestment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $this->financialTransactionService->recordTransaction(
            'investment',
            $validated['amount'],
            $validated['description']
        );

        return redirect()->route('home')->with('success', 'Investimento adicionado com sucesso!');
    }
}
