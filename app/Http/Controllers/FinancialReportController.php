<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Services\AccountContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function __construct(private AccountContext $accountContext)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->startOfMonth());
        $endDate = Carbon::parse($validated['end_date'] ?? now()->endOfMonth())->endOfDay();

        $ownerId = $this->accountContext->ownerId($request->user());
        $transactions = FinancialTransaction::where('account_owner_id', $ownerId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $currentBalance = FinancialTransaction::where('account_owner_id', $ownerId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->value('balance') ?? 0;

        $summary = [
            'total_investments' => $transactions->where('transaction_type', 'investment')->sum('amount'),
            'total_charges' => $transactions->where('transaction_type', 'charge')->sum('amount'),
            'total_payments' => $transactions->where('transaction_type', 'payment')->sum('amount'),
            'current_balance' => $currentBalance,
        ];

        return view('financial.report', compact('transactions', 'summary', 'startDate', 'endDate'));
    }
}
