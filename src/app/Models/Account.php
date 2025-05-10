<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Account extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_accounts');
    }

    public function incomes(): BelongsToMany
    {
        return $this->belongsToMany(Income::class, 'income_accounts');
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'sale_accounts');
    }

    public function stocks(): BelongsToMany
    {
        return $this->belongsToMany(Stock::class, 'stock_accounts');
    }
}
