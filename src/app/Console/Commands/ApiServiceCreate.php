<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use Illuminate\Database\QueryException;

class ApiServiceCreate extends BaseCommand
{
    protected $signature = 'api-service:create 
        {name : The name of the API service} 
        {description? : The description of the API service}';

    protected $description = 'Create new API service';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('The API service name cannot be empty or just whitespace.');
                    }
                },
            ],
            'description' => 'nullable|string|max:1000',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'name.required' => 'The API service name is required.',
            'name.string' => 'The API service name must be a string.',
            'name.max' => 'The API service name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
        ];
    }

    protected function prepareInput(array $arguments): array
    {
        return [
            'name' => preg_replace('/^name:/', '', $arguments['name']),
            'description' => isset($arguments['description'])
                ? preg_replace('/^description:/', '', $arguments['description'])
                : null,
        ];
    }

    protected function handleValidated(array $validated)
    {
        $name = trim($validated['name']);
        $description = isset($validated['description']) ? trim($validated['description']) : null;

        try {
            $service = ApiService::create([
                'name' => $name,
                'description' => $description,
            ]);
            $this->info("API service '{$name}' created with ID {$service->id}.");
            return 0;
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed') ||
                str_contains($e->getMessage(), 'Duplicate entry')) {
                $this->error("An API service with the name '{$name}' already exists.");
                return 1;
            }
            throw $e;
        }
    }
}