<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Client;
use App\Models\LoanProduct;
use App\Models\Branch;
use App\Models\User;
use App\Models\Organization;
use App\Models\Account;
use Illuminate\Http\Request;

class LoansController extends Controller
{
    /**
     * Display the loan dashboard
     */
    public function dashboard()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        $branchId = auth()->user()->branch_id;

        // Get loan statistics
        $totalLoans = Loan::where('organization_id', $organizationId)->count();
        $activeLoans = Loan::where('organization_id', $organizationId)->where('status', 'active')->count();
        $pendingApprovals = Loan::where('organization_id', $organizationId)->where('status', 'pending')->count();
        $overdueLoans = Loan::where('organization_id', $organizationId)->where('status', 'overdue')->count();

        // Get recent loans
        $recentLoans = Loan::where('organization_id', $organizationId)
            ->with(['client', 'loanProduct', 'branch'])
            ->latest()
            ->take(5)
            ->get();

        return view('loans.dashboard', compact('totalLoans', 'activeLoans', 'pendingApprovals', 'overdueLoans', 'recentLoans'));
    }

    /**
     * Display a listing of loans
     */
    public function index(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $query = Loan::where('organization_id', $organizationId)
            ->with(['client', 'loanProduct', 'branch', 'loanOfficer']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('application_date', [$request->date_from, $request->date_to]);
        }

        $loans = $query->latest()->paginate(20);
        $branches = Branch::where('organization_id', $organizationId)->get();

        return view('loans.index', compact('loans', 'branches'));
    }

    /**
     * Show the form for creating a new loan
     */
    public function create()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get clients for the organization
        $clients = Client::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();
        
        // Get loan products
        $loanProducts = LoanProduct::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        // Get branches
        $branches = Branch::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        // Get loan officers (users with loan officer role)
        $loanOfficers = User::where('organization_id', $organizationId)
            ->where('role', 'loan_officer')
            ->orderBy('first_name')
            ->get();

        return view('loans.create', compact('clients', 'loanProducts', 'branches', 'loanOfficers'));
    }

    /**
     * Store a newly created loan
     */
    public function store(Request $request)
    {
        // Implementation will be added later
        return redirect()->route('loans.index')->with('success', 'Loan application created successfully.');
    }

    /**
     * Display the specified loan
     */
    public function show(Loan $loan)
    {
        $loan->load([
            'client', 
            'loanProduct', 
            'branch', 
            'loanOfficer', 
            'schedules', 
            'transactions',
            'approvedBy',
            'rejectedBy',
            'returnedBy'
        ]);
        return view('loans.show', compact('loan'));
    }

    /**
     * Show the form for editing the specified loan
     */
    public function edit(Loan $loan)
    {
        return view('loans.edit', compact('loan'));
    }

    /**
     * Update the specified loan
     */
    public function update(Request $request, Loan $loan)
    {
        // Implementation will be added later
        return redirect()->route('loans.index')->with('success', 'Loan updated successfully.');
    }

    /**
     * Remove the specified loan
     */
    public function destroy(Loan $loan)
    {
        // Implementation will be added later
        return redirect()->route('loans.index')->with('success', 'Loan deleted successfully.');
    }

    /**
     * Display loan applications
     */
    public function applications(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        $query = Loan::where('organization_id', $organizationId)
            ->with(['client', 'loanProduct', 'branch', 'loanOfficer']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('application_date', [$request->date_from, $request->date_to]);
        }

        $loans = $query->latest()->paginate(20);
        $branches = Branch::where('organization_id', $organizationId)->get();

        return view('loans.applications', compact('loans', 'branches'));
    }

    /**
     * Display loan approvals
     */
    public function approvals(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get pending loans for approval
        $pendingLoans = Loan::where('organization_id', $organizationId)
            ->where('status', 'pending')
            ->with(['client', 'loanProduct', 'branch', 'loanOfficer'])
            ->latest()
            ->get();

        // Get recently approved loans
        $recentApprovals = Loan::where('organization_id', $organizationId)
            ->where('approval_status', 'approved')
            ->whereNotNull('approval_date')
            ->with(['client', 'loanProduct', 'branch', 'approvedBy'])
            ->latest('approval_date')
            ->take(10)
            ->get();

        return view('loans.approvals', compact('pendingLoans', 'recentApprovals'));
    }

    /**
     * Approve a loan
     */
    public function approve(Request $request, Loan $loan)
    {
        // Implementation will be added later
        return redirect()->back()->with('success', 'Loan approved successfully.');
    }

    /**
     * Reject a loan
     */
    public function reject(Request $request, Loan $loan)
    {
        // Implementation will be added later
        return redirect()->back()->with('success', 'Loan rejected.');
    }

    /**
     * Display loan disbursements
     */
    public function disbursements(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get approved loans ready for disbursement
        $readyForDisbursement = Loan::where('organization_id', $organizationId)
            ->where('status', 'approved')
            ->where('approval_status', 'approved')
            ->whereNull('disbursement_date')
            ->with(['client', 'loanProduct', 'branch', 'loanOfficer'])
            ->latest('approval_date')
            ->get();

        // Get recently disbursed loans
        $recentDisbursements = Loan::where('organization_id', $organizationId)
            ->where('status', 'disbursed')
            ->whereNotNull('disbursement_date')
            ->with(['client', 'loanProduct', 'branch', 'disbursementAccount'])
            ->latest('disbursement_date')
            ->take(10)
            ->get();

        // Get branch liability accounts for disbursement source
        $branchAccounts = Account::where('organization_id', $organizationId)
            ->whereHas('accountType', function($query) {
                $query->where('name', 'Liability');
            })
            ->whereNotNull('branch_id')
            ->with('branch')
            ->get();

        return view('loans.disbursements', compact('readyForDisbursement', 'recentDisbursements', 'branchAccounts'));
    }

    /**
     * Disburse a loan
     */
    public function disburse(Request $request, Loan $loan)
    {
        if ($loan->status !== 'approved' || $loan->approval_status !== 'approved') {
            return redirect()->back()->with('error', 'This loan is not ready for disbursement.');
        }

        try {
            // Get the first available liability account for disbursement
            $disbursementAccount = Account::where('organization_id', $loan->organization_id)
                ->whereHas('accountType', function($query) {
                    $query->where('name', 'Liability');
                })
                ->whereNotNull('branch_id')
                ->where('branch_id', $loan->branch_id)
                ->first();

            if (!$disbursementAccount) {
                return redirect()->back()->with('error', 'No disbursement account found for this branch.');
            }

            // Disburse the loan
            $success = $loan->disburseLoan($disbursementAccount->id, 'LOAN-DISB-' . $loan->loan_number);
            
            if ($success) {
                return redirect()->back()->with('success', 'Loan disbursed successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to disburse loan.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while disbursing the loan: ' . $e->getMessage());
        }
    }

    /**
     * Return loan to loan officer
     */
    public function returnToOfficer(Request $request, Loan $loan)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $loan->update([
                'status' => 'pending',
                'approval_status' => 'pending',
                'rejection_reason' => $request->reason,
                'returned_at' => now(),
                'returned_by' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Loan has been returned to the loan officer.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while returning the loan: ' . $e->getMessage());
        }
    }

    /**
     * Display loan repayments
     */
    public function repayments(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get active loans for repayment
        $activeLoans = Loan::where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'overdue'])
            ->with(['client', 'loanProduct', 'branch', 'schedules' => function($query) {
                $query->where('status', 'pending')->orderBy('due_date');
            }])
            ->get();

        // Get overdue loans
        $overdueLoans = Loan::where('organization_id', $organizationId)
            ->where('status', 'overdue')
            ->with(['client', 'loanProduct', 'branch'])
            ->get();

        // Get recent repayments
        $recentRepayments = LoanTransaction::where('organization_id', $organizationId)
            ->whereIn('transaction_type', ['principal_payment', 'interest_payment'])
            ->with(['loan.client', 'loanSchedule'])
            ->latest()
            ->take(10)
            ->get();

        return view('loans.repayments', compact('activeLoans', 'overdueLoans', 'recentRepayments'));
    }

    /**
     * Process loan repayment
     */
    public function processRepayment(Request $request, Loan $loan)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,bank_transfer,mobile_money,check,other',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
            $paymentAmount = $request->payment_amount;
            
            // Check if payment amount exceeds outstanding balance
            if ($paymentAmount > $loan->outstanding_balance) {
                return redirect()->back()->with('error', 'Payment amount cannot exceed outstanding balance.');
            }

            // Calculate principal and interest portions
            $principalAmount = 0;
            $interestAmount = 0;
            
            // Get the next due schedule
            $nextSchedule = $loan->schedules()->where('status', 'pending')->orderBy('due_date')->first();
            
            if ($nextSchedule) {
                // If payment is less than or equal to scheduled amount, split proportionally
                if ($paymentAmount <= $nextSchedule->amount) {
                    $principalAmount = $paymentAmount * ($nextSchedule->principal_amount / $nextSchedule->amount);
                    $interestAmount = $paymentAmount * ($nextSchedule->interest_amount / $nextSchedule->amount);
                } else {
                    // If payment exceeds scheduled amount, apply to principal
                    $interestAmount = $nextSchedule->interest_amount;
                    $principalAmount = $paymentAmount - $interestAmount;
                }
            } else {
                // No schedule, apply to principal
                $principalAmount = $paymentAmount;
            }

            // Create loan transaction
            $transaction = LoanTransaction::create([
                'loan_id' => $loan->id,
                'transaction_number' => LoanTransaction::generateTransactionNumber(),
                'transaction_type' => 'repayment',
                'amount' => $paymentAmount,
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'transaction_date' => now(),
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'notes' => $request->payment_notes,
                'processed_by' => auth()->id(),
                'organization_id' => $organizationId,
                'branch_id' => $loan->branch_id,
                'status' => 'completed',
            ]);

            // Update loan outstanding balance
            $loan->outstanding_balance -= $principalAmount;
            $loan->paid_amount += $paymentAmount;
            $loan->payments_made += 1;
            
            // Check if loan is fully paid
            if ($loan->outstanding_balance <= 0) {
                $loan->status = 'completed';
                $loan->closure_date = now();
                $loan->closed_by = auth()->id();
            }
            
            $loan->save();

            // Update loan schedule if applicable
            if ($nextSchedule) {
                $nextSchedule->paid_amount += $paymentAmount;
                if ($nextSchedule->paid_amount >= $nextSchedule->amount) {
                    $nextSchedule->status = 'paid';
                    $nextSchedule->paid_date = now();
                }
                $nextSchedule->save();
            }

            // Record in General Ledger
            $this->recordLoanRepaymentInLedger($loan, $paymentAmount, $principalAmount, $interestAmount);

            return redirect()->back()->with('success', 'Payment processed successfully. Amount: TZS ' . number_format($paymentAmount, 2));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while processing payment: ' . $e->getMessage());
        }
    }

    /**
     * Record loan repayment in general ledger
     */
    private function recordLoanRepaymentInLedger(Loan $loan, $totalAmount, $principalAmount, $interestAmount)
    {
        $organizationId = $loan->organization_id;
        $branchId = $loan->branch_id;
        
        // Get loan product accounts
        $loanProduct = $loan->loanProduct;
        $collectionAccount = $loanProduct->collectionAccount;
        $principalAccount = $loanProduct->principalAccount;
        $interestRevenueAccount = $loanProduct->interestRevenueAccount;
        
        if (!$collectionAccount || !$principalAccount || !$interestRevenueAccount) {
            throw new \Exception('Loan product accounts not configured properly.');
        }

        $transactionId = 'REP-' . date('Ymd') . '-' . str_pad($loan->id, 6, '0', STR_PAD_LEFT);
        $now = now();

        // Debit: Collection Account (Cash received)
        \App\Models\GeneralLedger::create([
            'organization_id' => $organizationId,
            'branch_id' => $branchId,
            'account_id' => $collectionAccount->id,
            'transaction_id' => $transactionId,
            'transaction_type' => 'debit',
            'amount' => $totalAmount,
            'description' => "Loan repayment - {$loan->loan_number}",
            'transaction_date' => $now,
            'reference_type' => 'LoanTransaction',
            'reference_id' => $loan->id,
            'created_by' => auth()->id(),
        ]);

        // Credit: Principal Account (Principal portion)
        if ($principalAmount > 0) {
            \App\Models\GeneralLedger::create([
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'account_id' => $principalAccount->id,
                'transaction_id' => $transactionId,
                'transaction_type' => 'credit',
                'amount' => $principalAmount,
                'description' => "Principal repayment - {$loan->loan_number}",
                'transaction_date' => $now,
                'reference_type' => 'LoanTransaction',
                'reference_id' => $loan->id,
                'created_by' => auth()->id(),
            ]);
        }

        // Credit: Interest Revenue Account (Interest portion)
        if ($interestAmount > 0) {
            \App\Models\GeneralLedger::create([
                'organization_id' => $organizationId,
                'branch_id' => $branchId,
                'account_id' => $interestRevenueAccount->id,
                'transaction_id' => $transactionId,
                'transaction_type' => 'credit',
                'amount' => $interestAmount,
                'description' => "Interest income - {$loan->loan_number}",
                'transaction_date' => $now,
                'reference_type' => 'LoanTransaction',
                'reference_id' => $loan->id,
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Display loan reports
     */
    public function reports(Request $request)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get loan statistics
        $totalLoans = Loan::where('organization_id', $organizationId)->count();
        $activeLoans = Loan::where('organization_id', $organizationId)->where('status', 'active')->count();
        $pendingLoans = Loan::where('organization_id', $organizationId)->where('status', 'pending')->count();
        $overdueLoans = Loan::where('organization_id', $organizationId)->where('status', 'overdue')->count();
        $completedLoans = Loan::where('organization_id', $organizationId)->where('status', 'completed')->count();

        // Get portfolio statistics
        $totalPortfolio = Loan::where('organization_id', $organizationId)->where('status', 'active')->sum('outstanding_balance');
        $totalDisbursed = Loan::where('organization_id', $organizationId)->whereNotNull('disbursement_date')->sum('approved_amount');
        $totalRepaid = Loan::where('organization_id', $organizationId)->sum('paid_amount');
        $totalOverdue = Loan::where('organization_id', $organizationId)->where('status', 'overdue')->sum('overdue_amount');

        // Get loans by branch
        $loansByBranch = Loan::where('organization_id', $organizationId)
            ->with('branch')
            ->selectRaw('branch_id, COUNT(*) as loan_count, SUM(outstanding_balance) as total_outstanding')
            ->groupBy('branch_id')
            ->get();

        // Get loans by loan officer
        $loansByOfficer = Loan::where('organization_id', $organizationId)
            ->with('loanOfficer')
            ->selectRaw('loan_officer_id, COUNT(*) as loan_count, SUM(outstanding_balance) as total_outstanding')
            ->groupBy('loan_officer_id')
            ->get();

        // Get branches for filtering
        $branches = Branch::where('organization_id', $organizationId)->get();

        $stats = [
            'totalLoans' => $totalLoans,
            'activeLoans' => $activeLoans,
            'pendingLoans' => $pendingLoans,
            'overdueLoans' => $overdueLoans,
            'completedLoans' => $completedLoans,
            'totalPortfolio' => $totalPortfolio,
            'totalDisbursed' => $totalDisbursed,
            'totalRepaid' => $totalRepaid,
            'totalOverdue' => $totalOverdue,
        ];

        return view('loans.reports', compact('stats', 'loansByBranch', 'loansByOfficer', 'branches'));
    }

    /**
     * Close a loan
     */
    public function closeLoan(Request $request, Loan $loan)
    {
        // Implementation will be added later
        return redirect()->back()->with('success', 'Loan closed successfully.');
    }

    /**
     * Write off a loan
     */
    public function writeOffLoan(Request $request, Loan $loan)
    {
        // Implementation will be added later
        return redirect()->back()->with('success', 'Loan written off successfully.');
    }

    /**
     * Restructure a loan
     */
    public function restructureLoan(Request $request, Loan $loan)
    {
        // Implementation will be added later
        return redirect()->back()->with('success', 'Loan restructured successfully.');
    }

    /**
     * Top up a loan
     */
    public function topUpLoan(Request $request, Loan $loan)
    {
        // Implementation will be added later
        return redirect()->back()->with('success', 'Loan topped up successfully.');
    }

    /**
     * Generate loan number
     */
    public function generateLoanNumber()
    {
        return response()->json([
            'loan_number' => Loan::generateLoanNumber()
        ]);
    }
}