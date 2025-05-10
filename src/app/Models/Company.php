<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Company extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
