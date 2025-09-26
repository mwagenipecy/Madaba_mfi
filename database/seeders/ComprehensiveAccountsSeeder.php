<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Organization;

class ComprehensiveAccountsSeeder extends Seeder
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

        // Get account types
        $accountTypes = AccountType::all()->keyBy('code');

        // Create main category accounts first
        $this->createMainCategoryAccounts($organization, $accountTypes);

        // Create asset sub-accounts
        $this->createAssetAccounts($organization, $accountTypes);

        // Create liability sub-accounts
        $this->createLiabilityAccounts($organization, $accountTypes);

        // Create equity sub-accounts
        $this->createEquityAccounts($organization, $accountTypes);

        // Create income sub-accounts
        $this->createIncomeAccounts($organization, $accountTypes);

        // Create expense sub-accounts
        $this->createExpenseAccounts($organization, $accountTypes);

        $this->command->info('Comprehensive accounts created successfully for organization: ' . $organization->name);
    }

    private function createMainCategoryAccounts($organization, $accountTypes)
    {
        $mainCategories = ['ASSETS', 'REVENUE', 'LIABILITY', 'EQUITY', 'EXPENSE'];

        foreach ($mainCategories as $category) {
            if (isset($accountTypes[$category])) {
                Account::firstOrCreate(
                    [
                        'account_number' => $this->generateAccountNumber($category, $organization->id),
                        'organization_id' => $organization->id,
                        'branch_id' => null,
                    ],
                    [
                        'name' => $accountTypes[$category]->name,
                        'account_type_id' => $accountTypes[$category]->id,
                        'parent_account_id' => null,
                        'balance' => 0.00,
                        'opening_balance' => 0.00,
                        'currency' => 'TZS',
                        'description' => 'Main ' . $accountTypes[$category]->name . ' category for ' . $organization->name,
                        'status' => 'active',
                        'opening_date' => now(),
                        'metadata' => [
                            'account_type' => 'main_category',
                            'auto_created' => true,
                        ],
                    ]
                );
            }
        }
    }

    private function createAssetAccounts($organization, $accountTypes)
    {
        $assetsParent = Account::where('organization_id', $organization->id)
            ->where('name', 'Assets')
            ->first();

        if (!$assetsParent) return;

        $assetSubAccounts = [
            'CASH' => 'Cash and Cash Equivalents',
            'BANK' => 'Bank Accounts',
            'LOAN_PORTFOLIO' => 'Loan Portfolio',
            'AR' => 'Accounts Receivable',
            'INVESTMENTS' => 'Investments',
            'FIXED_ASSETS' => 'Fixed Assets',
            'PREPAID' => 'Prepaid Expenses',
        ];

        foreach ($assetSubAccounts as $code => $name) {
            if (isset($accountTypes[$code])) {
                Account::firstOrCreate(
                    [
                        'account_number' => $this->generateAccountNumber($code, $organization->id),
                        'organization_id' => $organization->id,
                    ],
                    [
                        'name' => $name,
                        'account_type_id' => $accountTypes[$code]->id,
                        'parent_account_id' => $assetsParent->id,
                        'balance' => 0.00,
                        'opening_balance' => 0.00,
                        'currency' => 'TZS',
                        'description' => $accountTypes[$code]->description,
                        'status' => 'active',
                        'opening_date' => now(),
                        'metadata' => [
                            'account_type' => 'sub_account',
                            'parent_category' => 'asset',
                            'auto_created' => true,
                        ],
                    ]
                );
            }
        }
    }

    private function createLiabilityAccounts($organization, $accountTypes)
    {
        $liabilityParent = Account::where('organization_id', $organization->id)
            ->where('name', 'Liability')
            ->first();

        if (!$liabilityParent) return;

        $liabilitySubAccounts = [
            'CUSTOMER_DEPOSITS' => 'Customer Deposits',
            'AP' => 'Accounts Payable',
            'ACCRUED_EXP' => 'Accrued Expenses',
            'LOAN_PAYABLE' => 'Loan Payable',
            'DEFERRED_REV' => 'Deferred Revenue',
        ];

        foreach ($liabilitySubAccounts as $code => $name) {
            if (isset($accountTypes[$code])) {
                Account::firstOrCreate(
                    [
                        'account_number' => $this->generateAccountNumber($code, $organization->id),
                        'organization_id' => $organization->id,
                    ],
                    [
                        'name' => $name,
                        'account_type_id' => $accountTypes[$code]->id,
                        'parent_account_id' => $liabilityParent->id,
                        'balance' => 0.00,
                        'opening_balance' => 0.00,
                        'currency' => 'TZS',
                        'description' => $accountTypes[$code]->description,
                        'status' => 'active',
                        'opening_date' => now(),
                        'metadata' => [
                            'account_type' => 'sub_account',
                            'parent_category' => 'liability',
                            'auto_created' => true,
                        ],
                    ]
                );
            }
        }
    }

    private function createEquityAccounts($organization, $accountTypes)
    {
        $equityParent = Account::where('organization_id', $organization->id)
            ->where('name', 'Equity')
            ->first();

        if (!$equityParent) return;

        $equitySubAccounts = [
            'OWNER_CAPITAL' => 'Owner Capital',
            'RETAINED_EARNINGS' => 'Retained Earnings',
            'CURRENT_EARNINGS' => 'Current Year Earnings',
        ];

        foreach ($equitySubAccounts as $code => $name) {
            if (isset($accountTypes[$code])) {
                Account::firstOrCreate(
                    [
                        'account_number' => $this->generateAccountNumber($code, $organization->id),
                        'organization_id' => $organization->id,
                    ],
                    [
                        'name' => $name,
                        'account_type_id' => $accountTypes[$code]->id,
                        'parent_account_id' => $equityParent->id,
                        'balance' => 0.00,
                        'opening_balance' => 0.00,
                        'currency' => 'TZS',
                        'description' => $accountTypes[$code]->description,
                        'status' => 'active',
                        'opening_date' => now(),
                        'metadata' => [
                            'account_type' => 'sub_account',
                            'parent_category' => 'equity',
                            'auto_created' => true,
                        ],
                    ]
                );
            }
        }
    }

    private function createIncomeAccounts($organization, $accountTypes)
    {
        $incomeParent = Account::where('organization_id', $organization->id)
            ->where('name', 'Revenue')
            ->first();

        if (!$incomeParent) return;

        $incomeSubAccounts = [
            'INTEREST_INCOME' => 'Interest Income',
            'FEE_INCOME' => 'Fee Income',
            'INVESTMENT_INCOME' => 'Investment Income',
            'OTHER_INCOME' => 'Other Income',
        ];

        foreach ($incomeSubAccounts as $code => $name) {
            if (isset($accountTypes[$code])) {
                Account::firstOrCreate(
                    [
                        'account_number' => $this->generateAccountNumber($code, $organization->id),
                        'organization_id' => $organization->id,
                    ],
                    [
                        'name' => $name,
                        'account_type_id' => $accountTypes[$code]->id,
                        'parent_account_id' => $incomeParent->id,
                        'balance' => 0.00,
                        'opening_balance' => 0.00,
                        'currency' => 'TZS',
                        'description' => $accountTypes[$code]->description,
                        'status' => 'active',
                        'opening_date' => now(),
                        'metadata' => [
                            'account_type' => 'sub_account',
                            'parent_category' => 'income',
                            'auto_created' => true,
                        ],
                    ]
                );
            }
        }
    }

    private function createExpenseAccounts($organization, $accountTypes)
    {
        $expenseParent = Account::where('organization_id', $organization->id)
            ->where('name', 'Expense')
            ->first();

        if (!$expenseParent) return;

        $expenseSubAccounts = [
            'OP_EXPENSES' => 'Operating Expenses',
            'PERSONNEL' => 'Personnel Expenses',
            'ADMIN_EXP' => 'Administrative Expenses',
            'BAD_DEBT' => 'Bad Debt Expense',
            'INTEREST_EXP' => 'Interest Expense',
            'DEPRECIATION' => 'Depreciation Expense',
        ];

        foreach ($expenseSubAccounts as $code => $name) {
            if (isset($accountTypes[$code])) {
                Account::firstOrCreate(
                    [
                        'account_number' => $this->generateAccountNumber($code, $organization->id),
                        'organization_id' => $organization->id,
                    ],
                    [
                        'name' => $name,
                        'account_type_id' => $accountTypes[$code]->id,
                        'parent_account_id' => $expenseParent->id,
                        'balance' => 0.00,
                        'opening_balance' => 0.00,
                        'currency' => 'TZS',
                        'description' => $accountTypes[$code]->description,
                        'status' => 'active',
                        'opening_date' => now(),
                        'metadata' => [
                            'account_type' => 'sub_account',
                            'parent_category' => 'expense',
                            'auto_created' => true,
                        ],
                    ]
                );
            }
        }
    }

    private function generateAccountNumber(string $accountTypeCode, int $organizationId): string
    {
        $prefix = strtoupper($accountTypeCode);
        $orgCode = str_pad($organizationId, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $orgCode . '-' . $timestamp . '-' . $random;
    }
}
