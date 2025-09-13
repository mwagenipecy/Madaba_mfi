<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users with minimal required fields
        $users = [
            [
                'email' => 'superadmin@madaba.com',
                'name' => 'Super Administrator',
                'role' => 'super_admin',
            ],
            [
                'email' => 'admin@madaba.com',
                'name' => 'System Administrator',
                'role' => 'admin',
            ],
            [
                'email' => 'manager@madaba.com',
                'name' => 'Branch Manager',
                'role' => 'manager',
            ],
            [
                'email' => 'finance@madaba.com',
                'name' => 'Finance Officer',
                'role' => 'accountant',
            ],
            [
                'email' => 'teller@madaba.com',
                'name' => 'Main Branch Teller',
                'role' => 'cashier',
            ],
            [
                'email' => 'teller.downtown@madaba.com',
                'name' => 'Downtown Branch Teller',
                'role' => 'cashier',
            ],
            [
                'email' => 'customer@madaba.com',
                'name' => 'Customer Service Rep',
                'role' => 'loan_officer',
            ],
            [
                'email' => 'approval@madaba.com',
                'name' => 'Approval Officer',
                'role' => 'admin',
            ],
        ];

        $organization = Organization::first();
        $mainBranch = Branch::where('code', 'BR001')->first();
        $downtownBranch = Branch::where('code', 'BR002')->first();

        foreach ($users as $userData) {
            // Split name into first and last name
            $nameParts = explode(' ', $userData['name'], 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
            
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $userData['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'role' => $userData['role'] ?? 'admin',
                    'organization_id' => $organization->id,
                    'branch_id' => $userData['email'] === 'teller.downtown@madaba.com' ? $downtownBranch?->id : $mainBranch?->id,
                ]
            );
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('==================');
        $this->command->info('🔥 SUPER ADMIN: superadmin@madaba.com / password (FULL ACCESS)');
        $this->command->info('Admin: admin@madaba.com / password');
        $this->command->info('Branch Manager: manager@madaba.com / password');
        $this->command->info('Finance Officer: finance@madaba.com / password');
        $this->command->info('Main Branch Teller: teller@madaba.com / password');
        $this->command->info('Downtown Branch Teller: teller.downtown@madaba.com / password');
        $this->command->info('Customer Service: customer@madaba.com / password');
        $this->command->info('Approval Officer: approval@madaba.com / password');
        $this->command->info('');
        $this->command->info('All users have the password: "password"');
        $this->command->info('');
        $this->command->info('🚀 RECOMMENDED: Use superadmin@madaba.com for testing all features!');
    }
}
