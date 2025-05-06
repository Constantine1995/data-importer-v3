<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
        'date' => 'datetime',
        'last_change_date' => 'date',
        'cancel_dt' => 'date',
        'is_cancel' => 'boolean',
    ];

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'order_accounts');
    }

}
