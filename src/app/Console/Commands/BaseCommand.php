<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseCommand extends Command
{
    /**
     * Validation rules for command arguments
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Custom validation error messages
     */
    protected function validationMessages(): array
    {
        return [];
    }

    /**
     * Transform arguments before validation
     */
    protected function prepareInput(array $arguments): array
    {
        return $arguments;
    }

    /**
     * Validate command arguments
     */
    protected function validateArguments(array $arguments): array
    {
        $preparedInput = $this->prepareInput($arguments);

        $validator = Validator::make(
            $preparedInput,
            $this->rules(),
            $this->validationMessages()
        );

        try {
            return $validator->validate();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $errors) {
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }
            throw $e;
        }
    }

    /**
     * Command processing with automatic validation
     */
    final public function handle()
    {
        try {
            $arguments = $this->arguments();
            $validated = $this->validateArguments($arguments);
            return $this->handleValidated($validated);
        } catch (ValidationException $e) {
            return 1;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Main command logic (must be implemented in child classes)
     */
    abstract protected function handleValidated(array $validatedArguments);
}