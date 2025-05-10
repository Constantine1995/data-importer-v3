<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\ApiToken;
use App\Models\TokenType;
use Illuminate\Database\Seeder;

class ApiTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all accounts, services and token types
        $accounts = Account::all();
        $services = ApiService::all();
        $tokenTypes = TokenType::all();

        // For each account we create tokens
        $accounts->each(function ($account) use ($services, $tokenTypes) {
            $services->each(function ($service) use ($account, $tokenTypes) {
                $tokenTypes->each(function ($tokenType) use ($account, $service) {

                    // Check if the combination of service and tokenType is valid
                    if ($service->tokenTypes()->where('token_type_id', $tokenType->id)->exists()) {

                        // Check if the token exists
                        $existingToken = ApiToken::where([
                            'account_id' => $account->id,
                            'api_service_id' => $service->id,
                            'token_type_id' => $tokenType->id,
                        ])->first();

                        if (!$existingToken) {
                            // Create a token
                            ApiToken::factory()
                                ->forAccount($account)
                                ->forService($service)
                                ->forTokenType($tokenType->name)
                                ->create();
                        }
                    }
                });
            });
        });
    }
}