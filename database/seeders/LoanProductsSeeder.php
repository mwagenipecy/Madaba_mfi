<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LoanProduct;
use App\Models\Organization;

class LoanProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();
        
        if (!$organization) {
            $this->command->error('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        $loanProducts = [
            [
                'name' => 'Personal Loan',
                'code' => 'PERS001',
                'description' => 'Quick personal loans for immediate financial needs',
                'min_amount' => 500000.00,
                'max_amount' => 25000000.00,
                'interest_rate' => 12.50,
                'interest_type' => 'fixed',
                'interest_calculation_method' => 'reducing',
                'min_tenure_months' => 6,
                'max_tenure_months' => 36,
                'processing_fee' => 25000.00,
                'late_fee' => 12500.00,
                'repayment_frequency' => 'monthly',
                'grace_period_days' => 7,
                'requires_collateral' => false,
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Business Loan',
                'code' => 'BUSI001',
                'description' => 'Working capital and business expansion loans',
                'min_amount' => 2500000.00,
                'max_amount' => 100000000.00,
                'interest_rate' => 10.75,
                'interest_type' => 'variable',
                'interest_calculation_method' => 'reducing',
                'min_tenure_months' => 12,
                'max_tenure_months' => 60,
                'processing_fee' => 100000.00,
                'late_fee' => 25000.00,
                'repayment_frequency' => 'monthly',
                'grace_period_days' => 14,
                'requires_collateral' => true,
                'collateral_ratio' => 150.00,
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Emergency Loan',
                'code' => 'EMRG001',
                'description' => 'Fast emergency loans with minimal documentation',
                'min_amount' => 250000.00,
                'max_amount' => 5000000.00,
                'interest_rate' => 15.00,
                'interest_type' => 'fixed',
                'interest_calculation_method' => 'flat',
                'min_tenure_months' => 3,
                'max_tenure_months' => 12,
                'processing_fee' => 12500.00,
                'late_fee' => 7500.00,
                'repayment_frequency' => 'weekly',
                'grace_period_days' => 3,
                'requires_collateral' => false,
                'status' => 'active',
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Agricultural Loan',
                'code' => 'AGRI001',
                'description' => 'Seasonal loans for farming and agricultural activities',
                'min_amount' => 1000000.00,
                'max_amount' => 50000000.00,
                'interest_rate' => 8.50,
                'interest_type' => 'fixed',
                'interest_calculation_method' => 'reducing',
                'min_tenure_months' => 6,
                'max_tenure_months' => 24,
                'processing_fee' => 50000.00,
                'late_fee' => 15000.00,
                'repayment_frequency' => 'quarterly',
                'grace_period_days' => 30,
                'requires_collateral' => true,
                'collateral_ratio' => 125.00,
                'status' => 'active',
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Education Loan',
                'code' => 'EDUC001',
                'description' => 'Student loans for educational expenses',
                'min_amount' => 500000.00,
                'max_amount' => 37500000.00,
                'interest_rate' => 9.25,
                'interest_type' => 'fixed',
                'interest_calculation_method' => 'reducing',
                'min_tenure_months' => 12,
                'max_tenure_months' => 84,
                'processing_fee' => 37500.00,
                'late_fee' => 10000.00,
                'repayment_frequency' => 'monthly',
                'grace_period_days' => 90,
                'requires_collateral' => false,
                'status' => 'active',
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Home Improvement Loan',
                'code' => 'HOME001',
                'description' => 'Loans for home renovation and improvement projects',
                'min_amount' => 1500000.00,
                'max_amount' => 75000000.00,
                'interest_rate' => 11.00,
                'interest_type' => 'fixed',
                'interest_calculation_method' => 'reducing',
                'min_tenure_months' => 12,
                'max_tenure_months' => 48,
                'processing_fee' => 75000.00,
                'late_fee' => 20000.00,
                'repayment_frequency' => 'monthly',
                'grace_period_days' => 14,
                'requires_collateral' => true,
                'collateral_ratio' => 110.00,
                'status' => 'inactive',
                'is_featured' => false,
                'sort_order' => 6,
            ]
        ];

        foreach ($loanProducts as $productData) {
            LoanProduct::firstOrCreate(
                [
                    'code' => $productData['code'],
                    'organization_id' => $organization->id,
                ],
                array_merge($productData, [
                    'organization_id' => $organization->id,
                    'eligibility_criteria' => [
                        'minimum_age' => 18,
                        'maximum_age' => 65,
                        'minimum_income' => 1500,
                        'credit_score_required' => true,
                        'employment_verification' => true,
                    ],
                    'required_documents' => [
                        'national_id',
                        'proof_of_income',
                        'bank_statements',
                        'employment_letter',
                    ],
                ])
            );
        }

        $this->command->info('Sample loan products created successfully for organization: ' . $organization->name);
        $this->command->info('Created ' . count($loanProducts) . ' loan products:');
        foreach ($loanProducts as $product) {
            $this->command->info('- ' . $product['name'] . ' (' . $product['code'] . ')');
        }
    }
}