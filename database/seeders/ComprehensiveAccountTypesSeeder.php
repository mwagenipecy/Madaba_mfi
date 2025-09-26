<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountType;

class ComprehensiveAccountTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountTypes = [
            // Main Account Categories (5 fundamental categories)
            [
                'name' => 'Assets',
                'code' => 'ASSETS',
                'description' => 'Main Assets category - all asset accounts belong here',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Revenue',
                'code' => 'REVENUE',
                'description' => 'Main Revenue category - all income accounts belong here',
                'category' => 'income',
                'balance_type' => 'credit',
                'is_main_account' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Liability',
                'code' => 'LIABILITY',
                'description' => 'Main Liability category - all liability accounts belong here',
                'category' => 'liability',
                'balance_type' => 'credit',
                'is_main_account' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Equity',
                'code' => 'EQUITY',
                'description' => 'Main Equity category - all equity accounts belong here',
                'category' => 'equity',
                'balance_type' => 'credit',
                'is_main_account' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Expense',
                'code' => 'EXPENSE',
                'description' => 'Main Expense category - all expense accounts belong here',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => true,
                'sort_order' => 5,
            ],

            // Asset Sub-Accounts
            [
                'name' => 'Cash and Cash Equivalents',
                'code' => 'CASH',
                'description' => 'Physical cash, petty cash, and cash equivalents',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 101,
            ],
            [
                'name' => 'Bank Accounts',
                'code' => 'BANK',
                'description' => 'Bank deposits and checking accounts',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 102,
            ],
            [
                'name' => 'Loan Portfolio',
                'code' => 'LOAN_PORTFOLIO',
                'description' => 'Outstanding loan amounts and receivables',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 103,
            ],
            [
                'name' => 'Accounts Receivable',
                'code' => 'AR',
                'description' => 'Amounts owed by customers and clients',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 104,
            ],
            [
                'name' => 'Investments',
                'code' => 'INVESTMENTS',
                'description' => 'Investment accounts and securities',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 105,
            ],
            [
                'name' => 'Fixed Assets',
                'code' => 'FIXED_ASSETS',
                'description' => 'Equipment, furniture, vehicles, buildings',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 106,
            ],
            [
                'name' => 'Prepaid Expenses',
                'code' => 'PREPAID',
                'description' => 'Expenses paid in advance',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 107,
            ],

            // Liability Sub-Accounts
            [
                'name' => 'Customer Deposits',
                'code' => 'CUSTOMER_DEPOSITS',
                'description' => 'Customer savings deposits and balances',
                'category' => 'liability',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 201,
            ],
            [
                'name' => 'Accounts Payable',
                'code' => 'AP',
                'description' => 'Amounts owed to suppliers and vendors',
                'category' => 'liability',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 202,
            ],
            [
                'name' => 'Accrued Expenses',
                'code' => 'ACCRUED_EXP',
                'description' => 'Expenses incurred but not yet paid',
                'category' => 'liability',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 203,
            ],
            [
                'name' => 'Loan Payable',
                'code' => 'LOAN_PAYABLE',
                'description' => 'Borrowings and loans from external sources',
                'category' => 'liability',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 204,
            ],
            [
                'name' => 'Deferred Revenue',
                'code' => 'DEFERRED_REV',
                'description' => 'Revenue received but not yet earned',
                'category' => 'liability',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 205,
            ],

            // Equity Sub-Accounts
            [
                'name' => 'Owner Capital',
                'code' => 'OWNER_CAPITAL',
                'description' => 'Owner contributions and capital investments',
                'category' => 'equity',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 301,
            ],
            [
                'name' => 'Retained Earnings',
                'code' => 'RETAINED_EARNINGS',
                'description' => 'Accumulated profits from previous periods',
                'category' => 'equity',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 302,
            ],
            [
                'name' => 'Current Year Earnings',
                'code' => 'CURRENT_EARNINGS',
                'description' => 'Current year profit or loss',
                'category' => 'equity',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 303,
            ],

            // Income Sub-Accounts
            [
                'name' => 'Interest Income',
                'code' => 'INTEREST_INCOME',
                'description' => 'Interest earned on loans and investments',
                'category' => 'income',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 401,
            ],
            [
                'name' => 'Fee Income',
                'code' => 'FEE_INCOME',
                'description' => 'Processing fees, late fees, and other charges',
                'category' => 'income',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 402,
            ],
            [
                'name' => 'Investment Income',
                'code' => 'INVESTMENT_INCOME',
                'description' => 'Dividends and capital gains from investments',
                'category' => 'income',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 403,
            ],
            [
                'name' => 'Other Income',
                'code' => 'OTHER_INCOME',
                'description' => 'Miscellaneous income sources',
                'category' => 'income',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 404,
            ],

            // Expense Sub-Accounts
            [
                'name' => 'Operating Expenses',
                'code' => 'OP_EXPENSES',
                'description' => 'General operating expenses',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 501,
            ],
            [
                'name' => 'Personnel Expenses',
                'code' => 'PERSONNEL',
                'description' => 'Salaries, wages, and benefits',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 502,
            ],
            [
                'name' => 'Administrative Expenses',
                'code' => 'ADMIN_EXP',
                'description' => 'Administrative and overhead costs',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 503,
            ],
            [
                'name' => 'Bad Debt Expense',
                'code' => 'BAD_DEBT',
                'description' => 'Loan write-offs and bad debt provisions',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 504,
            ],
            [
                'name' => 'Interest Expense',
                'code' => 'INTEREST_EXP',
                'description' => 'Interest paid on borrowings',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 505,
            ],
            [
                'name' => 'Depreciation Expense',
                'code' => 'DEPRECIATION',
                'description' => 'Depreciation of fixed assets',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 506,
            ],
        ];

        foreach ($accountTypes as $accountType) {
            AccountType::firstOrCreate(
                ['code' => $accountType['code']],
                $accountType
            );
        }

        $this->command->info('Comprehensive account types seeded successfully.');
    }
}
