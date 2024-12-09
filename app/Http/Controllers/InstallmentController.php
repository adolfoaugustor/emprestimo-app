<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function pay($installment_id)
    {
        $installment = Installment::findOrFail($installment_id);

        if (!$installment->payment_date) {
            $installment->payment_date = now();
            $installment->save();

            // Atualize o valor pago na cobrança
            $charge = $installment->charge;
            $charge->amount_paid += $installment->amount;
            $charge->installments_paid += 1;

            // Verifique se todas as parcelas foram pagas
            if ($charge->installments_paid == $charge->installments_count) {
                $charge->end_date = now();
            }

            $charge->save();

            return redirect()->back()->with('success', 'Installment paid successfully!');
        }

        return redirect()->back()->with('error', 'Installment already paid!');
    }
}
