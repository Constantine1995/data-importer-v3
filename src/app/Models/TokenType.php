<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenType extends Model
{
    protected $fillable = [
        'name',
    ];

    public function apiServices()
    {
        return $this->belongsToMany(ApiService::class, 'api_service_token_types');
    }
}
