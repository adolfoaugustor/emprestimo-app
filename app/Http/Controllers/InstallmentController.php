<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function showPaymentForm($installment_id)
    {
        $installment = Installment::findOrFail($installment_id);
        return view('installments.pay', compact('installment'));
    }

    public function pay(Request $request, $installment_id)
    {
        $installment = Installment::findOrFail($installment_id);

        if (!$installment->payment_date) {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'penalty' => 'nullable|numeric|min:0',
            ]);

            $installment->payment_date = now();
            $installment->discount = $validated['discount'] ?? 0;
            $installment->penalty = $validated['penalty'] ?? 0;
            $installment->save();

            // Atualize o valor pago na cobrança
            $charge = $installment->charge;
            $charge->amount_paid += $validated['amount'] - $installment->discount + $installment->penalty;
            $charge->installments_paid += 1;

            // Verifique se todas as parcelas foram pagas
            if ($charge->installments_paid == $charge->installments_count) {
                $charge->end_date = now();
            }

            $charge->save();
            return redirect()->route('charges.show', ['client_id' => $charge->client_id])->with('success', 'Parcela paga!');
        }

        return redirect()->route('charges.show', ['client_id' => $installment->charge->client_id])->with('error', 'Parcela já foi paga!');
    }

    public function updateDueDate(Request $request, $installment_id)
    {
        $request->validate([
            'due_date' => 'required|date',
        ]);

        $installment = Installment::findOrFail($installment_id);
        $installment->due_date = $request->input('due_date');
        $installment->save();

        return redirect()->back()->with('success', __('Data da parcela atualizada!'));
    }
}
