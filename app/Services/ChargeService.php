<?php

namespace App\Services;

use App\Models\Charge;
use App\Models\Installment;
use Carbon\Carbon;

class ChargeService
{
    public function getZeroPaymentsFromLastCharge(int $userId): array
    {
        $lastCharge = Charge::where('client_id', $userId)->latest()->first();

        if (!$lastCharge) {
            return [];
        }

        return $lastCharge->installments()
            ->where('amount', 0.00)
            ->whereNotNull('payment_date')
            ->get()
            ->toArray();
    }
}