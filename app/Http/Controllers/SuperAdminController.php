<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\Branch;
use App\Models\Account;
use App\Services\OrganizationRegistrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    protected $organizationService;

    public function __construct(OrganizationRegistrationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    /**
     * Display organizations list
     */
    public function index()
    {
        $organizations = Organization::with(['branches', 'users'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('super-admin.organizations.index', compact('organizations'));
    }

    /**
     * Show organization creation form
     */
    public function create()
    {
        return view('super-admin.organizations.create');
    }

    /**
     * Store new organization with auto-setup
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:microfinance_bank,cooperative_society,ngo,credit_union,other',
            'name' => 'required|string|max:255|unique:organizations,name',
            'registration_number' => 'required|string|max:100|unique:organizations,registration_number',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Admin user data
            'admin_first_name' => 'required|string|max:100',
            'admin_last_name' => 'required|string|max:100',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_phone' => 'required|string|max:20',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $organizationData = $request->only([
                'name', 'type', 'registration_number', 'email', 'phone', 'address',
                'city', 'state', 'country', 'postal_code', 'website'
            ]);

            $userData = [
                'first_name' => $request->admin_first_name,
                'last_name' => $request->admin_last_name,
                'email' => $request->admin_email,
                'phone' => $request->admin_phone,
                'password' => $request->admin_password,
            ];

            $result = $this->organizationService->registerOrganization(
                $organizationData,
                $userData,
                $request->file('logo')
            );

            return redirect()->route('super-admin.organizations.show', $result['organization']->id)
                ->with('success', 'Organization created successfully with HQ branch, main accounts attached to HQ branch, zero amount transactions in general ledger, and admin user.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create organization: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show organization details
     */
    public function show(Organization $organization)
    {
        $organization->load([
            'branches' => function($query) {
                $query->orderBy('is_hq', 'desc')->orderBy('name');
            },
            'users' => function($query) {
                $query->where('role', 'admin');
            },
            'accounts'
        ]);

        $hqBranch = $organization->branches->where('is_hq', true)->first();
        $mainAccounts = $organization->accounts->filter(function($account) use ($hqBranch) {
            if ($account->branch_id !== $hqBranch->id || !$account->metadata) {
                return false;
            }
            
            // Check if it's a main category account (either by is_hq_account flag or account_type)
            $isHQAccount = $account->metadata['is_hq_account'] ?? false;
            $accountType = $account->metadata['account_type'] ?? null;
            
            return $isHQAccount || $accountType === 'main_category';
        });

        return view('super-admin.organizations.show', compact('organization', 'hqBranch', 'mainAccounts'));
    }

    /**
     * Show organization edit form
     */
    public function edit(Organization $organization)
    {
        return view('super-admin.organizations.edit', compact('organization'));
    }

    /**
     * Update organization
     */
    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:organizations,name,' . $organization->id,
            'registration_number' => 'required|string|max:100|unique:organizations,registration_number,' . $organization->id,
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $data = $request->only([
                'name', 'registration_number', 'email', 'phone', 'address',
                'city', 'state', 'country', 'postal_code', 'website'
            ]);

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('organization-logos', 'public');
                $data['logo_path'] = $logoPath;
            }

            $organization->update($data);

            return redirect()->route('super-admin.organizations.show', $organization->id)
                ->with('success', 'Organization updated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update organization: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Deactivate organization
     */
    public function deactivate(Organization $organization)
    {
        try {
            $organization->update(['status' => 'inactive']);
            
            // Deactivate all users
            $organization->users()->update(['status' => 'inactive']);
            
            // Deactivate all branches
            $organization->branches()->update(['status' => 'inactive']);

            return redirect()->route('super-admin.organizations.index')
                ->with('success', 'Organization deactivated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to deactivate organization: ' . $e->getMessage()]);
        }
    }

    /**
     * Reactivate organization
     */
    public function reactivate(Organization $organization)
    {
        try {
            $organization->update(['status' => 'active']);
            
            // Reactivate admin users
            $organization->users()->where('role', 'admin')->update(['status' => 'active']);
            
            // Reactivate HQ branch
            $organization->branches()->where('is_hq', true)->update(['status' => 'active']);

            return redirect()->route('super-admin.organizations.index')
                ->with('success', 'Organization reactivated successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reactivate organization: ' . $e->getMessage()]);
        }
    }

    /**
     * Get organization statistics
     */
    public function statistics(Organization $organization)
    {
        $stats = [
            'total_branches' => $organization->branches()->count(),
            'total_users' => $organization->users()->count(),
            'total_clients' => $organization->clients()->count(),
            'total_loans' => $organization->loans()->count(),
            'total_accounts' => $organization->accounts()->count(),
            'hq_branch' => $organization->branches()->where('is_hq', true)->first(),
            'main_accounts' => $organization->accounts()->where('metadata->is_hq_account', true)->get(),
        ];

        return response()->json($stats);
    }

    /**
     * View all mapped account balances across organizations
     */
    public function mappedAccountBalances()
    {
        $mappedAccounts = Account::with([
                'organization',
                'branch',
                'mappedRealAccount',
                'accountType'
            ])
            ->whereNotNull('real_account_id')
            ->orderBy('organization_id')
            ->orderBy('branch_id')
            ->get()
            ->groupBy('organization.name');

        $totalBalance = $mappedAccounts->flatten()->sum('balance');
        $totalAccounts = $mappedAccounts->flatten()->count();

        return view('super-admin.mapped-account-balances', compact(
            'mappedAccounts',
            'totalBalance',
            'totalAccounts'
        ));
    }

    /**
     * View specific organization's mapped accounts
     */
    public function organizationMappedAccounts(Organization $organization)
    {
        $mappedAccounts = Account::with([
                'branch',
                'mappedRealAccount',
                'accountType'
            ])
            ->where('organization_id', $organization->id)
            ->whereNotNull('real_account_id')
            ->orderBy('branch_id')
            ->get();

        $totalBalance = $mappedAccounts->sum('balance');

        return view('super-admin.organization-mapped-accounts', compact(
            'organization',
            'mappedAccounts',
            'totalBalance'
        ));
    }
}