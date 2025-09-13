<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default organization
        Organization::firstOrCreate(
            ['registration_number' => 'ORG001'],
            [
                'name' => 'Default Organization',
                'slug' => 'default-organization',
                'registration_number' => 'ORG001',
                'license_number' => 'LIC001',
                'type' => 'microfinance_bank',
                'email' => 'info@defaultorg.com',
                'phone' => '+1234567890',
                'address' => '123 Main Street',
                'city' => 'Sample City',
                'state' => 'Sample State',
                'country' => 'Sample Country',
                'postal_code' => '12345',
                'authorized_capital' => 1000000.00,
                'incorporation_date' => now()->subYears(2),
                'status' => 'active',
                'description' => 'Default organization for system administration',
                'approved_at' => now(),
            ]
        );

        $this->command->info('Default organization created successfully!');
    }
}
