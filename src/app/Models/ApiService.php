<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiService extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function tokenTypes()
    {
        return $this->belongsToMany(TokenType::class, 'api_service_token_types');
    }
}
