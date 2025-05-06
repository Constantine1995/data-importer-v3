<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAccount extends Model
{
    protected $fillable = [
        'account_id',
        'order_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
