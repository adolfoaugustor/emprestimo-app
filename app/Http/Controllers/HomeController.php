<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Installment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\FinancialTransactionService;
use App\Services\AccountContext;

class HomeController extends Controller
{
    protected $financialTransactionService;
    protected $accountContext;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FinancialTransactionService $financialTransactionService, AccountContext $accountContext)
    {
        $this->middleware('auth');
        $this->financialTransactionService = $financialTransactionService;
        $this->accountContext = $accountContext;
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

        $clients = $this->accountContext->clientsFor($user)->with('charges')->orderBy('name')->get();
        $clientIds = $clients->modelKeys();
        $isAccountOwner = $this->accountContext->isOwner($user);
        $transferOwners = $isAccountOwner
            ? User::query()->whereNull('account_owner_id')->where('is_active', true)->where('id', '<>', $user->id)->orderBy('name')->get(['id', 'name'])
            : collect();

        $totalPaid = $isAccountOwner ? Installment::whereHas('charge', function ($query) use ($clientIds) {
            $query->whereIn('client_id', $clientIds);
        })->whereNotNull('payment_date')->sum('amount') : null;

        $totalReceivable = $isAccountOwner ? Installment::whereHas('charge', function ($query) use ($clientIds) {
            $query->whereIn('client_id', $clientIds);
        })
        ->whereNull('payment_date')
        ->whereBetween('due_date', [$currentMonthStart, $currentMonthEnd])
        ->sum('amount') : null;

        $charger = $isAccountOwner ? Charge::whereIn('client_id', $clientIds)->sum('total_amount') : null;

        $totalClients = $clients->count();
        $pendingInstallments = Installment::with('charge.client')
            ->whereHas('charge', function ($query) use ($clientIds) {
                $query->whereIn('client_id', $clientIds);
            })
            ->whereNull('payment_date')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $canCreateClients = $this->accountContext->canPerform($user, 'create_clients');
        $canCreateCharges = $this->accountContext->canPerform($user, 'create_charges');
        $canRecordPayments = $this->accountContext->canPerform($user, 'record_payments');

        return view('home', compact(
            'clients', 'totalClients', 'totalPaid', 'totalReceivable', 'charger', 'pendingInstallments',
            'isAccountOwner', 'canCreateClients', 'canCreateCharges', 'canRecordPayments', 'transferOwners'
        ));
    }

    public function addInvestment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $this->financialTransactionService->recordTransaction(
            $this->accountContext->ownerId($request->user()),
            $request->user()->id,
            'investment',
            $validated['amount'],
            $validated['description']
        );

        return redirect()->route('home')->with('success', 'Investimento adicionado com sucesso!');
    }
}
