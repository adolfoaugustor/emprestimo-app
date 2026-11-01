<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentDueDateChange extends Model
{
    protected $fillable = [
        'installment_id',
        'old_due_date',
        'new_due_date',
        'changed_by_user_id',
    ];

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
