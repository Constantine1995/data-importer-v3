<?php

namespace App\Console\Commands;

use App\Models\TokenType;
use Illuminate\Database\QueryException;

class TokenTypeCreate extends BaseCommand
{
    protected $signature = 'token-type:create {name : The name of the token type}';
    protected $description = 'Create a new token type';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('The token type name cannot be empty or just whitespace.');
                    }
                },
            ],
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'name.required' => 'The token type name is required.',
            'name.string' => 'The token type name must be a string.',
            'name.max' => 'The token type name may not be greater than 255 characters.',
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
            $type = TokenType::create(['name' => $name]);
            $this->info("Token type '$name' created with ID $type->id.");
            return 0;
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed') ||
                str_contains($e->getMessage(), 'Duplicate entry')) {
                $this->error("A token type with the name '$name' already exists.");
                return 1;
            }
            throw $e;
        }
    }
}