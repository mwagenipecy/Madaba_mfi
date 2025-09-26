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

        // Get accounts for the organization using proper account types
        $customerDepositsAccount = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('code', 'CUSTOMER_DEPOSITS');
            })
            ->first();

        $cashAccount = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('code', 'CASH');
            })
            ->first();

        $interestIncomeAccount = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('code', 'INTEREST_INCOME');
            })
            ->first();

        $loanPortfolioAccount = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('code', 'LOAN_PORTFOLIO');
            })
            ->first();

        if (!$customerDepositsAccount || !$cashAccount || !$interestIncomeAccount || !$loanPortfolioAccount) {
            $this->command->error('Required accounts not found. Please run ComprehensiveAccountsSeeder first.');
            return;
        }

        // Update existing loan products with account configurations
        $loanProducts = LoanProduct::where('organization_id', $organizationId)->get();

        foreach ($loanProducts as $product) {
            // Assign proper accounts
            $disbursementAccount = $customerDepositsAccount; // Money comes from customer deposits (liability)
            $collectionAccount = $cashAccount; // Cash received (asset)
            $interestRevenueAccount = $interestIncomeAccount; // Interest income (revenue)
            $principalAccount = $loanPortfolioAccount; // Loan portfolio (asset)

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