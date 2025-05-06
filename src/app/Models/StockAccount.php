<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAccount extends Model
{
    protected $fillable = [
        'account_id',
        'stock_id',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
