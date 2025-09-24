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
        
        return view('accounts.index', compact('totalAccounts', 'activeAccounts', 'mainAccounts', 'branchAccounts'));
    }

    /**
     * Show main accounts (organization level)
     */
    public function mainAccounts()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;

        // Fetch main category accounts (no parent) scoped to organization and without branch
        $categories = Account::withCount(['childAccounts as sub_accounts_count' => function($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            }])
            ->withSum(['childAccounts as total_balance' => function($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            }], 'balance')
            ->whereNull('parent_account_id')
            ->whereNull('branch_id')
            ->where('organization_id', $organizationId)
            ->with('accountType')
            ->get();

        return view('accounts.main-accounts', compact('categories'));
    }

    /**
     * Show branch accounts
     */
    public function branchAccounts()
    {
        return view('accounts.branch-accounts');
    }

    /**
     * Show real accounts (MNO/Bank integration)
     */
    public function realAccounts()
    {
        return view('accounts.real-accounts');
    }

    /**
     * Show the form for creating a new account
     */
    public function create()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        $accountTypes = AccountType::all();
        $organizations = Organization::where('id', $organizationId)->get();
        $branches = Branch::where('organization_id', $organizationId)->get();
        $parentAccounts = Account::where('organization_id', $organizationId)
            ->whereNull('branch_id')
            ->whereNull('parent_account_id')
            ->get();

        return view('accounts.create', compact('accountTypes', 'organizations', 'branches', 'parentAccounts', 'organizationId'));
    }

    /**
     * Store a newly created account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_type_id' => 'required|exists:account_types,id',
            'organization_id' => 'required|exists:organizations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'opening_balance' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended,closed',
        ]);

        // Enforce default organization to authenticated user's organization
        $validated['organization_id'] = auth()->user()->organization_id ?? $validated['organization_id'];

        // Ensure selected branch belongs to organization if provided
        if (!empty($validated['branch_id'])) {
            $branchBelongs = Branch::where('id', $validated['branch_id'])
                ->where('organization_id', $validated['organization_id'])
                ->exists();
            abort_unless($branchBelongs, 422, 'Selected branch does not belong to your organization');
        }

        // Generate unique account number
        $accountType = AccountType::find($validated['account_type_id']);
        $validated['account_number'] = $this->generateAccountNumber($accountType->code, $validated['organization_id'], $validated['branch_id']);
        $validated['balance'] = $validated['opening_balance'];
        $validated['opening_date'] = now();

        $account = Account::create($validated);

        // Record zero-amount ledger transaction for creation (audit trail)
        \App\Models\GeneralLedger::create([
            'organization_id' => $account->organization_id,
            'branch_id' => $account->branch_id,
            'transaction_id' => 'ACCT-OPEN-' . now()->format('YmdHis') . '-' . $account->id,
            'transaction_date' => now()->toDateString(),
            'account_id' => $account->id,
            'transaction_type' => 'credit',
            'amount' => 0,
            'currency' => $account->currency,
            'description' => 'Account opened with opening balance recorded separately',
            'reference_type' => Account::class,
            'reference_id' => $account->id,
            'created_by' => auth()->id(),
            'approved_by' => null,
            'approved_at' => null,
            'balance_after' => $account->balance,
        ]);

        SystemLog::log(
            'account_created',
            "Account created: {$account->name} ({$account->account_number})",
            'info',
            $account,
            auth()->id()
        );

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified account
     */
    public function show(Account $account)
    {
        $account->load(['accountType', 'organization', 'branch', 'realAccount']);
        return view('accounts.show', compact('account'));
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
        $account->delete(); // Soft delete

        SystemLog::log(
            'account_disabled',
            "Account disabled: {$account->name} ({$account->account_number})",
            'warning',
            $account,
            auth()->id()
        );

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
    private function generateAccountNumber(string $accountTypeCode, int $organizationId, ?int $branchId = null): string
    {
        $prefix = strtoupper($accountTypeCode);
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
}
