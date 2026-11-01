<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\InstallmentDueDateChange;
use App\Services\AccountContext;
use App\Services\FinancialTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentController extends Controller
{
    public function __construct(
        private FinancialTransactionService $financialTransactionService,
        private AccountContext $accountContext
    ) {
    }

    public function showPaymentForm(Request $request, int $installment_id)
    {
        $installment = Installment::with('charge.client')->findOrFail($installment_id);
        abort_unless($this->accountContext->ownsInstallment($request->user(), $installment), 404);

        return view('installments.pay', compact('installment'));
    }

    public function pay(Request $request, int $installment_id)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'penalty' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $ownerId = $this->accountContext->ownerId($user);
        $alreadyPaid = false;
        $clientId = DB::transaction(function () use ($user, $ownerId, $installment_id, $validated, &$alreadyPaid) {
            $installment = Installment::with('charge.client')->whereKey($installment_id)->lockForUpdate()->firstOrFail();
            abort_unless($this->accountContext->ownsInstallment($user, $installment), 404);
            if ($installment->payment_date) {
                $alreadyPaid = true;
                return $installment->charge->client_id;
            }

            $amount = (float) $validated['amount'];
            $discount = (float) ($validated['discount'] ?? 0);
            $penalty = (float) ($validated['penalty'] ?? 0);
            if ($discount > $amount) {
                throw ValidationException::withMessages(['discount' => 'O desconto não pode ser maior que o valor recebido.']);
            }

            $charge = $installment->charge()->lockForUpdate()->firstOrFail();
            $paidAmount = round($amount - $discount + $penalty, 2);
            $installment->forceFill([
                'payment_date' => now(),
                'discount' => $discount,
                'penalty' => $penalty,
                'amount_paid' => $paidAmount,
            ])->save();

            $charge->amount_paid = (float) $charge->amount_paid + $paidAmount;
            $charge->installments_paid++;
            if ($charge->installments_paid >= $charge->installments_count) {
                $charge->end_date = now();
            }
            $charge->save();

            $this->financialTransactionService->recordTransaction(
                $ownerId,
                $user->id,
                'payment',
                $paidAmount,
                'Pagamento recebido ID ' . $installment->id,
                $installment->id
            );

            return $charge->client_id;
        });

        return redirect()->route('charges.show', ['client_id' => $clientId])->with(
            $alreadyPaid ? 'error' : 'success',
            $alreadyPaid ? 'Esta parcela já foi paga.' : 'Pagamento registrado.'
        );
    }

    public function updateDueDate(Request $request, int $installment_id)
    {
        $validated = $request->validate(['due_date' => ['required', 'date']]);
        $user = $request->user();

        DB::transaction(function () use ($user, $installment_id, $validated) {
            $installment = Installment::with('charge.client')->whereKey($installment_id)->lockForUpdate()->firstOrFail();
            abort_unless($this->accountContext->ownsInstallment($user, $installment), 404);
            if ($installment->payment_date) {
                throw ValidationException::withMessages(['due_date' => 'Não é possível alterar o vencimento de uma parcela já paga.']);
            }

            $oldDate = Carbon::parse($installment->due_date)->toDateString();
            $newDate = Carbon::parse($validated['due_date'])->toDateString();
            if ($oldDate === $newDate) {
                return;
            }

            $installment->due_date = $newDate;
            $installment->save();

            InstallmentDueDateChange::create([
                'installment_id' => $installment->id,
                'old_due_date' => $oldDate,
                'new_due_date' => $newDate,
                'changed_by_user_id' => $user->id,
            ]);
        });

        return redirect()->back()->with('success', 'Vencimento atualizado.');
    }
}
