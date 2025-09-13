<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemLog;
use App\Models\User;
use App\Models\Organization;

class SystemLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin user
        $adminUser = User::where('email', 'admin@madaba.com')->first();
        $regularUser = User::where('email', 'manager@madaba.com')->first();
        $organization = Organization::first();

        // Create sample system logs
        $logs = [
            [
                'action' => 'system_startup',
                'description' => 'System startup completed successfully',
                'level' => 'info',
                'user_id' => $adminUser->id,
                'data' => ['version' => '1.0.0', 'environment' => 'production'],
                'created_at' => now()->subDays(5),
            ],
            [
                'action' => 'user_created',
                'description' => "New user created: {$regularUser->name} ({$regularUser->email})",
                'level' => 'info',
                'model_type' => 'App\Models\User',
                'model_id' => $regularUser->id,
                'user_id' => $adminUser->id,
                'data' => ['role' => $regularUser->role],
                'created_at' => now()->subDays(4),
            ],
            [
                'action' => 'organization_created',
                'description' => "Organization created: {$organization->name}",
                'level' => 'info',
                'model_type' => 'App\Models\Organization',
                'model_id' => $organization->id,
                'user_id' => $adminUser->id,
                'data' => ['type' => $organization->type, 'status' => $organization->status],
                'created_at' => now()->subDays(3),
            ],
            [
                'action' => 'user_login',
                'description' => "User logged in: {$adminUser->name}",
                'level' => 'info',
                'model_type' => 'App\Models\User',
                'model_id' => $adminUser->id,
                'user_id' => $adminUser->id,
                'data' => ['ip_address' => '192.168.1.100'],
                'created_at' => now()->subDays(2),
            ],
            [
                'action' => 'failed_login_attempt',
                'description' => 'Failed login attempt detected',
                'level' => 'warning',
                'user_id' => null,
                'data' => ['email' => 'unknown@example.com', 'ip_address' => '192.168.1.200'],
                'created_at' => now()->subDays(1),
            ],
            [
                'action' => 'database_backup',
                'description' => 'Scheduled database backup completed',
                'level' => 'info',
                'user_id' => $adminUser->id,
                'data' => ['backup_size' => '15.2 MB', 'duration' => '2 minutes'],
                'created_at' => now()->subHours(12),
            ],
            [
                'action' => 'system_error',
                'description' => 'Critical system error detected',
                'level' => 'critical',
                'user_id' => $adminUser->id,
                'data' => ['error_code' => 'ERR_001', 'component' => 'payment_processor'],
                'created_at' => now()->subHours(6),
            ],
            [
                'action' => 'configuration_changed',
                'description' => 'System configuration updated',
                'level' => 'warning',
                'user_id' => $adminUser->id,
                'data' => ['config_key' => 'max_login_attempts', 'old_value' => 3, 'new_value' => 5],
                'created_at' => now()->subHours(3),
            ],
            [
                'action' => 'user_logout',
                'description' => "User logged out: {$regularUser->name}",
                'level' => 'info',
                'model_type' => 'App\Models\User',
                'model_id' => $regularUser->id,
                'user_id' => $regularUser->id,
                'data' => ['session_duration' => '2 hours 15 minutes'],
                'created_at' => now()->subHours(1),
            ],
        ];

        foreach ($logs as $log) {
            SystemLog::create($log);
        }
    }
}
