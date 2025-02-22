<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Client;
use App\Models\Installment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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
}
