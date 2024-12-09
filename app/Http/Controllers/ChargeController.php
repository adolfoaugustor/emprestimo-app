<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Client;
use App\Models\Installment;
use Illuminate\Http\Request;

class ChargeController extends Controller
{
    public function create($client_id)
    {
        $client = Client::findOrFail($client_id);
        return view('charges.create', compact('client'));
    }

    public function show($client_id)
    {
        $client = Client::with(['charges', 'charges.installments'])->findOrFail($client_id);
        $charge = $client->charges->first(); // Assumindo que estamos mostrando a primeira cobrança

        return view('charges.show', compact('client', 'charge'));
    }
    
    public function store(Request $request, $client_id)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'installments_count' => 'required|integer|min:1',
        ]);

        $charge = Charge::create([
            'client_id' => $client_id,
            'start_date' => $validated['start_date'],
            'total_amount' => $validated['total_amount'],
            'installments_count' => $validated['installments_count'],
        ]);

        $this->generateInstallments($charge);

        return redirect()
                ->route('charges.show', ['client_id' => $client_id])
                ->with('success', 'Cobrança criada com sucesso!');
    }

    private function generateInstallments(Charge $charge)
    {
        $installmentAmount = $charge->total_amount / $charge->installments_count;
        $startDate = \Carbon\Carbon::parse($charge->start_date);

        for ($i = 0; $i < $charge->installments_count; $i++) {
            // Increment the date by 1 day, skipping weekends
            do {
                $startDate->addDay();
            } while ($startDate->isWeekend());

            // Create the installment with the calculated due date
            Installment::create([
                'charge_id' => $charge->id,
                'amount' => $installmentAmount,
                'due_date' => $startDate->copy(),
            ]);
        }
    }
}
