<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use App\Mail\OrganizationWelcomeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrganizationRegistrationService
{
    public function registerOrganization(array $organizationData, array $userData, $logo = null)
    {
        return DB::transaction(function () use ($organizationData, $userData, $logo) {
            // Create Organization
            if ($logo) {
                $logoPath = $logo->store('organization-logos', 'public');
                $organizationData['logo_path'] = $logoPath;
            }

            $organizationData['slug'] = Str::slug($organizationData['name']);
            $organizationData['status'] = 'pending_approval';
            
            $organization = Organization::create($organizationData);

            // Create Admin User
            $userData['organization_id'] = $organization->id;
            $userData['password'] = Hash::make($userData['password']);
            $userData['role'] = 'admin';
            $userData['status'] = 'active';
            $userData['employee_id'] = 'ADM-' . str_pad($organization->id, 4, '0', STR_PAD_LEFT);
            $userData['permissions'] = $this->getDefaultAdminPermissions();

            $user = User::create($userData);

            // Send welcome email
            try {
                Mail::to($user->email)->send(new OrganizationWelcomeMail($organization, $user));
            } catch (\Exception $e) {
                // Log email error but don't fail registration
                logger()->error('Failed to send welcome email: ' . $e->getMessage());
            }

            return ['organization' => $organization, 'user' => $user];
        });
    }

    private function getDefaultAdminPermissions()
    {
        return [
            'manage_users',
            'manage_clients',
            'manage_loans',
            'manage_savings',
            'manage_collections',
            'view_reports',
            'generate_reports',
            'manage_settings',
            'manage_products',
            'approve_loans',
            'manage_groups',
            'manage_transactions',
            'system_backup',
            'audit_logs',
        ];
    }
}