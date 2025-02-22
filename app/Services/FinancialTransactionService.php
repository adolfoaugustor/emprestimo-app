<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;

class FinancialTransactionService
{
   public function recordTransaction($type, $amount, $description = null, $relatedId = null)
   {
      return DB::transaction(function () use ($type, $amount, $description, $relatedId) {
         $lastBalance = FinancialTransaction::latest()->value('balance') ?? 0;
         $newBalance = $type === 'investment' ? $lastBalance + $amount : $lastBalance - $amount;

         return FinancialTransaction::create([
               'transaction_type' => $type,
               'amount' => $amount,
               'transaction_date' => now(),
               'description' => $description,
               'related_id' => $relatedId,
               'balance' => $newBalance
         ]);
      });
   }
}