<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinancialTransactionService
{
   public function recordTransaction(int $accountOwnerId, int $createdByUserId, string $type, $amount, $description = null, $relatedId = null)
   {
      return DB::transaction(function () use ($accountOwnerId, $createdByUserId, $type, $amount, $description, $relatedId) {
         User::query()->whereKey($accountOwnerId)->whereNull('account_owner_id')->lockForUpdate()->firstOrFail();
         $authorCanWrite = User::query()->whereKey($createdByUserId)
            ->where(function ($query) use ($accountOwnerId) {
               $query->whereKey($accountOwnerId)->orWhere('account_owner_id', $accountOwnerId);
            })->exists();
         abort_unless($authorCanWrite, 403);

         $lastBalance = FinancialTransaction::query()
            ->where('account_owner_id', $accountOwnerId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->value('balance') ?? 0;
         $newBalance = round($type === 'investment' ? $lastBalance + $amount : $lastBalance - $amount, 2);

         return FinancialTransaction::create([
               'account_owner_id' => $accountOwnerId,
               'created_by_user_id' => $createdByUserId,
               'transaction_type' => $type,
               'amount' => $amount,
               'transaction_date' => now(),
               'description' => $description,
               'related_id' => $relatedId,
               'balance' => $newBalance
         ]);
      });
   }

   public function recalculateBalances(array $accountOwnerIds): void
   {
      DB::transaction(function () use ($accountOwnerIds) {
         $ownerIds = collect($accountOwnerIds)->map(fn ($id) => (int) $id)->unique()->sort()->values();
         User::query()->whereIn('id', $ownerIds)->orderBy('id')->lockForUpdate()->get();

         foreach ($ownerIds as $ownerId) {
            $balance = 0.0;
            FinancialTransaction::query()
               ->where('account_owner_id', $ownerId)
               ->orderBy('transaction_date')
               ->orderBy('id')
               ->chunk(500, function ($transactions) use (&$balance) {
                  foreach ($transactions as $transaction) {
                     $amount = (float) $transaction->amount;
                     $balance = round($transaction->transaction_type === 'investment'
                        ? $balance + $amount
                        : $balance - $amount, 2);

                     $transaction->forceFill(['balance' => $balance])->save();
                  }
               });
         }
      });
   }
}
