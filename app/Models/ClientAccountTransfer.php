<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAccountTransfer extends Model
{
    protected $fillable = [
        'client_id',
        'from_account_owner_id',
        'to_account_owner_id',
        'transferred_by_user_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function fromAccountOwner()
    {
        return $this->belongsTo(User::class, 'from_account_owner_id');
    }

    public function toAccountOwner()
    {
        return $this->belongsTo(User::class, 'to_account_owner_id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }
}
