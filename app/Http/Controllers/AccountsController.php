<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Branch;
use App\Models\RealAccount;
use App\Models\SystemLog;
use App\Models\Organization;

class AccountsController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $totalAccounts = Account::where('organization_id', $organizationId)->count();
        $activeAccounts = Account::where('organization_id', $organizationId)->where('status', 'active')->count();
        $mainAccounts = Account::where('organization_id', $organizationId)->whereNull('branch_id')->count();
        $branchAccounts = Account::where('organization_id', $organizationId)->whereNotNull('branch_id')->count();
        $externalAccounts = Account::where('organization_id', $organizationId)->where('account_classification', 'external')->count();
        
        // Get latest transactions from general ledger
        $latestTransactions = \App\Models\GeneralLedger::where('organization_id', $organizationId)
            ->with(['account.accountType', 'branch'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('accounts.index', compact('totalAccounts', 'activeAccounts', 'mainAccounts', 'branchAccounts', 'externalAccounts', 'latestTransactions'));
    }

    /**
     * Show main accounts (organization level)
     */
    public function mainAccounts()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;

        // Get the HQ branch for this organization
        $hqBranch = \App\Models\Branch::where('organization_id', $organizationId)
            ->where('is_hq', true)
            ->first();

        if (!$hqBranch) {
            // Fallback: get the main branch (first branch)
            $hqBranch = \App\Models\Branch::where('organization_id', $organizationId)->first();
        }

        // Get the 5 main account types
        $accountTypes = \App\Models\AccountType::whereIn('name', ['Assets', 'Revenue', 'Liability', 'Equity', 'Expense'])
            ->orderBy('name')
            ->get();

        // Fetch one main account per AccountType (avoid duplicates)
        $categories = collect();
        
        foreach ($accountTypes as $accountType) {
            $account = Account::where('organization_id', $organizationId)
                ->where('account_type_id', $accountType->id)
                ->where(function($query) use ($hqBranch) {
                    // First try HQ branch accounts (if exists and branch_id matches)
                    if ($hqBranch) {
                        $query->where(function($subQuery) use ($hqBranch) {
                            $subQuery->where('branch_id', $hqBranch->id)
                                    ->where(function($metaQuery) {
                                        $metaQuery->whereJsonContains('metadata->is_hq_account', true)
                                                ->orWhereJsonContains('metadata->account_type', 'main_category');
                                    });
                        });
                    }
                    
                    // Fallback to organization-level main accounts
                    $query->orWhere(function($subQuery) {
                        $subQuery->where('branch_id', null)
                                 ->whereJsonContains('metadata->account_type', 'main_category');
                    });
                })
                ->withCount(['childAccounts as sub_accounts_count' => function($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId);
                }])
                ->withSum(['childAccounts as total_balance' => function($q) use ($organizationId) {
                    $q->where('organization_id', $organizationId);
                }], 'balance')
                ->with('accountType')
                ->first();
                
            if ($account) {
                $categories->push($account);
            }
        }

        return view('accounts.main-accounts', compact('categories', 'hqBranch'));
    }

    /**
     * Show all accounts with branch filtering
     */
    public function branchAccounts(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get all branches for this organization
        $branches = Branch::where('organization_id', $organizationId)->orderBy('name')->get();
        
        // Get all accounts for this organization with relationships
        $query = Account::where('organization_id', $organizationId)
            ->with(['accountType', 'branch'])
            ->orderBy('created_at', 'desc');
        
        // Apply branch filter if provided
        $selectedBranchId = $request->get('branch_id');
        if ($selectedBranchId) {
            if ($selectedBranchId === 'main') {
                // Show organization-level accounts (no specific branch)
                $query->whereNull('branch_id');
            } else {
                // Show accounts for specific branch
                $query->where('branch_id', $selectedBranchId);
            }
        }
        
        $accounts = $query->get();
        
        // Calculate statistics
        $totalAccounts = $accounts->count();
        $totalBalance = $accounts->sum('balance');
        
        // Get account type statistics
        $accountTypeStats = $accounts->groupBy('accountType.name')->map(function ($group) {
            return [
                'count' => $group->count(),
                'balance' => $group->sum('balance')
            ];
        });
        
        return view('accounts.branch-accounts', compact(
            'accounts', 
            'branches', 
            'selectedBranchId', 
            'totalAccounts', 
            'totalBalance', 
            'accountTypeStats'
        ));
    }

    /**
     * Show real accounts (MNO/Bank integration)
     */
    public function realAccounts()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get all real accounts that have mappings to accounts in this organization
        $realAccounts = \App\Models\RealAccount::whereHas('mappedAccounts', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })
        ->with(['mappedAccounts.accountType', 'mappedAccounts.branch'])
        ->orderBy('created_at', 'desc')
        ->get();
        
        // Calculate statistics
        $totalRealAccounts = $realAccounts->count();
        $totalBalance = $realAccounts->sum('last_balance');
        
        // Get provider type statistics
        $providerStats = $realAccounts->groupBy('provider_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'balance' => $group->sum('last_balance')
            ];
        });
        
        return view('accounts.real-accounts', compact(
            'realAccounts', 
            'totalRealAccounts', 
            'totalBalance', 
            'providerStats'
        ));
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        $organizations = Organization::where('id', $organizationId)->get();
        $branches = Branch::where('organization_id', $organizationId)->get();
        
        // Get the 5 main accounts as parent account options (same logic as mainAccounts page)
        $accountTypes = \App\Models\AccountType::whereIn('name', ['Assets', 'Revenue', 'Liability', 'Equity', 'Expense'])
            ->orderBy('name')
            ->get();

        // Get the HQ branch for this organization (same logic as mainAccounts page)
        $hqBranch = \App\Models\Branch::where('organization_id', $organizationId)
            ->where('is_hq', true)
            ->first();

        if (!$hqBranch) {
            // Fallback: get the main branch (first branch)
            $hqBranch = \App\Models\Branch::where('organization_id', $organizationId)->first();
        }

        // Fetch one main account per AccountType (same logic as mainAccounts page)
        $parentAccounts = collect();
        
        foreach ($accountTypes as $accountType) {
            $account = Account::where('organization_id', $organizationId)
                ->where('account_type_id', $accountType->id)
                ->where(function($query) use ($hqBranch) {
                    // First try HQ branch accounts (if exists and branch_id matches)
                    if ($hqBranch) {
                        $query->where(function($subQuery) use ($hqBranch) {
                            $subQuery->where('branch_id', $hqBranch->id)
                                    ->where(function($metaQuery) {
                                        $metaQuery->whereJsonContains('metadata->is_hq_account', true)
                                                ->orWhereJsonContains('metadata->account_type', 'main_category');
                                    });
                        });
                    }
                    
                    // Fallback to organization-level main accounts
                    $query->orWhere(function($subQuery) {
                        $subQuery->where('branch_id', null)
                                 ->whereJsonContains('metadata->account_type', 'main_category');
                    });
                })
                ->first();
                
            if ($account) {
                $parentAccounts->push($account);
            }
        }

        return view('accounts.create', compact('organizations', 'branches', 'parentAccounts', 'organizationId', 'hqBranch'));
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'parent_account_id' => 'required|exists:accounts,id',
            'opening_balance' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended,closed',
            'account_classification' => 'required|in:internal,external',
            'external_account_type' => 'nullable|required_if:account_classification,external|in:receiver,giver',
        ]);

        // Automatically set account_type_id based on parent account
        $parentAccount = Account::find($validated['parent_account_id']);
        $validated['account_type_id'] = $parentAccount->account_type_id;

        // Enforce default organization to authenticated user's organization
        $validated['organization_id'] = auth()->user()->organization_id ?? $validated['organization_id'];

        // Set external_account_type to null for internal accounts
        if ($validated['account_classification'] === 'internal') {
            $validated['external_account_type'] = null;
        }

        // Ensure selected branch belongs to organization if provided
        if (!empty($validated['branch_id'])) {
            $branchBelongs = Branch::where('id', $validated['branch_id'])
                ->where('organization_id', $validated['organization_id'])
                ->exists();
            abort_unless($branchBelongs, 422, 'Selected branch does not belong to your organization');
        }

        // Generate unique account number
        $accountType = AccountType::find($validated['account_type_id']);
        $validated['account_number'] = $this->generateAccountNumber(
            $accountType->code, 
            $validated['organization_id'], 
            $validated['branch_id'],
            $validated['account_classification']
        );
        $validated['balance'] = $validated['opening_balance'];
        $validated['opening_date'] = now();

        $account = Account::create($validated);

        // Record opening balance transaction if there's an opening balance
       // (Skip ledger creation for test accounts)
        if ($account->opening_balance > 0 && !str_contains(strtoupper($account->account_number), 'TEST') && !str_contains(strtoupper($account->name), 'TEST')) {
            $accountType = $account->accountType;
            // Determine transaction type based on account category
            $transactionType = in_array($accountType->category, ['asset', 'expense']) ? 'debit' : 'credit';
            
            \App\Models\GeneralLedger::create([
                'organization_id' => $account->organization_id,
                'branch_id' => $account->branch_id,
                'transaction_id' => 'OPEN-' . $account->account_number . '-' . now()->format('YmdHis'),
                'transaction_date' => now()->toDateString(),
                'account_id' => $account->id,
                'transaction_type' => $transactionType,
                'amount' => $account->opening_balance,
                'currency' => $account->currency,
                'description' => 'Opening balance for ' . $account->name,
                'reference_type' => 'opening_balance',
                'reference_id' => $account->id,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(), // Auto-approved for account creation
                'approved_at' => now(),
                'balance_after' => $account->balance,
                'metadata' => [
                    'account_creation' => true,
                    'opening_balance' => true,
                ],
            ]);
        } else {
            // Record zero-amount ledger transaction for audit trail when no opening balance
            \App\Models\GeneralLedger::create([
                'organization_id' => $account->organization_id,
                'branch_id' => $account->branch_id,
                'transaction_id' => 'ACCT-OPEN-' . now()->format('YmdHis') . '-' . $account->id,
                'transaction_date' => now()->toDateString(),
                'account_id' => $account->id,
                'transaction_type' => 'credit',
                'amount' => 0,
                'currency' => $account->currency,
                'description' => 'Account opened with zero opening balance',
                'reference_type' => 'opening_balance',
                'reference_id' => $account->id,
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'balance_after' => $account->balance,
                'metadata' => [
                    'account_creation' => true,
                    'zero_opening_balance' => true,
                ],
            ]);
        }

        SystemLog::log(
            'account_created',
            "Account created: {$account->name} ({$account->account_number})",
            'info',
            $account,
            auth()->id()
        );

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
        
        } catch (\Exception $e) {
            \Log::error('Account creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Account creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified account
     */
    public function show(Account $account)
    {
        $account->load(['accountType', 'organization', 'branch', 'mappedRealAccounts']);
        
        // Get recent transactions for this account from general ledger
        $recentTransactions = \App\Models\GeneralLedger::where('account_id', $account->id)
            ->with(['branch'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('accounts.show', compact('account', 'recentTransactions'));
    }

    /**
     * List sub-accounts under a main category across branches with balances
     */
    public function subAccountsByCategory(Account $account)
    {
        $organizationId = auth()->user()->organization_id ?? $account->organization_id;

        // Ensure provided account is a main category account
        abort_if(!is_null($account->parent_account_id), 404);

        $subAccounts = Account::with(['branch'])
            ->where('organization_id', $organizationId)
            ->where('parent_account_id', $account->id)
            ->orderBy('branch_id')
            ->get();

        $totalBalance = $subAccounts->sum('balance');

        return view('accounts.subaccounts', [
            'category' => $account,
            'subAccounts' => $subAccounts,
            'totalBalance' => $totalBalance,
        ]);
    }

    /**
     * Show the form for editing the account
     */
    public function edit(Account $account)
    {
        $account->load(['accountType', 'organization', 'branch']);
        return view('accounts.edit', compact('account'));
    }

    /**
     * Update the specified account
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended,closed',
        ]);

        $account->update($validated);

        SystemLog::log(
            'account_updated',
            "Account updated: {$account->name} ({$account->account_number})",
            'info',
            $account,
            auth()->id()
        );

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    /**
     * Disable the specified account
     */
    public function disable(Account $account)
    {
        // Verify the account belongs to the user's organization
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        if ($account->organization_id !== $organizationId) {
            return redirect()->back()->with('error', 'You do not have permission to modify this account.');
        }

        // Check if this is a status change (from branch accounts page) or soft delete
        $request = request();
        if ($request->has('action') && $request->get('action') === 'status_change') {
            // Change status to inactive instead of soft delete
            $account->update(['status' => 'inactive']);
            
            SystemLog::log(
                'account_status_changed',
                "Account status changed to inactive: {$account->name} ({$account->account_number})",
                'warning',
                $account,
                auth()->id()
            );
        } else {
            // Original behavior - soft delete
            $account->delete(); // Soft delete
            
            SystemLog::log(
                'account_disabled',
                "Account disabled: {$account->name} ({$account->account_number})",
                'warning',
                $account,
                auth()->id()
            );
        }

        return redirect()->back()->with('success', 'Account has been disabled successfully.');
    }

    /**
     * Show form to create real account
     */
    public function createRealAccount(Account $account)
    {
        return view('accounts.create-real-account', compact('account'));
    }

    /**
     * Store a newly created real account
     */
    public function storeRealAccount(Request $request, Account $account)
    {
        $validated = $request->validate([
            'provider_type' => 'required|in:mno,bank,payment_gateway',
            'provider_name' => 'required|string|max:255',
            'external_account_id' => 'required|string|max:255',
            'external_account_name' => 'nullable|string|max:255',
            'api_endpoint' => 'nullable|url',
            'api_credentials' => 'nullable|array',
            'provider_metadata' => 'nullable|array',
        ]);

        $validated['account_id'] = $account->id;
        $validated['sync_status'] = 'pending';

        $realAccount = RealAccount::create($validated);

        SystemLog::log(
            'real_account_created',
            "Real account created: {$realAccount->provider_name} for {$account->name}",
            'info',
            $realAccount,
            auth()->id()
        );

        return redirect()->route('accounts.real-accounts')->with('success', 'Real account created successfully.');
    }

    /**
     * Sync real account balance
     */
    public function syncBalance(RealAccount $realAccount)
    {
        try {
            // This would typically call the external API
            // For now, we'll simulate a successful sync
            $realAccount->update([
                'last_balance' => $realAccount->last_balance + rand(100, 1000), // Simulate balance change
                'last_sync_at' => now(),
                'sync_status' => 'success',
                'sync_error_message' => null,
            ]);

            SystemLog::log(
                'balance_synced',
                "Balance synced for {$realAccount->provider_name}: {$realAccount->formatted_last_balance}",
                'info',
                $realAccount,
                auth()->id()
            );

            return redirect()->back()->with('success', 'Balance synced successfully.');
        } catch (\Exception $e) {
            $realAccount->update([
                'sync_status' => 'failed',
                'sync_error_message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to sync balance: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique account number
     */
    private function generateAccountNumber(string $accountTypeCode, int $organizationId, ?int $branchId = null, string $accountClassification = 'internal'): string
    {
        // Use "Ext" prefix for external accounts, otherwise use account type code
        $prefix = $accountClassification === 'external' ? 'EXT' : strtoupper($accountTypeCode);
        $orgCode = str_pad($organizationId, 3, '0', STR_PAD_LEFT);
        $branchCode = $branchId ? str_pad($branchId, 3, '0', STR_PAD_LEFT) : '000';
        $timestamp = now()->format('Ymd');
        $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $orgCode . '-' . $branchCode . '-' . $timestamp . '-' . $random;
    }

    /**
     * Display general ledger statement
     */
    public function generalLedger(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get general ledger entries with filters
        $query = \App\Models\GeneralLedger::where('organization_id', $organizationId)
            ->with(['account', 'creator']);

        // Apply filters
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transaction_date', [$request->date_from, $request->date_to]);
        }

        $entries = $query->latest('transaction_date')->latest('id')->paginate(50);
        
        // Get accounts for filtering
        $accounts = Account::where('organization_id', $organizationId)->get();
        
        // Get summary statistics
        $totalDebits = \App\Models\GeneralLedger::where('organization_id', $organizationId)
            ->where('transaction_type', 'debit')
            ->sum('amount');
            
        $totalCredits = \App\Models\GeneralLedger::where('organization_id', $organizationId)
            ->where('transaction_type', 'credit')
            ->sum('amount');
            
        $netBalance = $totalCredits - $totalDebits;

        return view('accounts.general-ledger', compact('entries', 'accounts', 'totalDebits', 'totalCredits', 'netBalance'));
    }

    /**
     * Show form to map account to real account
     */
    public function mapRealAccount(Account $account)
    {
        $organizationId = auth()->user()->organization_id ?? $account->organization_id;
        
        // Get available real accounts for this organization
        $realAccounts = RealAccount::whereHas('account', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->active()->get();

        return view('accounts.map-real-account', compact('account', 'realAccounts'));
    }

    /**
     * Store account to real account mapping
     */
    public function storeRealAccountMapping(Request $request, Account $account)
    {
        $validated = $request->validate([
            'real_account_id' => 'nullable|exists:real_accounts,id',
            'mapping_description' => 'nullable|string|max:500',
        ]);

        $account->update($validated);

        // Log the action
        SystemLog::log(
            'account_real_mapping',
            "Real account mapping updated for {$account->name} ({$account->account_number})",
            'info',
            $account,
            auth()->id()
        );

        return redirect()->route('accounts.show', $account)
            ->with('success', 'Account mapping updated successfully.');
    }

    /**
     * Show mapped accounts for current organization
     */
    public function mappedAccounts()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $mappedAccounts = Account::with(['branch', 'mappedRealAccount', 'accountType'])
            ->where('organization_id', $organizationId)
            ->whereNotNull('real_account_id')
            ->orderBy('branch_id')
            ->get();

        $totalBalance = $mappedAccounts->sum('balance');

        return view('accounts.mapped-accounts', compact('mappedAccounts', 'totalBalance'));
    }

    /**
     * Display external accounts with balances and transactions
     */
    public function externalAccounts()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get external accounts grouped by type
        $externalAccounts = Account::where('organization_id', $organizationId)
            ->where('account_classification', 'external')
            ->with(['accountType', 'branch'])
            ->orderBy('external_account_type')
            ->orderBy('name')
            ->get()
            ->groupBy('external_account_type');

        // Get transactions for external accounts
        $externalAccountIds = Account::where('organization_id', $organizationId)
            ->where('account_classification', 'external')
            ->pluck('id');

        $latestTransactions = \App\Models\GeneralLedger::whereIn('account_id', $externalAccountIds)
            ->with(['account.accountType', 'branch'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Calculate totals
        $totalExternalAccounts = $externalAccountIds->count();
        $totalExternalBalance = Account::where('organization_id', $organizationId)
            ->where('account_classification', 'external')
            ->sum('balance');
        
        $receiverAccounts = $externalAccounts->get('receiver', collect());
        $giverAccounts = $externalAccounts->get('giver', collect());
        
        $receiverBalance = $receiverAccounts->sum('balance');
        $giverBalance = $giverAccounts->sum('balance');

        return view('accounts.external', compact(
            'externalAccounts',
            'latestTransactions',
            'totalExternalAccounts',
            'totalExternalBalance',
            'receiverAccounts',
            'giverAccounts',
            'receiverBalance',
            'giverBalance'
        ));
    }

    /**
     * Enable an account
     */
    public function enable(Account $account)
    {
        // Verify the account belongs to the user's organization
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        if ($account->organization_id !== $organizationId) {
            return redirect()->back()->with('error', 'You do not have permission to modify this account.');
        }

        $account->update(['status' => 'active']);

        // Log the action
        SystemLog::log(
            'account_enabled',
            "Account {$account->name} ({$account->account_number}) has been enabled",
            'info',
            $account,
            auth()->id()
        );

        return redirect()->back()->with('success', 'Account has been enabled successfully.');
    }

}
