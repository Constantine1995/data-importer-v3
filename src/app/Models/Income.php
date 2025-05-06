<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $casts = [
        'date' => 'date',
        'last_change_date' => 'date',
        'date_close' => 'date',
    ];

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'income_accounts');
    }
}
