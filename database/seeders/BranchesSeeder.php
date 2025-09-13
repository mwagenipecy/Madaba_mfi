<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Organization;

class BranchesSeeder extends Seeder
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

        $branches = [
            [
                'name' => 'Main Branch',
                'code' => 'BR001',
                'description' => 'Main headquarters branch',
                'organization_id' => $organization->id,
                'address' => '123 Main Street',
                'city' => 'Sample City',
                'state' => 'Sample State',
                'country' => 'Sample Country',
                'postal_code' => '12345',
                'phone' => '+1234567890',
                'email' => 'main@defaultorg.com',
                'manager_name' => 'John Doe',
                'manager_email' => 'john.doe@defaultorg.com',
                'manager_phone' => '+1234567891',
                'status' => 'active',
                'established_date' => now()->subYears(2),
            ],
            [
                'name' => 'Downtown Branch',
                'code' => 'BR002',
                'description' => 'Downtown location branch',
                'organization_id' => $organization->id,
                'address' => '456 Downtown Ave',
                'city' => 'Sample City',
                'state' => 'Sample State',
                'country' => 'Sample Country',
                'postal_code' => '12346',
                'phone' => '+1234567892',
                'email' => 'downtown@defaultorg.com',
                'manager_name' => 'Jane Smith',
                'manager_email' => 'jane.smith@defaultorg.com',
                'manager_phone' => '+1234567893',
                'status' => 'active',
                'established_date' => now()->subYear(),
            ],
            [
                'name' => 'Suburban Branch',
                'code' => 'BR003',
                'description' => 'Suburban area branch',
                'organization_id' => $organization->id,
                'address' => '789 Suburban Road',
                'city' => 'Sample City',
                'state' => 'Sample State',
                'country' => 'Sample Country',
                'postal_code' => '12347',
                'phone' => '+1234567894',
                'email' => 'suburban@defaultorg.com',
                'manager_name' => 'Mike Johnson',
                'manager_email' => 'mike.johnson@defaultorg.com',
                'manager_phone' => '+1234567895',
                'status' => 'active',
                'established_date' => now()->subMonths(6),
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::firstOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
        }
    }
}
