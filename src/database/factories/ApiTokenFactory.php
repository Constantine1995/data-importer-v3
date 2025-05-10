<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\TokenType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiTokenFactory extends Factory
{
    protected $model = \App\Models\ApiToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::inRandomOrder()->first()->id ?? Account::factory(),
            'api_service_id' => ApiService::inRandomOrder()->first()->id ?? ApiService::factory(),
            'token_type_id' => TokenType::inRandomOrder()->first()->id ?? TokenType::factory(),
            'token' => $this->faker->unique()->uuid(),
            'expires_at' => Carbon::now()->addDays(30),
        ];
    }

    /**
     * Create a token
     */
    protected function generateTokenBasedOnType(string $type): string
    {
        return match ($type) {
            'api-key' => Str::random(64),
            'bearer' => Str::random(64),
            'login-password' => Hash::make($this->faker->userName . ':' . $this->faker->password),
            default => Hash::make(Str::random(32)),
        };
    }

    public function forTokenType(string $tokenTypeName): self
    {
        return $this->state(function (array $attributes) use ($tokenTypeName) {
            $tokenType = TokenType::firstOrCreate(['name' => $tokenTypeName]);
            return [
                'token_type_id' => $tokenType->id,
                'token' => $this->generateTokenBasedOnType($tokenTypeName),
            ];
        });
    }

    public function forAccount(Account $account): self
    {
        return $this->state([
            'account_id' => $account->id,
        ]);
    }

    public function forService(ApiService $service): self
    {
        return $this->state([
            'api_service_id' => $service->id,
        ]);
    }
}