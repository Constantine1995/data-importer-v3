<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeAccount extends Model
{
    protected $fillable = [
        'account_id',
        'income_id',
    ];

    public function income()
    {
        return $this->belongsTo(Income::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
