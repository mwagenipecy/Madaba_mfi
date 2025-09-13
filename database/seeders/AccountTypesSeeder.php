<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccountType;

class AccountTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountTypes = [
            // 5 Main Account Categories
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
            
            // Common Sub-account Types (for reference)
            [
                'name' => 'Cash',
                'code' => 'CASH',
                'description' => 'Physical cash and petty cash accounts (sub-account of Assets)',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Bank',
                'code' => 'BANK',
                'description' => 'Bank accounts and deposits (sub-account of Assets)',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Loan Portfolio',
                'code' => 'LOAN',
                'description' => 'Outstanding loan amounts and receivables (sub-account of Assets)',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 8,
            ],
            [
                'name' => 'Customer Deposits',
                'code' => 'DEPOSITS',
                'description' => 'Customer savings deposits (sub-account of Liability)',
                'category' => 'liability',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Investment',
                'code' => 'INV',
                'description' => 'Investment accounts and securities (sub-account of Assets)',
                'category' => 'asset',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'Interest Income',
                'code' => 'INT_INC',
                'description' => 'Interest earned on loans and investments (sub-account of Revenue)',
                'category' => 'income',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 11,
            ],
            [
                'name' => 'Operating Expenses',
                'code' => 'OP_EXP',
                'description' => 'General operating expenses (sub-account of Expense)',
                'category' => 'expense',
                'balance_type' => 'debit',
                'is_main_account' => false,
                'sort_order' => 12,
            ],
            [
                'name' => 'Retained Earnings',
                'code' => 'RETAINED',
                'description' => 'Accumulated profits (sub-account of Equity)',
                'category' => 'equity',
                'balance_type' => 'credit',
                'is_main_account' => false,
                'sort_order' => 13,
            ],
        ];

        foreach ($accountTypes as $accountType) {
            AccountType::firstOrCreate(
                ['code' => $accountType['code']],
                $accountType
            );
        }
    }
}
