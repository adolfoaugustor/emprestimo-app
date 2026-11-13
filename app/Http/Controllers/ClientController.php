<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAccountTransfer;
use App\Models\User;
use App\Services\AccountContext;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function __construct(
        private AccountContext $accountContext,
        private FinancialTransactionService $financialTransactionService
    )
    {
        $this->middleware('auth');
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:clients,email'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'phone1' => ['nullable', 'string', 'max:15'],
            'phone2' => ['nullable', 'string', 'max:15'],
            'birth_date' => ['nullable', 'date'],
            'document' => ['required', 'string', 'max:20', 'unique:clients,document'],
        ]);

        $user = $request->user();
        $ownerId = $this->accountContext->ownerId($user);

        DB::transaction(function () use ($validated, $user, $ownerId) {
            $client = Client::create([
                ...$validated,
                'account_owner_id' => $ownerId,
                'created_by_user_id' => $user->id,
            ]);

            User::findOrFail($ownerId)->clients()->syncWithoutDetaching([$client->id]);
        });

        return redirect()->route('home')->with('success', 'Cliente cadastrado na carteira do titular.');
    }

    public function edit(Request $request, int $id)
    {
        $client = $this->accountContext->clientsFor($request->user())
            ->with('accountTransfers')->findOrFail($id);

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, int $id)
    {
        $client = $this->accountContext->clientsFor($request->user())->findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($client->id)],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'phone1' => ['nullable', 'string', 'max:15'],
            'phone2' => ['nullable', 'string', 'max:15'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['nullable', 'integer', 'in:1,2'],
            'document' => ['required', 'string', 'max:20', Rule::unique('clients', 'document')->ignore($client->id)],
        ]);

        $client->update($validated);

        return redirect()->route('home')->with('success', 'Cliente atualizado.');
    }

    public function destroy(Request $request, int $id)
    {
        $client = $this->accountContext->clientsFor($request->user())->with('charges')->findOrFail($id);

        if ($client->charges->isNotEmpty()) {
            return redirect()->route('home')
                ->with('error', 'Cliente não pode ser excluído fisicamente pois possui histórico de cobranças vinculado. Clientes com cobranças quitadas ficam automaticamente ocultos da listagem ativa para preservar o histórico contábil.');
        }

        DB::transaction(function () use ($client) {
            $client->users()->detach();
            $client->delete();
        });

        return redirect()->route('home')->with('success', 'Cliente excluído com sucesso.');
    }

    public function transfer(Request $request, int $id)
    {
        $client = $this->accountContext->clientsFor($request->user())->findOrFail($id);
        $validated = $request->validate([
            'target_account_owner_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereNull('account_owner_id')->where('is_active', true)),
            ],
        ]);

        $sourceOwnerId = $this->accountContext->ownerId($request->user());
        $targetOwnerId = (int) $validated['target_account_owner_id'];
        if ($sourceOwnerId === $targetOwnerId) {
            throw ValidationException::withMessages(['target_account_owner_id' => 'Escolha outra conta titular.']);
        }

        DB::transaction(function () use ($request, $client, $sourceOwnerId, $targetOwnerId) {
            $lockedClient = $this->accountContext->clientsFor($request->user())
                ->whereKey($client->id)->lockForUpdate()->firstOrFail();

            $chargeIds = $lockedClient->charges()->pluck('id');
            $installmentIds = \App\Models\Installment::query()
                ->whereIn('charge_id', $chargeIds)->pluck('id');

            $lockedClient->forceFill(['account_owner_id' => $targetOwnerId])->save();
            ClientAccountTransfer::create([
                'client_id' => $lockedClient->id,
                'from_account_owner_id' => $sourceOwnerId,
                'to_account_owner_id' => $targetOwnerId,
                'transferred_by_user_id' => $request->user()->id,
            ]);
            $sourceUserIds = User::query()->where('account_owner_id', $sourceOwnerId)->pluck('id')->push($sourceOwnerId);
            $lockedClient->users()->detach($sourceUserIds);
            User::findOrFail($targetOwnerId)->clients()->syncWithoutDetaching([$lockedClient->id]);

            \App\Models\FinancialTransaction::query()
                ->where('transaction_type', 'charge')->whereIn('related_id', $chargeIds)
                ->update(['account_owner_id' => $targetOwnerId]);
            \App\Models\FinancialTransaction::query()
                ->where('transaction_type', 'payment')->whereIn('related_id', $installmentIds)
                ->update(['account_owner_id' => $targetOwnerId]);

            $this->financialTransactionService->recalculateBalances([$sourceOwnerId, $targetOwnerId]);
        });

        return redirect()->route('home')->with('success', 'Cliente e movimentos vinculados transferidos para a conta selecionada.');
    }
}
