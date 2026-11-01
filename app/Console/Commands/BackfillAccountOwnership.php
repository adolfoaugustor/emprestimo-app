<?php

namespace App\Console\Commands;

use App\Models\Charge;
use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\Installment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAccountOwnership extends Command
{
    protected $signature = 'accounts:backfill-ownership {--orphan-owner-id= : Titular que receberá os registros sem vínculo confiável} {--apply : Aplicar o mapeamento após a revisão}';

    protected $description = 'Pré-visualiza ou aplica o mapeamento de clientes e movimentos antigos para contas titulares.';

    public function handle(): int
    {
        $orphanOwnerId = (int) $this->option('orphan-owner-id');
        $orphanOwner = User::query()->whereKey($orphanOwnerId)->whereNull('account_owner_id')->where('is_active', true)->first();
        if (!$orphanOwner) {
            $this->error('Informe --orphan-owner-id com o ID de uma conta titular existente.');
            return self::FAILURE;
        }

        [$clientPlan, $conflicts] = $this->clientPlan($orphanOwnerId);
        [$transactionPlan, $transactionConflicts] = $this->transactionPlan($orphanOwnerId, $clientPlan);
        $conflicts = [...$conflicts, ...$transactionConflicts];
        $ownerNames = User::query()->pluck('name', 'id');

        $this->info('Prévia do mapeamento (não altera o banco sem --apply e confirmação).');
        $this->line('Conta destino dos registros ambíguos: ' . $orphanOwner->id . ' — ' . $orphanOwner->name);
        $this->table(['Cliente ID', 'Cliente', 'Titular', 'Origem'], array_map(
            fn ($row) => [$row['id'], $row['name'], $row['owner_id'] ? $row['owner_id'] . ' — ' . $ownerNames->get($row['owner_id'], 'REVISAR') : 'REVISAR', $row['source']],
            $clientPlan
        ));
        $this->table(['Movimento ID', 'Data', 'Tipo', 'Relacionado', 'Valor', 'Titular', 'Origem'], array_map(
            fn ($row) => [$row['id'], $row['date'], $row['type'], $row['related_id'] ?? '—', number_format($row['amount'], 2, ',', '.'), $row['owner_id'] ? $row['owner_id'] . ' — ' . $ownerNames->get($row['owner_id'], 'REVISAR') : 'REVISAR', $row['source']],
            $transactionPlan
        ));
        $this->line('Saldo global atualmente gravado: R$ ' . number_format((float) (FinancialTransaction::query()->orderByDesc('transaction_date')->orderByDesc('id')->value('balance') ?? 0), 2, ',', '.'));
        $this->table(['Titular', 'Movimentos', 'Investimentos', 'Cobranças', 'Pagamentos', 'Saldo projetado'], $this->financialSummary($transactionPlan, $ownerNames));

        if ($conflicts !== []) {
            $this->error('Há clientes vinculados a titulares diferentes. Resolva esses conflitos antes de aplicar o backfill.');
            $this->table(['Registro', 'Conflito de titularidade'], array_map(function ($row) use ($ownerNames) {
                $ids = array_filter(explode(', ', (string) $row[1]));
                return [$row[0], collect($ids)->map(fn ($id) => $id . ' — ' . $ownerNames->get($id, 'REVISAR'))->implode(', ')];
            }, $conflicts));
            return self::FAILURE;
        }

        if (!$this->option('apply')) {
            $this->comment('Prévia concluída. Revise os destinos acima antes de executar com --apply.');
            return self::SUCCESS;
        }

        if (is_file(storage_path('framework/down'))) {
            $this->error('A aplicação já está em manutenção. Verifique o estado do servidor antes do backfill.');
            return self::FAILURE;
        }

        if (!$this->confirm('Você revisou e aprovou cada destino listado e quer aplicar o backfill?')) {
            $this->comment('Backfill cancelado; nenhuma alteração foi feita.');
            return self::SUCCESS;
        }

        $this->call('down', ['--retry' => 30]);
        try {
            DB::transaction(function () use ($clientPlan, $transactionPlan) {
                foreach ($clientPlan as $row) {
                    Client::query()->whereKey($row['id'])->whereNull('account_owner_id')
                        ->update(['account_owner_id' => $row['owner_id']]);
                }

                foreach ($transactionPlan as $row) {
                    FinancialTransaction::query()->whereKey($row['id'])->whereNull('account_owner_id')
                        ->update(['account_owner_id' => $row['owner_id']]);
                }

                if (Client::query()->whereNull('account_owner_id')->exists()
                    || FinancialTransaction::query()->whereNull('account_owner_id')->exists()) {
                    throw new \RuntimeException('Novos registros apareceram durante o backfill. Nenhum registro foi alterado.');
                }

                $balances = [];
                FinancialTransaction::query()
                    ->orderBy('account_owner_id')
                    ->orderBy('transaction_date')
                    ->orderBy('id')
                    ->chunk(500, function ($transactions) use (&$balances) {
                        foreach ($transactions as $transaction) {
                            $ownerId = $transaction->account_owner_id;
                            $previous = $balances[$ownerId] ?? 0;
                            $amount = (float) $transaction->amount;
                            $balance = round($transaction->transaction_type === 'investment'
                                ? $previous + $amount
                                : $previous - $amount, 2);

                            $transaction->forceFill(['balance' => $balance])->save();
                            $balances[$ownerId] = $balance;
                        }
                    });
            });
        } finally {
            $this->call('up');
        }

        $this->info('Titularidade atribuída e saldos recalculados por conta.');
        return self::SUCCESS;
    }

    private function clientPlan(int $orphanOwnerId): array
    {
        $rows = [];
        $conflicts = [];

        Client::query()->with('users:id,account_owner_id')->orderBy('id')->chunk(200, function ($clients) use (&$rows, &$conflicts, $orphanOwnerId) {
            foreach ($clients as $client) {
                if ($client->account_owner_id) {
                    $linkedOwners = $client->users->map(fn (User $user) => $user->account_owner_id ?? $user->id)->unique()->values();
                    if (!User::query()->whereKey($client->account_owner_id)->whereNull('account_owner_id')->exists()
                        || $linkedOwners->contains(fn ($ownerId) => (int) $ownerId !== (int) $client->account_owner_id)) {
                        $conflicts[] = ['Cliente #' . $client->id, $linkedOwners->push($client->account_owner_id)->unique()->implode(', ')];
                        continue;
                    }

                    $rows[] = [
                        'id' => $client->id,
                        'name' => $client->name,
                        'owner_id' => (int) $client->account_owner_id,
                        'source' => 'titularidade atual',
                    ];
                    continue;
                }

                $ownerIds = $client->users->map(fn (User $user) => $user->account_owner_id ?? $user->id)->unique()->values();
                if ($ownerIds->count() > 1) {
                    $conflicts[] = ['Cliente #' . $client->id, $ownerIds->implode(', ')];
                    continue;
                }

                $hasExistingOwnerLink = $ownerIds->count() === 1;
                $rows[] = [
                    'id' => $client->id,
                    'name' => $client->name,
                    'owner_id' => $hasExistingOwnerLink ? (int) $ownerIds->first() : $orphanOwnerId,
                    'source' => $hasExistingOwnerLink ? 'vínculo atual' : 'sem vínculo → conta de teste',
                ];
            }
        });

        return [$rows, $conflicts];
    }

    private function transactionPlan(int $orphanOwnerId, array $clientPlan): array
    {
        $clientOwners = collect($clientPlan)->keyBy('id')->map(fn ($row) => $row['owner_id']);
        Client::query()->whereNotNull('account_owner_id')->pluck('account_owner_id', 'id')->each(
            fn ($ownerId, $clientId) => $clientOwners->put($clientId, (int) $ownerId)
        );

        $rows = [];
        $conflicts = [];
        FinancialTransaction::query()->orderBy('id')->chunk(200, function ($transactions) use (&$rows, &$conflicts, $clientOwners, $orphanOwnerId) {
            foreach ($transactions as $transaction) {
                $clientId = null;
                if ($transaction->transaction_type === 'charge' && $transaction->related_id) {
                    $clientId = Charge::query()->whereKey($transaction->related_id)->value('client_id');
                } elseif ($transaction->transaction_type === 'payment' && $transaction->related_id) {
                    $clientId = Installment::query()->whereKey($transaction->related_id)
                        ->join('charges', 'charges.id', '=', 'installments.charge_id')
                        ->value('charges.client_id');
                }

                $hasClientOwner = $clientId !== null && $clientOwners->has($clientId);
                $linkedOwnerId = $hasClientOwner ? (int) $clientOwners->get($clientId) : null;
                if ($transaction->account_owner_id) {
                    $explicitOwnerIsRoot = User::query()->whereKey($transaction->account_owner_id)->whereNull('account_owner_id')->exists();
                    if (!$explicitOwnerIsRoot || ($linkedOwnerId !== null && $linkedOwnerId !== (int) $transaction->account_owner_id)) {
                        $conflicts[] = ['Movimento #' . $transaction->id, collect([$transaction->account_owner_id, $linkedOwnerId])->filter()->unique()->implode(', ')];
                        continue;
                    }

                    $rows[] = [
                        'id' => $transaction->id,
                        'type' => $transaction->transaction_type,
                        'date' => $transaction->transaction_date->format('Y-m-d H:i:s'),
                        'related_id' => $transaction->related_id,
                        'amount' => (float) $transaction->amount,
                        'owner_id' => (int) $transaction->account_owner_id,
                        'source' => 'titularidade atual',
                    ];
                    continue;
                }

                $rows[] = [
                    'id' => $transaction->id,
                    'type' => $transaction->transaction_type,
                    'date' => $transaction->transaction_date->format('Y-m-d H:i:s'),
                    'related_id' => $transaction->related_id,
                    'amount' => (float) $transaction->amount,
                    'owner_id' => $linkedOwnerId ?? $orphanOwnerId,
                    'source' => $hasClientOwner ? 'cliente relacionado' : 'sem vínculo → conta de teste',
                ];
            }
        });

        return [$rows, $conflicts];
    }

    private function financialSummary(array $transactionPlan, $ownerNames): array
    {
        return collect($transactionPlan)->groupBy('owner_id')->map(function ($transactions, $ownerId) use ($ownerNames) {
            $ordered = $transactions->sortBy(fn ($row) => $row['date'] . '|' . str_pad((string) $row['id'], 20, '0', STR_PAD_LEFT));
            $balance = 0.0;
            foreach ($ordered as $transaction) {
                $balance = round($transaction['type'] === 'investment'
                    ? $balance + $transaction['amount']
                    : $balance - $transaction['amount'], 2);
            }

            return [
                $ownerId . ' — ' . $ownerNames->get($ownerId, 'REVISAR'),
                $transactions->count(),
                number_format($transactions->where('type', 'investment')->sum('amount'), 2, ',', '.'),
                number_format($transactions->where('type', 'charge')->sum('amount'), 2, ',', '.'),
                number_format($transactions->where('type', 'payment')->sum('amount'), 2, ',', '.'),
                number_format($balance, 2, ',', '.'),
            ];
        })->values()->all();
    }
}
