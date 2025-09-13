<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\User;

class ClientsSeeder extends Seeder
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

        $branch = Branch::first();
        $adminUser = User::where('role', 'admin')->first();

        $clients = [
            // Individual Clients
            [
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'individual',
                'organization_id' => $organization->id,
                'branch_id' => $branch?->id,
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Mwangi',
                'date_of_birth' => '1985-03-15',
                'gender' => 'male',
                'national_id' => '1234567890123',
                'phone_number' => '+255712345678',
                'secondary_phone' => '+255755123456',
                'email' => 'john.mwangi@email.com',
                'physical_address' => 'Plot 123, Kariakoo, Dar es Salaam',
                'city' => 'Dar es Salaam',
                'region' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'postal_code' => '11000',
                'monthly_income' => 1500000.00,
                'income_source' => 'Employment',
                'employer_name' => 'Tanzania Revenue Authority',
                'employment_address' => 'Kivukoni, Dar es Salaam',
                'bank_name' => 'CRDB Bank',
                'bank_account_number' => '0151234567890',
                'emergency_contact_name' => 'Mary Mwangi',
                'emergency_contact_phone' => '+255713456789',
                'emergency_contact_relationship' => 'Spouse',
                'marital_status' => 'married',
                'dependents' => 3,
                'occupation' => 'Tax Officer',
                'kyc_status' => 'verified',
                'kyc_verification_date' => now()->subDays(30),
                'verified_by' => $adminUser?->id,
                'status' => 'active',
                'notes' => 'Regular salary earner with good credit history',
            ],
            [
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'individual',
                'organization_id' => $organization->id,
                'branch_id' => $branch?->id,
                'first_name' => 'Fatuma',
                'middle_name' => 'Hassan',
                'last_name' => 'Ali',
                'date_of_birth' => '1992-07-22',
                'gender' => 'female',
                'national_id' => '9876543210987',
                'phone_number' => '+255765432109',
                'email' => 'fatuma.ali@email.com',
                'physical_address' => 'House 45, Mbagala, Dar es Salaam',
                'city' => 'Dar es Salaam',
                'region' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'monthly_income' => 850000.00,
                'income_source' => 'Business',
                'bank_name' => 'NMB Bank',
                'bank_account_number' => '0169876543210',
                'emergency_contact_name' => 'Hassan Ali',
                'emergency_contact_phone' => '+255756543210',
                'emergency_contact_relationship' => 'Father',
                'marital_status' => 'single',
                'dependents' => 0,
                'occupation' => 'Small Business Owner',
                'kyc_status' => 'verified',
                'kyc_verification_date' => now()->subDays(15),
                'verified_by' => $adminUser?->id,
                'status' => 'active',
                'notes' => 'Runs a small retail shop in Mbagala',
            ],
            [
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'individual',
                'organization_id' => $organization->id,
                'branch_id' => $branch?->id,
                'first_name' => 'Peter',
                'last_name' => 'Kimaro',
                'date_of_birth' => '1978-12-10',
                'gender' => 'male',
                'national_id' => '4567891234567',
                'phone_number' => '+255787654321',
                'physical_address' => 'Village Road, Arusha',
                'city' => 'Arusha',
                'region' => 'Arusha',
                'country' => 'Tanzania',
                'monthly_income' => 2200000.00,
                'income_source' => 'Business',
                'bank_name' => 'Equity Bank',
                'bank_account_number' => '0174567891234',
                'emergency_contact_name' => 'Grace Kimaro',
                'emergency_contact_phone' => '+255788765432',
                'emergency_contact_relationship' => 'Wife',
                'marital_status' => 'married',
                'dependents' => 4,
                'occupation' => 'Farm Owner',
                'kyc_status' => 'pending',
                'status' => 'active',
                'notes' => 'Large-scale farmer in Arusha region',
            ],

            // Business Clients
            [
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'business',
                'organization_id' => $organization->id,
                'branch_id' => $branch?->id,
                'business_name' => 'Mwenge Construction Ltd',
                'business_registration_number' => 'C.123456',
                'business_type' => 'corporation',
                'phone_number' => '+255222123456',
                'secondary_phone' => '+255222123457',
                'email' => 'info@mwengeconstruction.co.tz',
                'physical_address' => 'Industrial Area, Dar es Salaam',
                'city' => 'Dar es Salaam',
                'region' => 'Dar es Salaam',
                'country' => 'Tanzania',
                'postal_code' => '11000',
                'business_description' => 'Construction and engineering services',
                'years_in_business' => 15,
                'annual_turnover' => 250000000.00,
                'bank_name' => 'CRDB Bank',
                'bank_account_number' => '0159876543210',
                'emergency_contact_name' => 'James Mwenge',
                'emergency_contact_phone' => '+255713987654',
                'emergency_contact_relationship' => 'Managing Director',
                'kyc_status' => 'verified',
                'kyc_verification_date' => now()->subDays(45),
                'verified_by' => $adminUser?->id,
                'status' => 'active',
                'notes' => 'Established construction company with good track record',
            ],
            [
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'business',
                'organization_id' => $organization->id,
                'branch_id' => $branch?->id,
                'business_name' => 'Kilimanjaro Coffee Cooperative',
                'business_registration_number' => 'COOP.789012',
                'business_type' => 'cooperative',
                'phone_number' => '+255272345678',
                'email' => 'coffee@kilimanjaro.co.tz',
                'physical_address' => 'Moshi Town, Kilimanjaro',
                'city' => 'Moshi',
                'region' => 'Kilimanjaro',
                'country' => 'Tanzania',
                'business_description' => 'Coffee farming cooperative with 500+ members',
                'years_in_business' => 8,
                'annual_turnover' => 85000000.00,
                'bank_name' => 'NMB Bank',
                'bank_account_number' => '0161112223334',
                'emergency_contact_name' => 'Anna Mosha',
                'emergency_contact_phone' => '+255765987654',
                'emergency_contact_relationship' => 'Secretary',
                'kyc_status' => 'verified',
                'kyc_verification_date' => now()->subDays(20),
                'verified_by' => $adminUser?->id,
                'status' => 'active',
                'notes' => 'Large coffee cooperative serving local farmers',
            ],

            // Group Client
            [
                'client_number' => Client::generateClientNumber(),
                'client_type' => 'group',
                'organization_id' => $organization->id,
                'branch_id' => $branch?->id,
                'business_name' => 'Women Entrepreneurs Group',
                'business_type' => 'other',
                'phone_number' => '+255744567890',
                'email' => 'women@entrepreneurs.co.tz',
                'physical_address' => 'Community Center, Dodoma',
                'city' => 'Dodoma',
                'region' => 'Dodoma',
                'country' => 'Tanzania',
                'business_description' => 'Group of 25 women running various small businesses',
                'years_in_business' => 3,
                'bank_name' => 'Equity Bank',
                'bank_account_number' => '0175556667778',
                'emergency_contact_name' => 'Grace Mwalimu',
                'emergency_contact_phone' => '+255744567891',
                'emergency_contact_relationship' => 'Group Leader',
                'kyc_status' => 'pending',
                'status' => 'active',
                'notes' => 'Informal group of women entrepreneurs seeking microfinance',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::firstOrCreate(
                [
                    'client_number' => $clientData['client_number'],
                    'organization_id' => $clientData['organization_id'],
                ],
                $clientData
            );
        }

        $this->command->info('Sample clients created successfully for organization: ' . $organization->name);
        $this->command->info('Created ' . count($clients) . ' clients:');
        
        $individualCount = collect($clients)->where('client_type', 'individual')->count();
        $businessCount = collect($clients)->whereIn('client_type', ['business', 'group'])->count();
        $verifiedCount = collect($clients)->where('kyc_status', 'verified')->count();
        $pendingCount = collect($clients)->where('kyc_status', 'pending')->count();

        $this->command->info('- Individual Clients: ' . $individualCount);
        $this->command->info('- Business/Group Clients: ' . $businessCount);
        $this->command->info('- KYC Verified: ' . $verifiedCount);
        $this->command->info('- KYC Pending: ' . $pendingCount);

        foreach ($clients as $client) {
            $this->command->info('- ' . ($client['business_name'] ?? $client['first_name'] . ' ' . $client['last_name']) . ' (' . $client['client_number'] . ')');
        }
    }
}
