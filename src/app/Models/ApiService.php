<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class ApiService extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'description',
    ];

    public function tokenTypes(): BelongsToMany
    {
        return $this->belongsToMany(TokenType::class, 'api_service_token_types');
    }
}
