<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\Account;
use App\Models\RealAccount;
use App\Models\AccountType;

class MainEntryAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create organizations
        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->info('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        foreach ($organizations as $organization) {
            // Get HQ branch
            $hqBranch = $organization->branches()->where('is_hq', true)->first();
            if (!$hqBranch) {
                $this->command->info("No HQ branch found for organization: {$organization->name}");
                continue;
            }

            // Get or create a bank account type
            $bankAccountType = AccountType::where('name', 'LIKE', '%Bank%')->first();
            if (!$bankAccountType) {
                $bankAccountType = AccountType::create([
                    'name' => 'Bank Account',
                    'description' => 'Bank account for cash management',
                    'category' => 'Asset',
                    'is_active' => true,
                ]);
            }

            // Create a main bank account for HQ branch
            $mainBankAccount = Account::create([
                'name' => $organization->name . ' - Main Bank Account',
                'account_number' => 'BNK' . $organization->id . 'MAIN' . time(),
                'account_type_id' => $bankAccountType->id,
                'organization_id' => $organization->id,
                'branch_id' => $hqBranch->id,
                'balance' => 500000.00, // Sample balance
                'opening_balance' => 500000.00,
                'currency' => 'TZS',
                'description' => 'Main bank account for ' . $organization->name,
                'status' => 'active',
                'opening_date' => now(),
                'mapping_description' => 'This account is mapped to the organization\'s primary bank account for cash management and transaction processing.',
            ]);

            // Create a real account to map to
            $realAccount = RealAccount::create([
                'account_id' => $mainBankAccount->id,
                'provider_type' => 'bank',
                'provider_name' => 'Standard Bank',
                'external_account_id' => 'SB' . $organization->id . 'MAIN',
                'external_account_name' => $organization->name . ' Main Account',
                'last_balance' => 500000.00,
                'last_sync_at' => now(),
                'sync_status' => 'success',
                'is_active' => true,
            ]);

            // Link the account to the real account
            $mainBankAccount->update([
                'real_account_id' => $realAccount->id,
            ]);

            // Create mapped accounts for other branches
            $otherBranches = $organization->branches()->where('is_hq', false)->get();
            
            foreach ($otherBranches as $branch) {
                $branchBankAccount = Account::create([
                    'name' => $branch->name . ' - Bank Account',
                    'account_number' => 'BNK' . $organization->id . 'BR' . $branch->id . time(),
                    'account_type_id' => $bankAccountType->id,
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'balance' => rand(50000, 200000), // Random balance between 50k and 200k
                    'opening_balance' => 100000.00,
                    'currency' => 'TZS',
                    'description' => 'Bank account for ' . $branch->name . ' branch',
                    'status' => 'active',
                    'opening_date' => now(),
                    'mapping_description' => 'This account is mapped to the branch\'s bank account for local operations.',
                ]);

                // Create a real account for the branch
                $branchRealAccount = RealAccount::create([
                    'account_id' => $branchBankAccount->id,
                    'provider_type' => 'bank',
                    'provider_name' => 'Standard Bank',
                    'external_account_id' => 'SB' . $organization->id . 'BR' . $branch->id,
                    'external_account_name' => $branch->name . ' Branch Account',
                    'last_balance' => $branchBankAccount->balance,
                    'last_sync_at' => now()->subHours(rand(1, 24)), // Random sync time within last 24 hours
                    'sync_status' => 'success',
                    'is_active' => true,
                ]);

                // Link the branch account to the real account
                $branchBankAccount->update([
                    'real_account_id' => $branchRealAccount->id,
                ]);
            }

            $this->command->info("Created mapped accounts for organization: {$organization->name}");
        }

        $this->command->info('Mapped accounts seeded successfully!');
    }
}