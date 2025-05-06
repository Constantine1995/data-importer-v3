<?php

use App\Models\Account;
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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Account::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ApiService::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TokenType::class)->constrained()->cascadeOnDelete();
            $table->text('token');
            $table->unique(['account_id', 'api_service_id', 'token_type_id']);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
