<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();
        $mainBranch = Branch::where('code', 'BR001')->first();

        if (!$organization) {
            $this->command->warn('No organization found. Please run OrganizationSeeder first.');
            return;
        }

        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'organization_id' => $organization->id,
                'branch_id' => $mainBranch?->id,
            ]
        );

        // Create a regular user for testing
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'first_name' => 'Regular',
                'last_name' => 'User',
                'email' => 'user@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('user123'),
                'role' => 'manager',
                'organization_id' => $organization->id,
                'branch_id' => $mainBranch?->id,
            ]
        );

        $this->command->info('Admin and regular users created successfully!');
        $this->command->info('Admin Email: admin@example.com');
        $this->command->info('Admin Password: admin123');
        $this->command->info('User Email: user@example.com');
        $this->command->info('User Password: user123');
    }
}
