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
        $user = auth()->user();
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        // Obter os IDs dos clientes associados ao usuário atual
        $clientIds = $user->clients()->pluck('clients.id');

        $totalPaid = Installment::whereHas('charge', function ($query) use ($clientIds) {
            $query->whereIn('client_id', $clientIds);
        })
        ->whereNotNull('payment_date')
        ->sum('amount');

        $totalReceivable = Installment::whereHas('charge', function ($query) use ($clientIds) {
            $query->whereIn('client_id', $clientIds);
        })
        ->whereNull('payment_date')
        ->whereBetween('due_date', [$currentMonthStart, $currentMonthEnd])
        ->sum('amount');

        $charger = Charge::whereIn('client_id', $clientIds)->sum('total_amount');

        $clients = $user->clients()->with('charges')->get();
        $totalClients = $clients->count();

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
