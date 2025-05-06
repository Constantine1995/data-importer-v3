<?php

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_service_token_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ApiService::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TokenType::class)->constrained()->cascadeOnDelete();
            $table->unique(['api_service_id', 'token_type_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_service_token_types');
    }
};
