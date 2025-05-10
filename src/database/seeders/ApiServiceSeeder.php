<?php

namespace Database\Seeders;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Database\Seeder;

class ApiServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create token types
        $tokenTypes = ['api-key', 'bearer', 'login-password'];
        foreach ($tokenTypes as $type) {
            TokenType::firstOrCreate(['name' => $type]);
        }

        // Create services
        ApiService::factory()->createAllServices();

        // Create connections in api_service_token_types
        $tokenTypes = TokenType::all();
        ApiService::all()->each(function ($service) use ($tokenTypes) {
            $service->tokenTypes()->syncWithoutDetaching($tokenTypes->pluck('id')->toArray());
        });
    }
}