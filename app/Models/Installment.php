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
        'payment_date',
        'due_date',
        'discount',
        'penalty',
    ];

    public function charge()
    {
        return $this->belongsTo(Charge::class);
    }
}