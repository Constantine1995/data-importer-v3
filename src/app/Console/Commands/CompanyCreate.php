<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Database\QueryException;

class CompanyCreate extends BaseCommand
{
    protected $signature = 'company:create {name : The name of the company}';
    protected $description = 'Create a new company';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('The company name cannot be empty or just whitespace.');
                    }
                },
            ],
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'name.required' => 'The company name is required.',
            'name.string' => 'The company name must be a string.',
            'name.max' => 'The company name may not be greater than 255 characters.',
        ];
    }

    protected function prepareInput(array $arguments): array
    {
        return [
            'name' => preg_replace('/^name:/', '', $arguments['name']),
        ];
    }

    protected function handleValidated(array $validated)
    {
        $name = trim($validated['name']);

        try {
            Company::create(['name' => $name]);
            $this->info("Company '$name' successfully created.");
            return 0;
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed') ||
                str_contains($e->getMessage(), 'Duplicate entry')) {
                $this->error("A company with the name '$name' already exists.");
                return 1;
            }
            throw $e;
        }
    }
}