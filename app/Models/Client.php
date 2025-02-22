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
}
