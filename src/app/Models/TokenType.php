<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class TokenType extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
    ];

    public function apiServices(): BelongsToMany
    {
        return $this->belongsToMany(ApiService::class, 'api_service_token_types');
    }
}
