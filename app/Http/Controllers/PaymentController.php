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
        
        $accounts = Account::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with(['accountType', 'branch'])
            ->get();

        return view('payments.create-fund-transfer', compact('accounts'));
    }

    /**
     * Store a newly created fund transfer
     */
    public function storeFundTransfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ]);

        try {
            $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
            
            // Generate transfer number
            $transferNumber = 'FT-' . date('Ymd') . '-' . str_pad(FundTransfer::count() + 1, 4, '0', STR_PAD_LEFT);

            $fundTransfer = FundTransfer::create([
                'transfer_number' => $transferNumber,
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'amount' => $request->amount,
                'currency' => 'TZS',
                'description' => $request->description,
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'metadata' => [
                    'request_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
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
                    'description' => "Fund transfer approval: {$fundTransfer->transfer_number}",
                    'metadata' => [
                        'amount' => $request->amount,
                        'from_account' => $fundTransfer->fromAccount->name,
                        'to_account' => $fundTransfer->toAccount->name
                    ]
                ]);
            }

            return redirect()->route('payments')->with('success', 'Fund transfer request submitted successfully. It will be reviewed for approval.');
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
        
        $mainAccounts = Account::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereNull('branch_id')
            ->whereHas('accountType', function($query) {
                $query->whereIn('name', ['Assets', 'Cash', 'Bank', 'Investment']);
            })
            ->distinct()
            ->get();

        $branches = Branch::where('organization_id', $organizationId)->get();

        // Prepare branch accounts for JavaScript
        $branchAccounts = $branches->map(function($branch) use ($organizationId) {
            $branchAccount = Account::where('organization_id', $organizationId)
                ->where('branch_id', $branch->id)
                ->whereHas('accountType', function($query) {
                    $query->where('name', 'Liability');
                })
                ->first();
            
            return [
                'id' => $branchAccount ? $branchAccount->id : null,
                'name' => $branchAccount ? $branchAccount->name : null,
                'branch_name' => $branch->name
            ];
        })->filter(function($account) {
            return $account['id'] !== null;
        });

        return view('payments.create-account-recharge', compact('mainAccounts', 'branches', 'branchAccounts'));
    }

    /**
     * Store a newly created account recharge
     */
    public function storeAccountRecharge(Request $request)
    {
        $request->validate([
            'main_account_id' => 'required|exists:accounts,id',
            'recharge_amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'distribution_plan' => 'required|array|min:1',
            'distribution_plan.*.account_id' => 'required|exists:accounts,id',
            'distribution_plan.*.amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
            
            // Generate recharge number
            $rechargeNumber = 'RC-' . date('Ymd') . '-' . str_pad(AccountRecharge::count() + 1, 4, '0', STR_PAD_LEFT);

            $accountRecharge = AccountRecharge::create([
                'recharge_number' => $rechargeNumber,
                'main_account_id' => $request->main_account_id,
                'recharge_amount' => $request->recharge_amount,
                'currency' => 'TZS',
                'description' => $request->description,
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'distribution_plan' => $request->distribution_plan,
                'metadata' => [
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
                    'description' => "Account recharge approval: {$accountRecharge->recharge_number}",
                    'metadata' => [
                        'amount' => $request->recharge_amount,
                        'main_account' => $accountRecharge->mainAccount->name,
                        'distribution_count' => count($request->distribution_plan)
                    ]
                ]);
            }

            return redirect()->route('payments')->with('success', 'Account recharge request submitted successfully. It will be reviewed for approval.');
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
