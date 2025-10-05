<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\Account;
use App\Models\RealAccount;
use App\Models\AccountRealAccountMapping;
use Illuminate\Support\Facades\Auth;

class OrganizationSettingsController extends Controller
{
    /**
     * Display organization settings dashboard
     */
    public function index()
    {
        return view('organization-settings.index');
    }

    /**
     * Display organization details (self-view)
     */
    public function details()
    {
        // Get the logged-in user's organization
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'User must belong to an organization');
        }
        
        $organization = Organization::with(['branches', 'users'])
            ->findOrFail($user->organization_id);
        
        // Get HQ branch
        $hqBranch = $organization->branches->where('is_hq', true)->first();
        
        // Get main accounts for this organization
        $mainAccounts = $organization->accounts->filter(function($account) use ($hqBranch) {
            if (!$account->branch_id || !$account->metadata) {
                return false;
            }
            
            // Check if it's a main category account (either by is_hq_account flag or account_type)
            $isHQAccount = $account->metadata['is_hq_account'] ?? false;
            $accountType = $account->metadata['account_type'] ?? null;
            
            return $isHQAccount || $accountType === 'main_category';
        });
        
        return view('organization-settings.details', compact('organization', 'hqBranch', 'mainAccounts'));
    }

    /**
     * Show organization's own users
     */
    public function users()
    {
        return view('organization-settings.users');
    }

    /**
     * Show form to create user for own organization
     */
    public function createUser()
    {
        return view('organization-settings.create-user');
    }

    /**
     * Show user details
     */
    public function showUser(User $user)
    {
        // Ensure the user belongs to the current user's organization
        if ($user->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to user data.');
        }

        return view('organization-settings.show-user', compact('user'));
    }

    /**
     * Show form to edit user
     */
    public function editUser(User $user)
    {
        // Ensure the user belongs to the current user's organization
        if ($user->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to user data.');
        }

        return view('organization-settings.edit-user', compact('user'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        // Ensure the user belongs to the current user's organization
        if ($user->organization_id !== Auth::user()->organization_id) {
            abort(403, 'Unauthorized access to user data.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,loan_officer,user',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,pending',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'branch_id' => $request->branch_id,
        ];

        // Update email verification status based on status
        if ($request->status === 'active') {
            $userData['email_verified_at'] = now();
        } else {
            $userData['email_verified_at'] = null;
        }

        // Update password if provided
        if ($request->filled('password')) {
            $userData['password'] = bcrypt($request->password);
        }

        $user->update($userData);

        return redirect()->route('organization-settings.users.show', $user)
                        ->with('success', 'User updated successfully.');
    }

    /**
     * Show form to edit own organization
     */
    public function edit()
    {
        return view('organization-settings.edit');
    }

    /**
     * View mapped account balances for own organization
     */
    public function mappedAccountBalances()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'User must belong to an organization');
        }

        $organization = Organization::with(['accounts.accountType', 'accounts.branch', 'branches', 'users'])
            ->findOrFail($user->organization_id);

        // Get only external accounts for mapping dropdown
        $externalAccounts = $organization->accounts()
            ->where('account_classification', 'external')
            ->whereIn('external_account_type', ['receiver', 'giver'])
            ->with(['accountType', 'branch'])
            ->orderBy('external_account_type')
            ->orderBy('name')
            ->get();

        // Get RealAccounts that map to accounts in this organization
        $realAccountsWithMappings = RealAccount::with(['mappedAccounts.accountType', 'mappedAccounts.branch'])
            ->whereHas('mappedAccounts', function($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            })
            ->get()
            ->groupBy(function($realAccount) {
                // Group by the branch of the first mapped account
                $firstAccount = $realAccount->mappedAccounts->first();
                return $firstAccount && $firstAccount->branch ? $firstAccount->branch->name : 'HQ Branch';
            });

        $totalBalance = $realAccountsWithMappings->flatten()->flatMap(function($realAccount) {
            return $realAccount->mappedAccounts;
        })->sum('balance');
        
        $totalAccounts = $realAccountsWithMappings->flatten()->sum(function($realAccount) {
            return $realAccount->mappedAccounts->count();
        });

        return view('organization-settings.mapped-account-balances', compact(
            'organization',
            'externalAccounts',
            'realAccountsWithMappings',
            'totalBalance',
            'totalAccounts'
        ));
    }

    /**
     * Store a new real account mapping
     */
    public function storeRealAccount(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'User must belong to an organization');
        }

        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'external_account_name' => 'required|string|max:255',
            'external_account_id' => 'required|string|max:255',
            'provider_name' => 'required|string|max:255',
            'provider_type' => 'required|string|in:bank,mno,payment_gateway,other',
            'api_endpoint' => 'nullable|url|max:255',
            'mapping_description' => 'nullable|string|max:500',
        ]);

        // Verify the account belongs to the user's organization and is external
        $account = Account::where('id', $validated['account_id'])
            ->where('organization_id', $user->organization_id)
            ->where('account_classification', 'external')
            ->whereIn('external_account_type', ['receiver', 'giver'])
            ->firstOrFail();

        // Create the real account record first
        $realAccount = RealAccount::create([
            'external_account_name' => $validated['external_account_name'],
            'external_account_id' => $validated['external_account_id'],
            'provider_name' => $validated['provider_name'],
            'provider_type' => $validated['provider_type'],
            'api_endpoint' => $validated['api_endpoint'],
            'sync_status' => 'pending',
            'is_active' => true,
            'last_balance' => 0.00,
            'last_sync_at' => null,
        ]);

        // Create the mapping relationship
        AccountRealAccountMapping::create([
            'account_id' => $account->id,
            'real_account_id' => $realAccount->id,
            'mapping_description' => $validated['mapping_description'],
            'is_active' => true,
        ]);

        return redirect()->route('organization-settings.mapped-account-balances')
            ->with('success', 'Real account mapping created successfully!');
    }

    /**
     * Display real accounts list
     */
    public function realAccounts()
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'User must belong to an organization');
        }

        $organization = Organization::with(['accounts.mappedRealAccount', 'accounts.accountType', 'branches'])
            ->findOrFail($user->organization_id);

        $realAccounts = RealAccount::with(['account.accountType', 'account.branch'])
            ->whereHas('account', function($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('organization-settings.real-accounts', compact('organization', 'realAccounts'));
    }

    /**
     * Show edit form for real account mapping
     */
    public function editRealAccount(RealAccount $realAccount)
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'User must belong to an organization');
        }

        // Verify the real account maps to an account in user's organization
        $mappedAccount = $realAccount->mappedAccounts()
            ->where('organization_id', $user->organization_id)
            ->first();

        if (!$mappedAccount) {
            abort(404, 'Real account mapping not found');
        }

        $organization = Organization::with(['accounts.accountType', 'accounts.branch'])
            ->findOrFail($user->organization_id);

        // Get the mapping record with pivots
        $mapping = $realAccount->accountMappings()
            ->where('account_id', $mappedAccount->id)
            ->first();

        return view('organization-settings.edit-real-account', compact(
            'organization',
            'realAccount',
            'mapping'
        ));
    }

    /**
     * Update real account mapping
     */
    public function updateRealAccount(Request $request, RealAccount $realAccount)
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'User must belong to an organization');
        }

        $validated = $request->validate([
            'external_account_name' => 'required|string|max:255',
            'external_account_id' => 'required|string|max:255',
            'provider_name' => 'required|string|max:255',
            'provider_type' => 'required|string|in:mno,bank,payment_gateway,other',
            'api_endpoint' => 'nullable|url|max:255',
            'mapping_description' => 'nullable|string|max:500',
            'account_id' => 'required|exists:accounts,id',
        ]);

        // Verify the real account maps to an account in user's organization
        $mappedAccount = $realAccount->mappedAccounts()
            ->where('organization_id', $user->organization_id)
            ->first();

        if (!$mappedAccount) {
            abort(404, 'Real account mapping not found');
        }

        // Verify the new account belongs to user's organization
        $newAccount = Account::where('id', $validated['account_id'])
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        // Update the real account
        $realAccount->update([
            'external_account_name' => $validated['external_account_name'],
            'external_account_id' => $validated['external_account_id'],
            'provider_name' => $validated['provider_name'],
            'provider_type' => $validated['provider_type'],
            'api_endpoint' => $validated['api_endpoint'],
        ]);

        // Update the mapping relationship
        $mapping = AccountRealAccountMapping::where('real_account_id', $realAccount->id)
            ->where('account_id', $mappedAccount->id)
            ->first();

        if ($mapping) {
            $mapping->update([
                'account_id' => $validated['account_id'],
                'mapping_description' => $validated['mapping_description'],
            ]);
        }

        return redirect()->route('organization-settings.mapped-account-balances')
            ->with('success', 'Real account mapping updated successfully!');
    }

    /**
     * Delete real account mapping
     */
    public function destroyRealAccount(RealAccount $realAccount)
    {
        $user = Auth::user();
        if (!$user || !$user->organization_id) {
            abort(403, 'User must belong to an organization');
        }

        // Verify the real account maps to an account in user's organization
        $mappedAccount = $realAccount->mappedAccounts()
            ->where('organization_id', $user->organization_id)
            ->first();

        if (!$mappedAccount) {
            abort(404, 'Real account mapping not found');
        }

        // Find and delete the mapping
        $mapping = AccountRealAccountMapping::where('real_account_id', $realAccount->id)
            ->where('account_id', $mappedAccount->id)
            ->first();

        if ($mapping) {
            $mapping->delete();
        }

        // If no more accounts map to this real account, delete the real account
        $remainingMappings = AccountRealAccountMapping::where('real_account_id', $realAccount->id)->count();
        if ($remainingMappings === 0) {
            $realAccount->delete();
        }

        return redirect()->route('organization-settings.mapped-account-balances')
            ->with('success', 'Real account mapping deleted successfully!');
    }
}
