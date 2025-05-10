<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Income extends Model
{
    protected $casts = [
        'date' => 'date',
        'last_change_date' => 'date',
        'date_close' => 'date',
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'income_accounts');
    }
}
