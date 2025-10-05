<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FundTransfer;
use App\Models\AccountRecharge;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\Approval;

class PaymentController extends Controller
{
    /**
     * Display the payments dashboard
     */
    public function index()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get recent fund transfers
        $recentTransfers = FundTransfer::whereHas('fromAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->with(['fromAccount', 'toAccount', 'requester', 'approver'])
          ->latest()
          ->take(5)
          ->get();

        // Get recent account recharges
        $recentRecharges = AccountRecharge::whereHas('mainAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->with(['mainAccount', 'requester', 'approver'])
          ->latest()
          ->take(5)
          ->get();

        return view('payments.index', compact('recentTransfers', 'recentRecharges'));
    }

    /**
     * Show the form for creating a fund transfer
     */
    public function createFundTransfer()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get all branches for the organization
        $branches = Branch::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('is_hq', 'desc') // HQ first
            ->orderBy('name')
            ->get();

        // Get all accounts grouped by branch
        $accountsByBranch = [];
        $accountingService = app(\App\Services\AccountingService::class);

        foreach ($branches as $branch) {
            $accounts = Account::where('organization_id', $organizationId)
                ->where('branch_id', $branch->id)
                ->where('status', 'active')
                ->where('account_classification', 'internal') // Only internal accounts for fund transfers
                ->with(['accountType', 'branch'])
                ->get();

            // Calculate accurate balances
            $accounts = $accounts->map(function($account) use ($accountingService) {
                $account->calculated_balance = $accountingService->calculateAccountBalance($account);
                return $account;
            });

            $accountsByBranch[$branch->id] = $accounts;
        }

        // Get HQ accounts (main organization accounts)
        $hqAccounts = Account::where('organization_id', $organizationId)
            ->whereNull('branch_id')
            ->where('status', 'active')
            ->where('account_classification', 'internal')
            ->with(['accountType'])
            ->get();

        // Calculate accurate balances for HQ accounts
        $hqAccounts = $hqAccounts->map(function($account) use ($accountingService) {
            $account->calculated_balance = $accountingService->calculateAccountBalance($account);
            return $account;
        });

        $accountsByBranch['hq'] = $hqAccounts;

        return view('payments.create-fund-transfer', compact('branches', 'accountsByBranch'));
    }

    /**
     * Store a newly created fund transfer
     */
    public function storeFundTransfer(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'required|string',
            'from_account_id' => 'required|exists:accounts,id',
            'to_branch_id' => 'required|string',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ], [
            'from_branch_id.required' => 'Source branch is required.',
            'from_account_id.required' => 'Source account is required.',
            'from_account_id.exists' => 'Selected source account does not exist.',
            'to_branch_id.required' => 'Destination branch is required.',
            'to_account_id.required' => 'Destination account is required.',
            'to_account_id.exists' => 'Selected destination account does not exist.',
            'to_account_id.different' => 'Cannot transfer funds to the same account.',
            'amount.required' => 'Transfer amount is required.',
            'amount.numeric' => 'Transfer amount must be a valid number.',
            'amount.min' => 'Transfer amount must be greater than 0.',
            'description.required' => 'Transfer description is required.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ]);

        try {
            $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
            
            // Validate accounts belong to the same organization and are internal accounts
            $fromAccount = Account::where('id', $request->from_account_id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->where('account_classification', 'internal')
                ->first();
            
            $toAccount = Account::where('id', $request->to_account_id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->where('account_classification', 'internal')
                ->first();

            if (!$fromAccount) {
                return redirect()->back()->with('error', 'Source account not found, inactive, or not an internal account.');
            }

            if (!$toAccount) {
                return redirect()->back()->with('error', 'Destination account not found, inactive, or not an internal account.');
            }

            // Validate branch selection matches account branches
            if ($request->from_branch_id === 'hq') {
                if ($fromAccount->branch_id !== null) {
                    return redirect()->back()->with('error', 'Selected source account does not belong to HQ branch.');
                }
            } else {
                if ($fromAccount->branch_id != $request->from_branch_id) {
                    return redirect()->back()->with('error', 'Selected source account does not belong to the selected branch.');
                }
            }

            if ($request->to_branch_id === 'hq') {
                if ($toAccount->branch_id !== null) {
                    return redirect()->back()->with('error', 'Selected destination account does not belong to HQ branch.');
                }
            } else {
                if ($toAccount->branch_id != $request->to_branch_id) {
                    return redirect()->back()->with('error', 'Selected destination account does not belong to the selected branch.');
                }
            }

            // Check if source account has sufficient balance using AccountingService
            $accountingService = app(\App\Services\AccountingService::class);
            $currentBalance = $accountingService->calculateAccountBalance($fromAccount);
            
            if ($currentBalance < $request->amount) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Insufficient balance in source account. Available balance: TZS ' . number_format($currentBalance, 2));
            }

            // Generate transfer number
            $transferNumber = 'FT-' . date('Ymd') . '-' . str_pad(FundTransfer::count() + 1, 4, '0', STR_PAD_LEFT);

            $fundTransfer = FundTransfer::create([
                'transfer_number' => $transferNumber,
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'amount' => $request->amount,
                'currency' => 'TZS',
                'description' => $request->description,
                'status' => 'pending', // Will remain pending until approved
                'requested_by' => auth()->id(),
                'metadata' => [
                    'from_branch_id' => $request->from_branch_id,
                    'to_branch_id' => $request->to_branch_id,
                    'request_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'available_balance' => $currentBalance,
                ]
            ]);

            // Create approval request
            $approver = $this->getApprover($organizationId);
            if ($approver) {
                Approval::create([
                    'approval_number' => 'APP-FT-' . str_pad($fundTransfer->id, 6, '0', STR_PAD_LEFT),
                    'type' => 'fund_transfer',
                    'reference_type' => 'FundTransfer',
                    'reference_id' => $fundTransfer->id,
                    'requested_by' => auth()->id(),
                    'approver_id' => $approver->id,
                    'status' => 'pending',
                    'description' => "Fund transfer approval: {$fundTransfer->transfer_number} - From: {$fundTransfer->fromAccount->name} To: {$fundTransfer->toAccount->name}",
                    'metadata' => [
                        'amount' => $request->amount,
                        'from_account' => $fundTransfer->fromAccount->name,
                        'from_branch' => $fundTransfer->fromAccount->branch ? $fundTransfer->fromAccount->branch->name : 'HQ',
                        'to_account' => $fundTransfer->toAccount->name,
                        'to_branch' => $fundTransfer->toAccount->branch ? $fundTransfer->toAccount->branch->name : 'HQ',
                        'transfer_number' => $fundTransfer->transfer_number,
                    ]
                ]);
            }

            return redirect()->route('payments')->with('success', 'Fund transfer request submitted successfully. It will be reviewed for approval before execution.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while creating the fund transfer: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating an account recharge
     */
    public function createAccountRecharge()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get giver accounts (external accounts that represent money coming into the system)
        // Giver accounts should be organization-level accounts (not branch-specific)
        $giverAccounts = Account::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->where('account_classification', 'external')
            ->where('external_account_type', 'giver')
            ->whereNull('branch_id') // Organization-level accounts only
            ->with(['accountType'])
            ->get();

        // Get the 5 main organization accounts for this specific organization
        // Find one account for each of the 5 main account types (Assets, Revenue, Liability, Equity, Expense)
        $accountTypes = \App\Models\AccountType::whereIn('name', ['Assets', 'Revenue', 'Liability', 'Equity', 'Expense'])
            ->orderBy('name')
            ->get();

        $capitalAccounts = collect();
        foreach ($accountTypes as $accountType) {
            $account = Account::where('organization_id', $organizationId)
                ->where('status', 'active')
                ->where('account_classification', 'internal')
                ->where('account_type_id', $accountType->id)
                ->whereNull('branch_id')
                ->whereJsonContains('metadata->account_type', 'main_category')
                ->with(['accountType'])
                ->first();
                
            if ($account) {
                $capitalAccounts->push($account);
            }
        }

        // Calculate balances for giver accounts (these should be negative/credit balances)
        $accountingService = app(\App\Services\AccountingService::class);
        $giverAccounts = $giverAccounts->map(function($account) use ($accountingService) {
            $account->calculated_balance = $accountingService->calculateAccountBalance($account);
            return $account;
        });

        $capitalAccounts = $capitalAccounts->map(function($account) use ($accountingService) {
            $account->calculated_balance = $accountingService->calculateAccountBalance($account);
            return $account;
        });


        return view('payments.create-account-recharge', compact('giverAccounts', 'capitalAccounts'));
    }

    /**
     * Store a newly created account recharge
     */
    public function storeAccountRecharge(Request $request)
    {
        $request->validate([
            'giver_account_id' => 'required|exists:accounts,id',
            'capital_account_id' => 'required|exists:accounts,id',
            'recharge_amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ], [
            'giver_account_id.required' => 'Source giver account is required.',
            'giver_account_id.exists' => 'Selected giver account does not exist.',
            'capital_account_id.required' => 'Destination capital account is required.',
            'capital_account_id.exists' => 'Selected capital account does not exist.',
            'recharge_amount.required' => 'Capital injection amount is required.',
            'recharge_amount.numeric' => 'Amount must be a valid number.',
            'recharge_amount.min' => 'Amount must be greater than 0.',
            'description.required' => 'Description is required.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ]);

        try {
            $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
            
            // Validate accounts belong to the same organization
            $giverAccount = Account::where('id', $request->giver_account_id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->where('account_classification', 'external')
                ->where('external_account_type', 'giver')
                ->first();
            
            $capitalAccount = Account::where('id', $request->capital_account_id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->where('account_classification', 'internal')
                ->whereNull('branch_id')
                ->first();

            if (!$giverAccount) {
                return redirect()->back()->with('error', 'Selected giver account not found, inactive, or not a valid giver account.');
            }

            if (!$capitalAccount) {
                return redirect()->back()->with('error', 'Selected capital account not found, inactive, or not a valid capital account.');
            }

            // Prevent using the same account for both source and destination
            if ($giverAccount->id === $capitalAccount->id) {
                return redirect()->back()->with('error', 'Source and destination accounts cannot be the same.');
            }

            // Generate recharge number
            $rechargeNumber = 'RC-' . date('Ymd') . '-' . str_pad(AccountRecharge::count() + 1, 4, '0', STR_PAD_LEFT);

            $accountRecharge = AccountRecharge::create([
                'recharge_number' => $rechargeNumber,
                'main_account_id' => $request->capital_account_id, // Keep field name for compatibility
                'recharge_amount' => $request->recharge_amount,
                'currency' => 'TZS',
                'description' => $request->description,
                'status' => 'pending', // Will remain pending until approved
                'requested_by' => auth()->id(),
                'distribution_plan' => null, // No distribution plan needed for capital injection
                'metadata' => [
                    'giver_account_id' => $request->giver_account_id,
                    'capital_account_id' => $request->capital_account_id,
                    'request_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ]);

            // Create approval request
            $approver = $this->getApprover($organizationId);
            if ($approver) {
                Approval::create([
                    'approval_number' => 'APP-RC-' . str_pad($accountRecharge->id, 6, '0', STR_PAD_LEFT),
                    'type' => 'account_recharge',
                    'reference_type' => 'AccountRecharge',
                    'reference_id' => $accountRecharge->id,
                    'requested_by' => auth()->id(),
                    'approver_id' => $approver->id,
                    'status' => 'pending',
                    'description' => "Capital injection approval: {$accountRecharge->recharge_number} - From: {$giverAccount->name} To: {$capitalAccount->name}",
                    'metadata' => [
                        'amount' => $request->recharge_amount,
                        'giver_account' => $giverAccount->name,
                        'giver_branch' => $giverAccount->branch ? $giverAccount->branch->name : 'HQ',
                        'capital_account' => $capitalAccount->name,
                        'recharge_number' => $accountRecharge->recharge_number,
                    ]
                ]);
            }

            return redirect()->route('payments')->with('success', 'Capital injection request submitted successfully. It will be reviewed for approval before execution.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while creating the account recharge: ' . $e->getMessage());
        }
    }

    /**
     * Get an approver for the organization
     */
    private function getApprover($organizationId)
    {
        return \App\Models\User::where('organization_id', $organizationId)
            ->where('role', 'manager')
            ->first() ?? \App\Models\User::where('organization_id', $organizationId)->first();
    }
}
