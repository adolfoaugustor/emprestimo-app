<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $transactions = FinancialTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date')
            ->get();

        $summary = [
            'total_investments' => $transactions->where('transaction_type', 'investment')->sum('amount'),
            'total_charges' => $transactions->where('transaction_type', 'charge')->sum('amount'),
            'total_payments' => $transactions->where('transaction_type', 'payment')->sum('amount'),
            'current_balance' => $transactions->last()->balance ?? 0,
        ];

        return view('financial.report', compact('transactions', 'summary', 'startDate', 'endDate'));
    }
}
