<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Organization;

class MainAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();

        if (!$organization) {
            $this->command->warn('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        // Get the 5 main category account types (Assets, Revenue, Liability, Equity, Expense)
        $mainAccountTypes = AccountType::where('is_main_account', true)->active()->get();

        foreach ($mainAccountTypes as $accountType) {
            $accountNumber = $this->generateAccountNumber($accountType->code, $organization->id);
            
            Account::firstOrCreate(
                [
                    'account_number' => $accountNumber,
                    'organization_id' => $organization->id,
                    'branch_id' => null, // Main account (no branch)
                ],
                [
                    'name' => $accountType->name,
                    'account_type_id' => $accountType->id,
                    'parent_account_id' => null, // These are main category accounts
                    'balance' => 0.00,
                    'opening_balance' => 0.00,
                    'currency' => 'TZS',
                    'description' => 'Main ' . $accountType->name . ' category for ' . $organization->name,
                    'status' => 'active',
                    'opening_date' => now(),
                    'metadata' => [
                        'account_type' => 'main_category',
                        'auto_created' => true,
                    ],
                ]
            );
        }

        $this->command->info('Main category accounts created successfully for organization: ' . $organization->name);
        $this->command->info('Created ' . $mainAccountTypes->count() . ' main category accounts:');
        foreach ($mainAccountTypes as $accountType) {
            $this->command->info('- ' . $accountType->name);
        }
    }

    /**
     * Generate a unique account number
     */
    private function generateAccountNumber(string $accountTypeCode, int $organizationId): string
    {
        $prefix = strtoupper($accountTypeCode);
        $orgCode = str_pad($organizationId, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $orgCode . '-' . $timestamp . '-' . $random;
    }
}
