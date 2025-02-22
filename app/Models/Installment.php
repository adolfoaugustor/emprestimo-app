<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'charge_id',
        'amount',
        'amount_paid',
        'payment_date',
        'due_date',
        'discount',
        'penalty',
    ];

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $this->formatDecimal($value);
    }

    public function setDiscountAttribute($value)
    {
        $this->attributes['discount'] = $this->formatDecimal($value);
    }

    public function setPenaltyAttribute($value)
    {
        $this->attributes['penalty'] = $this->formatDecimal($value);
    }

    public function setAmountPaidAttribute($value)
    {
        $this->attributes['amount_paid'] = $this->formatDecimal($value);
    }

    private function formatDecimal($value)
    {
        $value = preg_replace('/[^0-9.]/', '', $value);
        return number_format((float) $value, 2, '.', '');
    }
}