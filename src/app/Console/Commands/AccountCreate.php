<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;

class AccountCreate extends BaseCommand
{
    protected $signature = 'account:create 
        {company_name : The name or ID of company} 
        {name : The name for account}';

    protected $description = 'Create a new account for a company';

    protected function rules(): array
    {
        return [
            'company_name' => 'required',
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('The account name cannot be empty or just whitespace.');
                    }
                },
            ]
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'company_name.required' => 'Company name or ID is required.',
            'name.required' => 'Account name is required.',
            'name.string' => 'Account name must be a string.',
            'name.max' => 'Account name may not be greater than 255 characters.',
        ];
    }

    protected function prepareInput(array $arguments): array
    {
        return [
            'company_name' => preg_replace('/^company_name:/', '', $arguments['company_name']),
            'name' => preg_replace('/^name:/', '', $arguments['name']),
        ];
    }

    protected function handleValidated(array $validated)
    {
        $companyName = $validated['company_name'];
        $accountName = trim($validated['name']);

        $company = $this->findCompany($companyName);
        if (!$company) {
            $this->error(is_numeric($companyName)
                ? "Company with ID {$companyName} not found."
                : "Company with name '{$companyName}' not found.");
            return 1;
        }

        if (Account::where('company_id', $company->id)
            ->where('name', $accountName)
            ->exists()) {
            $this->error("Account '{$accountName}' already exists for company '{$company->name}'.");
            return 1;
        }

        $account = Account::create([
            'company_id' => $company->id,
            'name' => $accountName,
        ]);

        $this->info("Account '{$accountName}' (ID: {$account->id}) successfully created for company '{$company->name}' (ID: {$company->id}).");
        return 0;
    }

    protected function findCompany($identifier)
    {
        if (is_numeric($identifier)) {
            return Company::find($identifier);
        }

        return Company::where('name', 'like', '%' . $identifier . '%')->first();
    }
}