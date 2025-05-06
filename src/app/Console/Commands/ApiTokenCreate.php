<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\ApiToken;
use App\Models\TokenType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiTokenCreate extends BaseCommand
{
    protected $signature = 'api-token:create 
        {account_id : Account ID, format: id:1} 
        {api_service : API service name or ID, e.g. id:2 or api_service:Orders} 
        {token_type : Token type name or ID, e.g. id:3 or type_token:bearer} 
        {login? : Optional login for login-password token type}
        {password? : Optional password for login-password token type}
        {token? : Optional token string}';

    protected $description = 'Create an API token for an account';

    protected function rules(): array
    {
        return [
            'account_id' => 'required',
            'api_service' => 'required',
            'token_type' => 'required',
            'login' => 'nullable|string',
            'password' => 'nullable|string',
            'token' => 'nullable|string',
        ];
    }

    protected function prepareInput(array $arguments): array
    {
        return [
            'account_id' => $arguments['account_id'],
            'api_service' => preg_replace('/^(api_service|id):/', '', $arguments['api_service']),
            'token_type' => preg_replace('/^(type_token|id):/', '', $arguments['token_type']),
            'login' => isset($arguments['login']) ? preg_replace('/^login:/', '', $arguments['login']) : null,
            'password' => isset($arguments['password']) ? preg_replace('/^password:/', '', $arguments['password']) : null,
            'token' => isset($arguments['token']) ? preg_replace('/^token:/', '', $arguments['token']) : Str::random(60),
        ];
    }

    protected function handleValidated(array $validated)
    {
        // Parse account_id
        [$accountKey, $accountValue] = $this->parseKeyValue($validated['account_id']);
        $account = Account::where($accountKey, $accountValue)->first();

        if (!$account) {
            $this->error("Account not found by {$accountKey}: {$accountValue}");
            return 1;
        }

        // Find API Service
        $apiService = is_numeric($validated['api_service'])
            ? ApiService::find($validated['api_service'])
            : ApiService::where('name', $validated['api_service'])->first();

        if (!$apiService) {
            $this->error("API Service not found: {$validated['api_service']}");
            return 1;
        }

        // Find Token Type
        $tokenType = is_numeric($validated['token_type'])
            ? TokenType::find($validated['token_type'])
            : TokenType::where('name', $validated['token_type'])->first();

        if (!$tokenType) {
            $this->error("Token Type not found: {$validated['token_type']}");
            return 1;
        }

        // Handle 'login-password' token type
        if ($tokenType->name === 'login-password') {
            if (empty($validated['login']) || empty($validated['password'])) {
                $this->error("Login and password are required for the 'login-password' token type.");
                return 1;
            }
            $hash = trim("{$validated['login']}:{$validated['password']}");
            $validated['token'] = Hash::make($hash);
        }

        // Check token type support
        $isSupported = DB::table('api_service_token_types')
            ->where('api_service_id', $apiService->id)
            ->where('token_type_id', $tokenType->id)
            ->exists();

        if (!$isSupported) {
            $this->error("API Service '{$apiService->name}' does not support Token Type '{$tokenType->name}'.");
            return 1;
        }

        // Check for existing token
        $existingToken = ApiToken::where('account_id', $account->id)
            ->where('api_service_id', $apiService->id)
            ->where('token_type_id', $tokenType->id)
            ->first();

        if ($existingToken) {
            $this->error("A token already exists for this combination.");
            return 1;
        }

        // Create token
        $apiToken = ApiToken::create([
            'account_id' => $account->id,
            'api_service_id' => $apiService->id,
            'token_type_id' => $tokenType->id,
            'token' => $validated['token'],
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->info("Token created (ID: {$apiToken->id})");
        $this->line("Service: {$apiService->name}");
        $this->line("Type: {$tokenType->name}");
        $this->line("Token: {$validated['token']}");
        $this->line("Expires: in 30 days");

        return 0;
    }

    protected function parseKeyValue(string $input): array
    {
        $parts = explode(':', $input, 2);
        if (count($parts) !== 2) {
            $this->error("Invalid format: '{$input}'. Use key:value (e.g., id:1 or api_service:Orders)");
            exit(1);
        }
        return [$parts[0], $parts[1]];
    }
}