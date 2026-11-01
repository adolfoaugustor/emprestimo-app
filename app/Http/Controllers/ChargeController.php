<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Client;
use App\Models\Installment;
use App\Services\AccountContext;
use App\Services\ChargeService;
use App\Services\FinancialTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChargeController extends Controller
{
    public function __construct(
        private FinancialTransactionService $financialTransactionService,
        private ChargeService $chargeService,
        private AccountContext $accountContext
    ) {
    }

    public function create(Request $request, int $client_id)
    {
        $client = $this->accountContext->clientsFor($request->user())->findOrFail($client_id);

        if ($this->hasActiveCharge($client->id)) {
            return redirect()->route('home')->with('error', 'Este cliente já possui uma cobrança ativa.');
        }

        $zeroPayments = $this->chargeService->getZeroPaymentsFromLastCharge($client->id);

        return view('charges.create', compact('client', 'zeroPayments'));
    }

    public function show(Request $request, int $client_id)
    {
        $client = $this->accountContext->clientsFor($request->user())
            ->with(['charges' => fn ($query) => $query->orderByDesc('created_at'), 'charges.installments' => fn ($query) => $query->orderBy('due_date')])
            ->findOrFail($client_id);

        if ($client->charges->isEmpty()) {
            return redirect()->route('home')->with('error', 'Cliente não possui cobranças.');
        }

        $charge = $client->charges->firstWhere('end_date', null) ?? $client->charges->first();
        $charge->load([
            'installments' => fn ($query) => $query->orderBy('due_date'),
            'installments.dueDateChanges.changedBy',
        ]);

        return view('charges.show', compact('client', 'charge'));
    }

    public function store(Request $request, int $client_id)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'installments_count' => ['required', 'integer', 'min:1', 'max:120'],
            'include_payments' => ['nullable', 'array'],
            'include_payments.*' => ['integer', 'distinct'],
        ]);

        $user = $request->user();
        $ownerId = $this->accountContext->ownerId($user);
        $includedIds = $validated['include_payments'] ?? [];

        $charge = DB::transaction(function () use ($user, $ownerId, $client_id, $validated, $includedIds) {
            $client = $this->accountContext->clientsFor($user)->whereKey($client_id)->lockForUpdate()->firstOrFail();

            if ($this->hasActiveCharge($client->id)) {
                throw ValidationException::withMessages(['total_amount' => 'Este cliente já possui uma cobrança ativa.']);
            }

            $eligibleInstallments = collect();
            if ($includedIds !== []) {
                $eligibleInstallments = Installment::query()
                    ->whereIn('id', $includedIds)
                    ->whereNotNull('payment_date')
                    ->where('amount_paid', 0)
                    ->whereHas('charge', fn ($query) => $query->where('client_id', $client->id))
                    ->lockForUpdate()
                    ->get();

                if ($eligibleInstallments->count() !== count($includedIds)) {
                    throw ValidationException::withMessages(['include_payments' => 'Uma ou mais parcelas selecionadas não pertencem a este cliente.']);
                }
            }

            $totalAmount = round((float) $eligibleInstallments->sum('amount') + (float) $validated['total_amount'], 2);
            $charge = Charge::create([
                'client_id' => $client->id,
                'created_by_user_id' => $user->id,
                'start_date' => $validated['start_date'],
                'total_amount' => $totalAmount,
                'installments_count' => $validated['installments_count'],
            ]);

            $this->financialTransactionService->recordTransaction(
                $ownerId,
                $user->id,
                'charge',
                $charge->total_amount,
                'Cobrança #' . $charge->id,
                $charge->id
            );

            $this->generateInstallments($charge);

            return $charge;
        });

        return redirect()->route('charges.show', ['client_id' => $charge->client_id])
            ->with('success', 'Empréstimo criado com sucesso!');
    }

    private function generateInstallments(Charge $charge): void
    {
        $incrementedAmount = (float) $charge->total_amount * 1.20;
        $installmentAmount = $incrementedAmount / $charge->installments_count;
        $startDate = Carbon::parse($charge->start_date)->addDay();

        if ($startDate->isFriday()) {
            $startDate->addDays(3);
        } elseif ($startDate->isSaturday()) {
            $startDate->addDays(2);
        } elseif ($startDate->isSunday()) {
            $startDate->addDay();
        }

        for ($index = 0; $index < $charge->installments_count; $index++) {
            Installment::create([
                'charge_id' => $charge->id,
                'amount' => $installmentAmount,
                'due_date' => $startDate->copy(),
            ]);

            do {
                $startDate->addDay();
            } while ($startDate->isWeekend());
        }
    }

    private function hasActiveCharge(int $clientId): bool
    {
        return Charge::query()->where('client_id', $clientId)->whereNull('end_date')->exists();
    }
}
