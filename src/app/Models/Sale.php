<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sale extends Model
{
    protected $casts = [
        'date' => 'date',
        'last_change_date' => 'date',
        'is_supply' => 'boolean',
        'is_realization' => 'boolean',
        'is_storno' => 'boolean',
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'sale_accounts');
    }
}
