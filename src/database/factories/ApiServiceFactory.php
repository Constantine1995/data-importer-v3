<?php

namespace Database\Factories;

use App\Models\ApiService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiService>
 */
class ApiServiceFactory extends Factory
{
    protected $model = ApiService::class;

    protected static array $services = [
        'Orders',
        'Incomes',
        'Sales',
        'Stocks'
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(self::$services),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Generates a description for a specific service
     */
    protected function generateDescriptionForService(string $service): string
    {
        return match ($service) {
            'Orders' => 'API for managing customer orders',
            'Incomes' => 'API for income tracking and reporting',
            'Sales' => 'API for sales data and analytics',
            'Stocks' => 'API for stock inventory management',
            default => 'General API service',
        };
    }

    /**
     * Create all api services
     */
    public static function createAllServices(): void
    {
        foreach (self::$services as $service) {
            ApiService::firstOrCreate(
                ['name' => $service],
                ['description' => (new self())->generateDescriptionForService($service)]
            );
        }
    }

    /**
     * Create a specific service
     */
    public function forService(string $service): self
    {
        return $this->state([
            'name' => $service,
            'description' => $this->generateDescriptionForService($service),
        ]);
    }

}