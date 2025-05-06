<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'company_id',
        'name',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_accounts');
    }

    public function incomes()
    {
        return $this->belongsToMany(Income::class, 'income_accounts');
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'sale_accounts');
    }

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, 'stock_accounts');
    }
}
