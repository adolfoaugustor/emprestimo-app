<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Installment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AccountContext
{
    private const PERMISSIONS = [
        'create_clients' => 'create_clients',
        'create_charges' => 'create_charges',
        'record_payments' => 'record_payments',
        'change_due_dates' => 'change_due_dates',
    ];

    public function owner(User $user): User
    {
        return $user->accountOwner ?? $user;
    }

    public function ownerId(User $user): int
    {
        return (int) ($user->account_owner_id ?? $user->id);
    }

    public function isOwner(User $user): bool
    {
        return $user->account_owner_id === null;
    }

    public function canPerform(User $user, string $permission): bool
    {
        if (!$user->is_active || !isset(self::PERMISSIONS[$permission])) {
            return false;
        }

        if ($this->isOwner($user)) {
            return true;
        }

        $permissions = $user->subuserPermissions;

        return $permissions !== null && (bool) $permissions->{self::PERMISSIONS[$permission]};
    }

    public function clientsFor(User $user): Builder
    {
        $ownerId = $this->ownerId($user);

        return Client::query()->where(function (Builder $query) use ($ownerId) {
            $query->where('account_owner_id', $ownerId)
                ->orWhere(function (Builder $legacy) use ($ownerId) {
                    $legacy->whereNull('account_owner_id')
                        ->whereHas('users', fn ($users) => $users->whereKey($ownerId))
                        ->whereDoesntHave('users', function ($users) use ($ownerId) {
                            $users->where('users.id', '<>', $ownerId)
                                ->whereNull('users.account_owner_id');
                        });
                });
        });
    }

    public function ownsClient(User $user, int $clientId): bool
    {
        return $this->clientsFor($user)->whereKey($clientId)->exists();
    }

    public function ownsInstallment(User $user, Installment $installment): bool
    {
        return $this->clientsFor($user)
            ->whereHas('charges.installments', fn (Builder $query) => $query->whereKey($installment->id))
            ->exists();
    }
}
