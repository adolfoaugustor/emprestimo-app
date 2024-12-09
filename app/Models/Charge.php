<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'start_date',
        'end_date',
        'total_amount',
        'amount_paid',
        'installments_count',
        'installments_paid',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }
}