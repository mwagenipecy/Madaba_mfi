<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;

class ClientLoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();
        $branches = Branch::all();
        $loanProducts = LoanProduct::all();
        $user = User::first();
        
        if (!$organization || $branches->isEmpty() || $loanProducts->isEmpty() || !$user) {
            $this->command->error('Required data not found. Please run other seeders first.');
            return;
        }

        $this->command->info('Creating sample clients and loans...');

        // Create individual clients
        $individualClients = [];
        for ($i = 1; $i <= 5; $i++) {
            $client = Client::create([
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'individual',
                'organization_id' => $organization->id,
                'branch_id' => $branches->random()->id,
                'first_name' => "John{$i}",
                'last_name' => "Doe{$i}",
                'middle_name' => 'M',
                'date_of_birth' => Carbon::now()->subYears(25 + $i),
                'gender' => $i % 2 === 0 ? 'male' : 'female',
                'national_id' => 'ID' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'phone_number' => '255' . (700000000 + $i),
                'email' => "john{$i}@example.com",
                'physical_address' => "Street {$i}, Dar es Salaam",
                'city' => 'Dar es Salaam',
                'region' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'monthly_income' => 500000 + ($i * 100000),
                'income_source' => 'Employment',
                'occupation' => 'Software Developer',
                'marital_status' => $i % 2 === 0 ? 'single' : 'married',
                'dependents' => $i % 2 === 0 ? 0 : 2,
                'kyc_status' => $i <= 3 ? 'verified' : 'pending',
                'status' => 'active',
            ]);
            $individualClients[] = $client;
        }

        // Create business clients
        $businessClients = [];
        for ($i = 1; $i <= 3; $i++) {
            $client = Client::create([
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'business',
                'organization_id' => $organization->id,
                'branch_id' => $branches->random()->id,
                'business_name' => "Business Corp {$i}",
                'business_type' => 'corporation',
                'business_registration_number' => 'REG' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'phone_number' => '255' . (800000000 + $i),
                'email' => "business{$i}@example.com",
                'physical_address' => "Business Street {$i}, Dar es Salaam",
                'city' => 'Dar es Salaam',
                'region' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'annual_turnover' => 10000000 + ($i * 5000000),
                'years_in_business' => 2 + $i,
                'business_description' => "A growing business in sector {$i}",
                'kyc_status' => $i <= 2 ? 'verified' : 'pending',
                'status' => 'active',
            ]);
            $businessClients[] = $client;
        }

        // Create loans for individual clients
        foreach ($individualClients as $index => $client) {
            $loanCount = rand(1, 3);
            for ($j = 1; $j <= $loanCount; $j++) {
                $loanProduct = $loanProducts->random();
                $loanAmount = rand(500000, 2000000);
                $interestRate = $loanProduct->interest_rate;
                $tenureMonths = rand(6, 24);
                
                $loan = Loan::create([
                    'loan_number' => Loan::generateLoanNumber(),
                    'client_id' => $client->id,
                    'loan_product_id' => $loanProduct->id,
                    'organization_id' => $organization->id,
                    'branch_id' => $client->branch_id,
                    'loan_officer_id' => $user->id,
                    'loan_amount' => $loanAmount,
                    'approved_amount' => $loanAmount,
                    'interest_rate' => $interestRate,
                    'interest_calculation_method' => $loanProduct->interest_calculation_method,
                    'loan_tenure_months' => $tenureMonths,
                    'repayment_frequency' => 'monthly',
                    'application_date' => Carbon::now()->subDays(rand(30, 365)),
                    'approval_date' => Carbon::now()->subDays(rand(20, 350)),
                    'disbursement_date' => Carbon::now()->subDays(rand(15, 340)),
                    'first_payment_date' => Carbon::now()->subDays(rand(10, 330)),
                    'maturity_date' => Carbon::now()->addMonths(rand(3, 18)),
                    'total_interest' => $loanAmount * ($interestRate / 100) * ($tenureMonths / 12),
                    'total_amount' => $loanAmount + ($loanAmount * ($interestRate / 100) * ($tenureMonths / 12)),
                    'monthly_payment' => ($loanAmount + ($loanAmount * ($interestRate / 100) * ($tenureMonths / 12))) / $tenureMonths,
                    'processing_fee' => $loanAmount * 0.02,
                    'status' => $this->getRandomLoanStatus(),
                    'approval_status' => 'approved',
                    'approved_by' => $user->id,
                    'paid_amount' => rand(0, $loanAmount),
                    'outstanding_balance' => $loanAmount - rand(0, $loanAmount),
                    'overdue_amount' => rand(0, 1) ? rand(50000, 200000) : 0,
                    'overdue_days' => rand(0, 1) ? rand(1, 30) : 0,
                ]);
            }
        }

        // Create loans for business clients
        foreach ($businessClients as $index => $client) {
            $loanCount = rand(1, 2);
            for ($j = 1; $j <= $loanCount; $j++) {
                $loanProduct = $loanProducts->random();
                $loanAmount = rand(2000000, 10000000);
                $interestRate = $loanProduct->interest_rate;
                $tenureMonths = rand(12, 36);
                
                $loan = Loan::create([
                    'loan_number' => Loan::generateLoanNumber(),
                    'client_id' => $client->id,
                    'loan_product_id' => $loanProduct->id,
                    'organization_id' => $organization->id,
                    'branch_id' => $client->branch_id,
                    'loan_officer_id' => $user->id,
                    'loan_amount' => $loanAmount,
                    'approved_amount' => $loanAmount,
                    'interest_rate' => $interestRate,
                    'interest_calculation_method' => $loanProduct->interest_calculation_method,
                    'loan_tenure_months' => $tenureMonths,
                    'repayment_frequency' => 'monthly',
                    'application_date' => Carbon::now()->subDays(rand(30, 365)),
                    'approval_date' => Carbon::now()->subDays(rand(20, 350)),
                    'disbursement_date' => Carbon::now()->subDays(rand(15, 340)),
                    'first_payment_date' => Carbon::now()->subDays(rand(10, 330)),
                    'maturity_date' => Carbon::now()->addMonths(rand(6, 24)),
                    'total_interest' => $loanAmount * ($interestRate / 100) * ($tenureMonths / 12),
                    'total_amount' => $loanAmount + ($loanAmount * ($interestRate / 100) * ($tenureMonths / 12)),
                    'monthly_payment' => ($loanAmount + ($loanAmount * ($interestRate / 100) * ($tenureMonths / 12))) / $tenureMonths,
                    'processing_fee' => $loanAmount * 0.015,
                    'status' => $this->getRandomLoanStatus(),
                    'approval_status' => 'approved',
                    'approved_by' => $user->id,
                    'paid_amount' => rand(0, $loanAmount),
                    'outstanding_balance' => $loanAmount - rand(0, $loanAmount),
                    'overdue_amount' => rand(0, 1) ? rand(100000, 500000) : 0,
                    'overdue_days' => rand(0, 1) ? rand(1, 45) : 0,
                ]);
            }
        }

        $this->command->info('Sample clients and loans created successfully!');
        $this->command->info('Individual clients: ' . count($individualClients));
        $this->command->info('Business clients: ' . count($businessClients));
        $this->command->info('Total loans: ' . Loan::count());
    }

    private function getRandomLoanStatus()
    {
        $statuses = ['pending', 'under_review', 'approved', 'rejected', 'disbursed', 'active', 'overdue', 'completed', 'written_off', 'cancelled'];
        $weights = [5, 5, 10, 5, 15, 40, 10, 5, 3, 2]; // Weighted random selection
        
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($statuses as $index => $status) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $status;
            }
        }
        
        return 'active'; // Fallback
    }
}