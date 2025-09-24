<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use App\Models\Branch;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\GeneralLedger;
use App\Mail\OrganizationWelcomeMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
            $organizationData['status'] = 'active'; // Changed to active for super admin creation
            
            $organization = Organization::create($organizationData);

            // Create HQ Branch automatically
            $hqBranch = $this->createHQBranch($organization);

            // Create Main 5 Accounts (organization-level, no branch)
            $mainAccounts = $this->createMainAccounts($organization);

            // Create General Ledger entries for zero balance accounts
            $this->createZeroBalanceLedgerEntries($mainAccounts, $organization);

            // Create Admin User
            $userData['organization_id'] = $organization->id;
            $userData['branch_id'] = $hqBranch->id; // Assign to HQ branch
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

            return [
                'organization' => $organization, 
                'user' => $user, 
                'hq_branch' => $hqBranch,
                'main_accounts' => $mainAccounts
            ];
        });
    }

    /**
     * Create HQ Branch for the organization
     */
    private function createHQBranch(Organization $organization)
    {
        return Branch::create([
            'name' => 'Headquarters',
            'code' => 'HQ-' . str_pad($organization->id, 3, '0', STR_PAD_LEFT),
            'description' => 'Main headquarters branch for ' . $organization->name,
            'organization_id' => $organization->id,
            'address' => $organization->address,
            'city' => $organization->city,
            'state' => $organization->state,
            'country' => $organization->country,
            'postal_code' => $organization->postal_code,
            'phone' => $organization->phone,
            'email' => $organization->email,
            'manager_name' => 'System Administrator',
            'manager_email' => $organization->email,
            'manager_phone' => $organization->phone,
            'status' => 'active',
            'established_date' => now(),
            'is_hq' => true, // Mark as HQ branch
        ]);
    }

    /**
     * Create main 5 accounts for the organization
     */
    private function createMainAccounts(Organization $organization)
    {
        // Get the 5 main account types
        $mainAccountTypes = AccountType::where('is_main_account', true)
            ->where('is_active', true)
            ->get();

        $accounts = [];

        foreach ($mainAccountTypes as $accountType) {
            $accountNumber = $this->generateAccountNumber($accountType->code, $organization->id);
            
            $account = Account::create([
                'account_number' => $accountNumber,
                'name' => $accountType->name,
                'account_type_id' => $accountType->id,
                'organization_id' => $organization->id,
                'branch_id' => null, // Organization-level main category (no branch)
                'parent_account_id' => null,
                'balance' => 0.00,
                'opening_balance' => 0.00,
                'currency' => 'TZS',
                'description' => 'Main ' . $accountType->name . ' category for ' . $organization->name,
                'status' => 'active',
                'opening_date' => now(),
                'metadata' => [
                    'account_type' => 'main_category',
                    'auto_created' => true,
                ],
            ]);

            $accounts[] = $account;
        }

        return $accounts;
    }

    /**
     * Create general ledger entries for zero balance accounts
     */
    private function createZeroBalanceLedgerEntries(array $accounts, Organization $organization)
    {
        foreach ($accounts as $account) {
            // Create opening balance entry
            GeneralLedger::create([
                'organization_id' => $organization->id,
                'branch_id' => $account->branch_id,
                'transaction_id' => 'OPEN-' . $account->account_number . '-' . now()->format('YmdHis'),
                'transaction_date' => now(),
                'account_id' => $account->id,
                'transaction_type' => 'debit', // Opening balance is typically a debit
                'amount' => 0.00,
                'currency' => 'TZS',
                'description' => 'Opening balance for ' . $account->name,
                'reference_type' => 'opening_balance',
                'reference_id' => $account->id,
                'created_by' => 1, // System user
                'approved_by' => 1, // Auto-approved for system accounts
                'approved_at' => now(),
                'balance_after' => 0.00,
                'metadata' => [
                    'account_type' => 'main_category',
                    'auto_created' => true,
                    'opening_balance' => true,
                ],
            ]);
        }
    }

    /**
     * Generate a unique account number
     */
    private function generateAccountNumber(string $accountTypeCode, int $organizationId): string
    {
        $prefix = strtoupper($accountTypeCode);
        $orgCode = str_pad($organizationId, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $orgCode . '-' . $timestamp . '-' . $random;
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
            'manage_branches',
            'manage_accounts',
            'manage_approvals',
            'manage_expenses',
        ];
    }
}