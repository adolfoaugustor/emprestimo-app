<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Client;
use App\Models\Installment;
use Carbon\Carbon;
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
        
        $client = Client::with([
            'charges' => function ($query) {
                $query->orderBy('created_at', 'desc'); // Ordena as cobranças pela data de criação, mais recente primeiro
            },
            'charges.installments' => function ($query) {
                $query->orderBy('due_date', 'asc');
            }
        ])->findOrFail($client_id);
        
        // add redirect to home with message client not charge
        if ($client->charges->isEmpty()) {
            return redirect()->route('home')->with('error', 'Cliente não possui cobranças.');
        }
            
        // Tenta obter a cobrança ativa (sem data de término)
        $charge = $client->charges->firstWhere('end_date', null);

        // Se não houver cobrança ativa, pega a última criada
        if (!$charge) {
            $charge = $client->charges->first();
        }

        // Carrega as parcelas da cobrança selecionada, se existir
        if ($charge) {
            $charge->load(['installments' => function ($query) {
                $query->orderBy('due_date', 'asc');
            }]);
        }

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
        // Calcular o valor total com 20% de incremento
        $originalAmount = $charge['total_amount'];
        $incrementedAmount = $originalAmount * 1.20; // Incrementa 20%

        $installmentAmount = $incrementedAmount / $charge->installments_count;
        $startDate = Carbon::parse($charge->start_date)->addDay();
    
        // Ajustar a data inicial para o próximo dia útil, se necessário
        switch ($startDate->dayOfWeek) {
            case Carbon::SATURDAY:
                $startDate->addDays(2); // Pular para segunda-feira
                break;
            case Carbon::SUNDAY:
                $startDate->addDay(); // Pular para segunda-feira
                break;
            case Carbon::FRIDAY:
                $startDate->addDays(3); // Pular para segunda-feira
                break;
            // Nenhum ajuste necessário para outros dias da semana
        }
    
        for ($i = 0; $i < $charge->installments_count; $i++) {
            // Criar a parcela com a data de vencimento calculada
            Installment::create([
                'charge_id' => $charge->id,
                'amount' => $installmentAmount,
                'due_date' => $startDate->copy(),
            ]);
    
            // Incrementar a data por 1 dia, pulando fins de semana
            do {
                $startDate->addDay();
            } while ($startDate->isWeekend());
        }
    }


}
