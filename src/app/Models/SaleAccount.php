<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleAccount extends Model
{
    protected $fillable = [
        'account_id',
        'sale_id',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
