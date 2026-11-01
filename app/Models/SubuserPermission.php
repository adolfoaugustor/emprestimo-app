<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubuserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'create_clients',
        'create_charges',
        'record_payments',
        'change_due_dates',
    ];

    protected $casts = [
        'create_clients' => 'boolean',
        'create_charges' => 'boolean',
        'record_payments' => 'boolean',
        'change_due_dates' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
