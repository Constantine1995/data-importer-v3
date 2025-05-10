<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeAccount extends Model
{
    protected $fillable = [
        'account_id',
        'income_id',
    ];

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
