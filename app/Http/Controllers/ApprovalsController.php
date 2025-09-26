<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Approval;
use App\Models\Loan;
use App\Models\FundTransfer;
use App\Models\AccountRecharge;
use App\Models\ExpenseRequest;
use App\Models\Organization;

class ApprovalsController extends Controller
{
    /**
     * Display pending approvals
     */
    public function pending()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get all pending approvals
        $pendingApprovals = Approval::where('status', 'pending')
            ->whereHas('requester', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->with(['requester', 'approver', 'reference'])
            ->latest()
            ->get();

        // Get statistics
        $pendingLoans = Loan::where('organization_id', $organizationId)
            ->where('status', 'pending')
            ->count();
            
        $pendingFundTransfers = FundTransfer::whereHas('fromAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'pending')->count();
        
        $pendingAccountRecharges = AccountRecharge::whereHas('mainAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'pending')->count();

        $totalPending = $pendingLoans + $pendingFundTransfers + $pendingAccountRecharges;

        return view('approvals.pending', compact(
            'pendingApprovals', 
            'pendingLoans', 
            'pendingFundTransfers', 
            'pendingAccountRecharges', 
            'totalPending'
        ));
    }

    /**
     * Display loan approvals
     */
    public function loans()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $pendingLoans = Loan::where('organization_id', $organizationId)
            ->where('status', 'pending')
            ->with(['client', 'loanProduct', 'branch', 'loanOfficer'])
            ->latest()
            ->get();

        $recentApprovals = Loan::where('organization_id', $organizationId)
            ->where('approval_status', 'approved')
            ->whereNotNull('approval_date')
            ->with(['client', 'loanProduct', 'branch', 'approvedBy'])
            ->latest('approval_date')
            ->take(10)
            ->get();

        return view('approvals.loans', compact('pendingLoans', 'recentApprovals'));
    }

    /**
     * Display fund transfer approvals
     */
    public function fundTransfers()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $pendingTransfers = FundTransfer::whereHas('fromAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'pending')
        ->with(['fromAccount', 'toAccount', 'requester', 'approver'])
        ->latest()
        ->get();

        $recentTransfers = FundTransfer::whereHas('fromAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->whereIn('status', ['approved', 'completed', 'rejected'])
        ->with(['fromAccount', 'toAccount', 'requester', 'approver'])
        ->latest()
        ->take(10)
        ->get();

        return view('approvals.fund-transfers', compact('pendingTransfers', 'recentTransfers'));
    }

    /**
     * Display account recharge approvals
     */
    public function accountRecharges()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $pendingRecharges = AccountRecharge::whereHas('mainAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->where('status', 'pending')
        ->with(['mainAccount', 'requester', 'approver'])
        ->latest()
        ->get();

        $recentRecharges = AccountRecharge::whereHas('mainAccount', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })->whereIn('status', ['approved', 'completed', 'rejected'])
        ->with(['mainAccount', 'requester', 'approver'])
        ->latest()
        ->take(10)
        ->get();

        return view('approvals.account-recharges', compact('pendingRecharges', 'recentRecharges'));
    }

    /**
     * Display approval history
     */
    public function history()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $approvals = Approval::whereHas('requester', function($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })
        ->with(['requester', 'approver', 'reference'])
        ->latest()
        ->paginate(20);

        return view('approvals.history', compact('approvals'));
    }

    /**
     * Approve a loan request
     */
    public function approve(Request $request, $loanId)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:500',
            'approved_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $loan = Loan::findOrFail($loanId);
            
            // Check if loan can be approved
            if ($loan->status !== 'pending') {
                return redirect()->back()->with('error', 'This loan cannot be approved in its current status.');
            }

            // Update loan with approval details
            $approvedAmount = $request->approved_amount ?? $loan->loan_amount;
            
            $loan->update([
                'status' => 'approved',
                'approval_status' => 'approved',
                'approved_amount' => $approvedAmount,
                'approval_date' => now(),
                'approved_by' => auth()->id(),
                'approval_notes' => $request->approval_notes,
            ]);

            // Create or update approval record
            $approval = Approval::where('reference_type', 'App\\Models\\Loan')
                ->where('reference_id', $loan->id)
                ->first();
            
            if ($approval) {
                $approval->update([
                    'status' => 'approved',
                    'approver_id' => auth()->id(),
                    'approval_notes' => $request->approval_notes,
                    'approved_at' => now(),
                ]);
            } else {
                Approval::create([
                    'approval_number' => Approval::generateApprovalNumber(),
                    'organization_id' => $loan->organization_id,
                    'branch_id' => $loan->branch_id,
                    'type' => 'loan_approval',
                    'reference_type' => 'App\\Models\\Loan',
                    'reference_id' => $loan->id,
                    'requested_by' => $loan->loan_officer_id,
                    'approver_id' => auth()->id(),
                    'status' => 'approved',
                    'description' => "Loan approval for {$loan->loan_number}",
                    'approval_notes' => $request->approval_notes,
                    'approved_at' => now(),
                ]);
            }

            return redirect()->back()->with('success', 'Loan approved successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while approving the loan: ' . $e->getMessage());
        }
    }

    /**
     * Reject a loan request
     */
    public function reject(Request $request, $loanId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $loan = Loan::findOrFail($loanId);
            
            // Check if loan can be rejected
            if ($loan->status !== 'pending') {
                return redirect()->back()->with('error', 'This loan cannot be rejected in its current status.');
            }

            // Update loan with rejection details
            $loan->update([
                'status' => 'rejected',
                'approval_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => auth()->id(),
            ]);

            // Create or update approval record
            $approval = Approval::where('reference_type', 'App\\Models\\Loan')
                ->where('reference_id', $loan->id)
                ->first();
            
            if ($approval) {
                $approval->update([
                    'status' => 'rejected',
                    'approver_id' => auth()->id(),
                    'approval_notes' => $request->rejection_reason,
                    'approved_at' => now(),
                ]);
            } else {
                Approval::create([
                    'approval_number' => Approval::generateApprovalNumber(),
                    'organization_id' => $loan->organization_id,
                    'branch_id' => $loan->branch_id,
                    'type' => 'loan_approval',
                    'reference_type' => 'App\\Models\\Loan',
                    'reference_id' => $loan->id,
                    'requested_by' => $loan->loan_officer_id,
                    'approver_id' => auth()->id(),
                    'status' => 'rejected',
                    'description' => "Loan rejection for {$loan->loan_number}",
                    'approval_notes' => $request->rejection_reason,
                    'approved_at' => now(),
                ]);
            }

            return redirect()->back()->with('success', 'Loan rejected successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while rejecting the loan: ' . $e->getMessage());
        }
    }

    /**
     * Display expense approvals
     */
    public function expenses()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get pending expense requests
        $pendingExpenses = ExpenseRequest::where('organization_id', $organizationId)
            ->where('status', 'pending')
            ->with(['requester', 'expenseAccount', 'paymentAccount', 'branch'])
            ->latest()
            ->get();

        // Get recently approved expenses
        $recentApprovals = ExpenseRequest::where('organization_id', $organizationId)
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->with(['requester', 'approver', 'expenseAccount', 'paymentAccount'])
            ->latest('approved_at')
            ->take(10)
            ->get();

        // Get statistics
        $totalPending = $pendingExpenses->count();
        $totalApproved = ExpenseRequest::where('organization_id', $organizationId)
            ->where('status', 'approved')
            ->count();
        $totalCompleted = ExpenseRequest::where('organization_id', $organizationId)
            ->where('status', 'completed')
            ->count();
        $totalRejected = ExpenseRequest::where('organization_id', $organizationId)
            ->where('status', 'rejected')
            ->count();

        return view('approvals.expenses', compact(
            'pendingExpenses',
            'recentApprovals',
            'totalPending',
            'totalApproved',
            'totalCompleted',
            'totalRejected'
        ));
    }

    /**
     * Approve an expense request
     */
    public function approveExpense(Request $request, ExpenseRequest $expenseRequest)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:500',
        ]);

        try {
            if (!$expenseRequest->canBeApproved()) {
                return redirect()->back()->with('error', 'This expense request cannot be approved.');
            }

            // Update expense request
            $expenseRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
            ]);

            // Update approval record
            $approval = Approval::where('reference_type', 'ExpenseRequest')
                ->where('reference_id', $expenseRequest->id)
                ->first();
            
            if ($approval) {
                $approval->update([
                    'status' => 'approved',
                    'approver_id' => auth()->id(),
                    'notes' => $request->approval_notes,
                ]);
            }

            return redirect()->back()->with('success', 'Expense request approved successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while approving the expense: ' . $e->getMessage());
        }
    }

    /**
     * Reject an expense request
     */
    public function rejectExpense(Request $request, ExpenseRequest $expenseRequest)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            if (!$expenseRequest->canBeRejected()) {
                return redirect()->back()->with('error', 'This expense request cannot be rejected.');
            }

            // Update expense request
            $expenseRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Update approval record
            $approval = Approval::where('reference_type', 'ExpenseRequest')
                ->where('reference_id', $expenseRequest->id)
                ->first();
            
            if ($approval) {
                $approval->update([
                    'status' => 'rejected',
                    'approver_id' => auth()->id(),
                    'notes' => $request->rejection_reason,
                ]);
            }

            return redirect()->back()->with('success', 'Expense request rejected successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while rejecting the expense: ' . $e->getMessage());
        }
    }
}