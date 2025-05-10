<?php

namespace Database\Seeders;

use App\Models\TokenType;
use Illuminate\Database\Seeder;

class TokenTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['api-key', 'bearer', 'login-password'];

        foreach ($types as $name) {
            TokenType::updateOrCreate(
                ['name' => $name],
            );
        }
    }
}
