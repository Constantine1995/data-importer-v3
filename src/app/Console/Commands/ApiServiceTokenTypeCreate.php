<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Support\Facades\DB;

class ApiServiceTokenTypeCreate extends BaseCommand
{
    protected $signature = 'api-service-token-type:create 
        {api_service : The ID or name of the API service} 
        {token_type : The ID or name of the token type}';

    protected $description = 'Associate API service with token type';

    protected function rules(): array
    {
        return [
            'api_service' => 'required',
            'token_type' => 'required',
        ];
    }

    protected function prepareInput(array $arguments): array
    {
        return [
            'api_service' => preg_replace('/^api_service:/', '', $arguments['api_service']),
            'token_type' => preg_replace('/^token_type:/', '', $arguments['token_type']),
        ];
    }

    protected function handleValidated(array $validated)
    {
        $apiServiceInput = $validated['api_service'];
        $tokenTypeInput = $validated['token_type'];

        // Find API Service
        $apiService = is_numeric($apiServiceInput)
            ? ApiService::find($apiServiceInput)
            : ApiService::where('name', 'like', '%' . $apiServiceInput . '%')->first();

        if (!$apiService) {
            $this->error(is_numeric($apiServiceInput)
                ? "API service with ID {$apiServiceInput} not found."
                : "API service with name '{$apiServiceInput}' not found.");
            return 1;
        }

        // Find Token Type
        $tokenType = is_numeric($tokenTypeInput)
            ? TokenType::find($tokenTypeInput)
            : TokenType::where('name', 'like', '%' . $tokenTypeInput . '%')->first();

        if (!$tokenType) {
            $this->error(is_numeric($tokenTypeInput)
                ? "Token type with ID {$tokenTypeInput} not found."
                : "Token type with name '{$tokenTypeInput}' not found.");
            return 1;
        }

        // Check existing association
        $exists = DB::table('api_service_token_types')
            ->where('api_service_id', $apiService->id)
            ->where('token_type_id', $tokenType->id)
            ->exists();

        if ($exists) {
            $this->error("API service '{$apiService->name}' is already associated with token type '{$tokenType->name}'.");
            return 1;
        }

        // Create association
        DB::table('api_service_token_types')->insert([
            'api_service_id' => $apiService->id,
            'token_type_id' => $tokenType->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("API service '{$apiService->name}' successfully associated with token type '{$tokenType->name}'.");
        return 0;
    }
}