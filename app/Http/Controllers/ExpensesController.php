<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Organization;
use App\Models\Branch;
use App\Models\GeneralLedger;
use App\Models\ExpenseRequest;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpensesController extends Controller
{
    /**
     * Display the expense request form
     */
    public function repayment()
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        
        // Get accounts for the organization
        $accounts = Account::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with(['accountType', 'branch'])
            ->orderBy('name')
            ->get();

        // Get branches for the organization
        $branches = Branch::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('expenses.repayment', compact('accounts', 'branches'));
    }

    /**
     * Store an expense request
     */
    public function storeRepayment(Request $request)
    {
        $request->validate([
            'expense_type' => 'required|string|in:repayment,refund,adjustment,operational,other',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'payment_method' => 'required|string|in:cash,bank_transfer,mobile_money,check,other',
            'reference_number' => 'nullable|string|max:255',
            'expense_account_id' => 'required|exists:accounts,id',
            'payment_account_id' => 'required|exists:accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'expense_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
            $branchId = $request->branch_id ?? Auth::user()->branch_id;

            // Create expense request
            $expenseRequest = ExpenseRequest::create([
                'request_number' => ExpenseRequest::generateRequestNumber(),
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'requested_by' => Auth::id(),
                'expense_type' => $request->expense_type,
                'amount' => $request->amount,
                'description' => $request->description,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'expense_account_id' => $request->expense_account_id,
                'payment_account_id' => $request->payment_account_id,
                'expense_date' => $request->expense_date,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Create approval record
            try {
                $approval = Approval::create([
                    'approval_number' => Approval::generateApprovalNumber(),
                    'organization_id' => $organizationId,
                    'branch_id' => $branchId,
                    'requested_by' => Auth::id(),
                    'approver_id' => null, // Will be assigned by admin
                    'reference_type' => 'ExpenseRequest',
                    'reference_id' => $expenseRequest->id,
                    'type' => 'expense_request',
                    'status' => 'pending',
                    'description' => 'Expense request awaiting approval',
                ]);
            } catch (\Exception $approvalError) {
                // If approval creation fails, delete the expense request and throw the error
                $expenseRequest->delete();
                throw new \Exception('Failed to create approval: ' . $approvalError->getMessage());
            }

            return redirect()->back()->with('success', 'Expense request submitted successfully. Request Number: ' . $expenseRequest->request_number);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while submitting the expense request: ' . $e->getMessage());
        }
    }

    /**
     * Display expense requests (for requesters)
     */
    public function requests(Request $request)
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        $branchId = Auth::user()->branch_id ?? null;

        $query = ExpenseRequest::where('organization_id', $organizationId)
            ->where('requested_by', Auth::id())
            ->with(['expenseAccount', 'paymentAccount', 'branch', 'approver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        $expenseRequests = $query->latest('created_at')->paginate(20);

        // Get statistics
        $totalRequests = ExpenseRequest::where('organization_id', $organizationId)
            ->where('requested_by', Auth::id())
            ->count();
        
        $pendingRequests = ExpenseRequest::where('organization_id', $organizationId)
            ->where('requested_by', Auth::id())
            ->where('status', 'pending')
            ->count();

        $approvedRequests = ExpenseRequest::where('organization_id', $organizationId)
            ->where('requested_by', Auth::id())
            ->where('status', 'approved')
            ->count();

        $totalAmount = ExpenseRequest::where('organization_id', $organizationId)
            ->where('requested_by', Auth::id())
            ->where('status', '!=', 'rejected')
            ->sum('amount');

        return view('expenses.requests', compact('expenseRequests', 'totalRequests', 'pendingRequests', 'approvedRequests', 'totalAmount'));
    }

    /**
     * Display expense history (completed expenses)
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $query = ExpenseRequest::where('organization_id', $organizationId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->with(['expenseAccount.accountType', 'paymentAccount.accountType', 'requester', 'approver']);

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('expense_date', [$request->date_from, $request->date_to]);
        }

        // Filter by expense account
        if ($request->filled('account_id')) {
            $query->where('expense_account_id', $request->account_id);
        }

        $expenses = $query->latest('completed_at')->latest('id')->paginate(20);

        // Get accounts for filter dropdown
        $accounts = Account::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereHas('accountType', function($query) {
                $query->whereIn('name', ['Expense', 'Cost']);
            })
            ->orderBy('name')
            ->get();

        // Get statistics
        $totalExpenses = ExpenseRequest::where('organization_id', $organizationId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->sum('amount');
            
        $thisMonthExpenses = ExpenseRequest::where('organization_id', $organizationId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('amount');

        return view('expenses.history', compact('expenses', 'accounts', 'totalExpenses', 'thisMonthExpenses'));
    }

    /**
     * Complete an approved expense request
     */
    public function complete(Request $request, ExpenseRequest $expenseRequest)
    {
        if (!$expenseRequest->canBeCompleted()) {
            return redirect()->back()->with('error', 'This expense request cannot be completed.');
        }

        $request->validate([
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        try {
            // Store receipt
            $receiptPath = $request->file('receipt')->store('expense-receipts', 'public');
            $receiptFilename = $request->file('receipt')->getClientOriginalName();

            // Update expense request
            $expenseRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
                'receipt_path' => $receiptPath,
                'receipt_filename' => $receiptFilename,
            ]);

            // Record in General Ledger
            $this->recordExpenseInLedger($expenseRequest);

            // Update approval status
            $approval = Approval::where('reference_type', 'ExpenseRequest')
                ->where('reference_id', $expenseRequest->id)
                ->first();
            
            if ($approval) {
                $approval->update(['status' => 'completed']);
            }

            return redirect()->back()->with('success', 'Expense request completed successfully. Receipt uploaded.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while completing the expense: ' . $e->getMessage());
        }
    }

    /**
     * Record expense in general ledger
     */
    private function recordExpenseInLedger(ExpenseRequest $expenseRequest)
    {
        $organizationId = $expenseRequest->organization_id;
        $branchId = $expenseRequest->branch_id;
        
        $transactionId = 'EXP-' . date('Ymd') . '-' . str_pad($expenseRequest->id, 6, '0', STR_PAD_LEFT);
        $now = now();

        // Debit: Expense Account (expense recorded)
        GeneralLedger::create([
            'organization_id' => $organizationId,
            'branch_id' => $branchId,
            'account_id' => $expenseRequest->expense_account_id,
            'transaction_id' => $transactionId,
            'transaction_type' => 'debit',
            'amount' => $expenseRequest->amount,
            'description' => $expenseRequest->description,
            'transaction_date' => $expenseRequest->expense_date,
            'reference_type' => 'ExpenseRequest',
            'reference_id' => $expenseRequest->id,
            'created_by' => $expenseRequest->requested_by,
        ]);

        // Credit: Payment Account (cash/bank paid out)
        GeneralLedger::create([
            'organization_id' => $organizationId,
            'branch_id' => $branchId,
            'account_id' => $expenseRequest->payment_account_id,
            'transaction_id' => $transactionId,
            'transaction_type' => 'credit',
            'amount' => $expenseRequest->amount,
            'description' => $expenseRequest->description,
            'transaction_date' => $expenseRequest->expense_date,
            'reference_type' => 'ExpenseRequest',
            'reference_id' => $expenseRequest->id,
            'created_by' => $expenseRequest->requested_by,
        ]);
    }

    /**
     * Show detailed expense request
     */
    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $organizationId = $user->organization_id;
        $branchId = $user->branch_id;

        $query = ExpenseRequest::with([
                'expenseAccount.accountType',
                'paymentAccount.accountType', 
                'requester',
                'approver',
                'organization',
                'branch'
            ])
            ->where('organization_id', $organizationId);

        // Non-admin users can only see their own branch and own requests
        if (!in_array($user->role, ['super_admin', 'admin', 'manager'])) {
            $query->where('branch_id', $branchId)
                  ->where('requested_by', $user->id);
        }

        $expense = $query->findOrFail($id);

        // Get related approval
        $approval = Approval::where('reference_type', 'ExpenseRequest')
            ->where('reference_id', $expense->id)
            ->first();

        // Get general ledger entries for this expense (if completed)
        $ledgerEntries = collect();
        if ($expense->status === 'completed') {
            $ledgerEntries = GeneralLedger::where('organization_id', $organizationId)
                ->where('branch_id', $branchId)
                ->where('reference_type', 'ExpenseRequest')
                ->where('reference_id', $expense->id)
                ->with(['account.accountType'])
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();
        }

        return view('expenses.show', compact('expense', 'approval', 'ledgerEntries'));
    }

    /**
     * Download receipt
     */
    public function downloadReceipt(ExpenseRequest $expenseRequest)
    {
        if (!$expenseRequest->receipt_path) {
            abort(404, 'Receipt not found');
        }

        return Storage::disk('public')->download($expenseRequest->receipt_path, $expenseRequest->receipt_filename);
    }
}