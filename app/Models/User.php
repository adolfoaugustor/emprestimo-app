<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type', // Adiciona este campo
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    /**
     * Os clientes associados ao usuário.
     */
    public function clients()
    {
        return $this->belongsToMany(Client::class)->withTimestamps();
    }

    public function accountOwner()
    {
        return $this->belongsTo(self::class, 'account_owner_id');
    }

    public function subusers()
    {
        return $this->hasMany(self::class, 'account_owner_id');
    }

    public function subuserPermissions()
    {
        return $this->hasOne(SubuserPermission::class);
    }

    public function isAccountOwner(): bool
    {
        return $this->account_owner_id === null;
    }

    public function accountOwnerId(): int
    {
        return $this->account_owner_id ?? $this->id;
    }
}
