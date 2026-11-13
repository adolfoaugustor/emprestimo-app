<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'description',
        'address',
        'city',
        'district',
        'phone1',
        'phone2',
        'birth_date',
        'status',
        'document',
        'account_owner_id',
        'created_by_user_id',
    ];

    public function charges()
    {
        return $this->hasMany(Charge::class);
    }

    /**
     * Os usuários associados ao cliente.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function accountOwner()
    {
        return $this->belongsTo(User::class, 'account_owner_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function accountTransfers()
    {
        return $this->hasMany(ClientAccountTransfer::class)->with(['fromAccountOwner', 'toAccountOwner', 'transferredBy'])->orderByDesc('created_at');
    }

    /**
     * Verifica se o cliente possui algum empréstimo ativo (cobrança sem end_date).
     */
    public function hasActiveCharge(): bool
    {
        if ($this->relationLoaded('charges')) {
            return $this->charges->contains(fn ($charge) => is_null($charge->end_date));
        }

        return $this->charges()->whereNull('end_date')->exists();
    }

    /**
     * Verifica se o cliente já finalizou/quitou todos os seus empréstimos.
     */
    public function isSettled(): bool
    {
        if ($this->relationLoaded('charges')) {
            return $this->charges->isNotEmpty() && !$this->hasActiveCharge();
        }

        return $this->charges()->exists() && !$this->hasActiveCharge();
    }

    /**
     * Verifica se o cliente é novo (nunca teve empréstimo).
     */
    public function isNewClient(): bool
    {
        if ($this->relationLoaded('charges')) {
            return $this->charges->isEmpty();
        }

        return !$this->charges()->exists();
    }
}
