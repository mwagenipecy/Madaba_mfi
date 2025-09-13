<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoanProduct;
use App\Models\Account;
use App\Models\Organization;

class LoanProductAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizationId = Organization::first()?->id;
        if (!$organizationId) {
            $this->command->error('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        // Get accounts for the organization
        $liabilityAccounts = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('name', 'Liability');
            })
            ->get();

        $assetAccounts = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('name', 'Assets');
            })
            ->get();

        $revenueAccounts = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('name', 'Revenue');
            })
            ->get();

        if ($liabilityAccounts->isEmpty() || $assetAccounts->isEmpty() || $revenueAccounts->isEmpty()) {
            $this->command->error('Required accounts not found. Please run MainAccountsSeeder first.');
            return;
        }

        // Update existing loan products with account configurations
        $loanProducts = LoanProduct::where('organization_id', $organizationId)->get();

        foreach ($loanProducts as $product) {
            // Assign accounts based on product type
            $disbursementAccount = $liabilityAccounts->where('name', 'like', '%Customer Deposits%')->first() 
                ?? $liabilityAccounts->first();
            
            $collectionAccount = $assetAccounts->where('name', 'like', '%Cash%')->first() 
                ?? $assetAccounts->first();
            
            $interestRevenueAccount = $revenueAccounts->where('name', 'like', '%Interest%')->first() 
                ?? $revenueAccounts->first();
            
            $principalAccount = $assetAccounts->where('name', 'like', '%Loan Portfolio%')->first() 
                ?? $assetAccounts->first();

            $product->update([
                'disbursement_account_id' => $disbursementAccount->id,
                'collection_account_id' => $collectionAccount->id,
                'interest_revenue_account_id' => $interestRevenueAccount->id,
                'principal_account_id' => $principalAccount->id,
            ]);

            $this->command->info("Updated loan product: {$product->name}");
        }

        $this->command->info('Loan product accounts configured successfully.');
    }
}