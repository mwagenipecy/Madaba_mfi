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
use App\Models\SystemLog;
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
        // Get user's organization (required)
        $userOrganizationId = auth()->user()->organization_id;
        if (!$userOrganizationId) {
            return redirect()->route('dashboard')->with('error', 'You must be assigned to an organization to create loans.');
        }
        
        $userOrganization = Organization::findOrFail($userOrganizationId);
        
        // Get clients for the organization
        $clients = Client::where('organization_id', $userOrganizationId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();
        
        // Get loan products
        $loanProducts = LoanProduct::where('organization_id', $userOrganizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        // Get branches that belong to user's organization
        $branches = Branch::where('organization_id', $userOrganizationId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        // Get loan officers (users with loan officer role)
        $loanOfficers = User::where('organization_id', $userOrganizationId)
            ->where('role', 'loan_officer')
            ->orderBy('first_name')
            ->get();

        return view('loans.create', compact('clients', 'loanProducts', 'branches', 'loanOfficers', 'userOrganization'));
    }

    /**
     * Store a newly created loan
     */
    public function store(Request $request)
    {
        // Get user's organization
        $userOrganizationId = auth()->user()->organization_id;
        if (!$userOrganizationId) {
            return redirect()->route('dashboard')->with('error', 'You must be assigned to an organization to create loans.');
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'loan_amount' => 'required|numeric|min:0.01',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'loan_tenure_months' => 'nullable|integer|min:1|max:360',
            'interest_calculation_method' => 'nullable|in:flat,reducing',
            'repayment_frequency' => 'nullable|in:daily,weekly,monthly,quarterly',
            'branch_id' => 'nullable|exists:branches,id',
            'loan_officer_id' => 'nullable|exists:users,id',
            'purpose' => 'nullable|string|max:500',
            'collateral_description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ], [
            'client_id.required' => 'Please select a client.',
            'client_id.exists' => 'Selected client does not exist.',
            'loan_product_id.required' => 'Please select a loan product.',
            'loan_product_id.exists' => 'Selected loan product does not exist.',
            'loan_amount.required' => 'Loan amount is required.',
            'loan_amount.numeric' => 'Loan amount must be a valid number.',
            'loan_amount.min' => 'Loan amount must be greater than 0.',
            'interest_calculation_method.in' => 'Interest calculation method must be either flat or reducing.',
            'repayment_frequency.in' => 'Repayment frequency must be daily, weekly, monthly, or quarterly.',
            'branch_id.exists' => 'Selected branch does not exist or does not belong to your organization.',
            'loan_officer_id.exists' => 'Selected loan officer does not exist.',
        ]);

        // Verify client belongs to user's organization
        $client = Client::where('id', $request->client_id)
                       ->where('organization_id', $userOrganizationId)
                       ->first();

        if (!$client) {
            return redirect()->back()
                ->withErrors(['client_id' => 'Selected client does not belong to your organization.'])
                ->withInput();
        }

        // Verify loan product belongs to user's organization
        $loanProduct = LoanProduct::where('id', $request->loan_product_id)
                                 ->where('organization_id', $userOrganizationId)
                                 ->first();

        if (!$loanProduct) {
            return redirect()->back()
                ->withErrors(['loan_product_id' => 'Selected loan product does not belong to your organization.'])
                ->withInput();
        }

        // Verify branch belongs to user's organization if provided
        if ($request->branch_id) {
            $branch = Branch::where('id', $request->branch_id)
                           ->where('organization_id', $userOrganizationId)
                           ->first();

            if (!$branch) {
                return redirect()->back()
                    ->withErrors(['branch_id' => 'Selected branch does not belong to your organization.'])
                    ->withInput();
            }
        }

        // Create the loan
        $loan = Loan::create([
            'loan_number' => Loan::generateLoanNumber(),
            'client_id' => $request->client_id,
            'loan_product_id' => $request->loan_product_id,
            'organization_id' => $userOrganizationId,
            'branch_id' => $request->branch_id,
            'loan_officer_id' => $request->loan_officer_id ?? auth()->id(),
            'loan_amount' => $request->loan_amount,
            'interest_rate' => $request->interest_rate ?? $loanProduct->interest_rate,
            'loan_tenure_months' => $request->loan_tenure_months ?? $loanProduct->min_tenure_months,
            'interest_calculation_method' => $request->interest_calculation_method ?? 'flat',
            'repayment_frequency' => $request->repayment_frequency ?? 'monthly',
            'application_date' => now()->toDateString(),
            'purpose' => $request->purpose,
            'collateral_description' => $request->collateral_description,
            'status' => 'pending',
            'approval_status' => 'pending',
            'notes' => $request->notes,
        ]);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan application created successfully.');
    }

    /**
     * Upload document for a loan
     */
    public function uploadDocument(Request $request, Loan $loan)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
            'document_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('loan_documents', $filename, 'public');

            // Get existing documents or create empty array
            $documents = $loan->documents ?? [];
            
            // Add new document
            $documents[] = [
                'id' => uniqid(),
                'name' => $file->getClientOriginalName(),
                'type' => $request->document_type,
                'description' => $request->description,
                'filename' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now()->toISOString(),
                'status' => 'pending'
            ];

            $loan->update(['documents' => $documents]);

            SystemLog::log(
                'Document uploaded',
                'Document "' . $file->getClientOriginalName() . '" uploaded for loan ' . $loan->loan_number,
                'info',
                $loan,
                auth()->id(),
                ['document_type' => $request->document_type, 'filename' => $filename]
            );

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Document uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload document.');
    }

    /**
     * Add comment to a loan
     */
    public function addComment(Request $request, Loan $loan)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
            'comment_type' => 'nullable|in:general,internal,client_communication',
        ]);

        // Get existing comments or create empty array
        $comments = $loan->comments ?? [];
        
        // Add new comment
        $comments[] = [
            'id' => uniqid(),
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            'user_role' => auth()->user()->role,
            'comment' => $request->comment,
            'comment_type' => $request->comment_type ?? 'general',
            'created_at' => now()->toISOString(),
        ];

        $loan->update(['comments' => $comments]);

        SystemLog::log(
            'Comment added',
            'Comment added to loan ' . $loan->loan_number,
            'info',
            $loan,
            auth()->id(),
            ['comment_type' => $request->comment_type ?? 'general']
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Download a loan document
     */
    public function downloadDocument(Loan $loan, $documentId)
    {
        $documents = $loan->documents ?? [];
        
        foreach ($documents as $document) {
            if ($document['id'] === $documentId) {
                $filePath = storage_path('app/public/' . $document['path']);
                
                if (file_exists($filePath)) {
                    return response()->download($filePath, $document['name']);
                }
                break;
            }
        }

        return redirect()->back()->with('error', 'Document not found.');
    }

    /**
     * Delete a loan document
     */
    public function deleteDocument(Loan $loan, $documentId)
    {
        $documents = $loan->documents ?? [];
        $updatedDocuments = [];
        $deletedDocument = null;

        foreach ($documents as $document) {
            if ($document['id'] !== $documentId) {
                $updatedDocuments[] = $document;
            } else {
                $deletedDocument = $document;
                // Delete the actual file
                $filePath = storage_path('app/public/' . $document['path']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $loan->update(['documents' => $updatedDocuments]);

        if ($deletedDocument) {
            SystemLog::log(
                'Document deleted',
                'Document "' . $deletedDocument['name'] . '" deleted from loan ' . $loan->loan_number,
                'warning',
                $loan,
                auth()->id(),
                ['document_type' => $deletedDocument['type']]
            );

            return redirect()->route('loans.show', $loan)
                ->with('success', 'Document deleted successfully.');
        }

        return redirect()->back()->with('error', 'Document not found.');
    }

    /**
     * Approve a loan
     */
    public function approve(Request $request, Loan $loan)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
            'approved_amount' => 'nullable|numeric|min:0',
        ]);

        // Check if user has permission to approve loans
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to approve loans.');
        }

        // Update loan status
        $loan->update([
            'status' => 'approved',
            'approval_status' => 'approved',
            'approval_date' => now()->toDateString(),
            'approved_by' => auth()->id(),
            'approval_notes' => $request->approval_notes,
            'approved_amount' => $request->approved_amount ?? $loan->loan_amount,
        ]);

        SystemLog::log(
            'Loan approved',
            'Loan ' . $loan->loan_number . ' has been approved',
            'info',
            $loan,
            auth()->id(),
            ['approved_amount' => $request->approved_amount ?? $loan->loan_amount, 'approval_notes' => $request->approval_notes]
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan has been approved successfully.');
    }

    /**
     * Reject a loan
     */
    public function reject(Request $request, Loan $loan)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        // Check if user has permission to reject loans
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to reject loans.');
        }

        // Update loan status
        $loan->update([
            'status' => 'rejected',
            'approval_status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        SystemLog::log(
            'Loan rejected',
            'Loan ' . $loan->loan_number . ' has been rejected',
            'warning',
            $loan,
            auth()->id(),
            ['rejection_reason' => $request->rejection_reason]
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan has been rejected.');
    }

    /**
     * Return loan to loan officer for review
     */
    public function returnToOfficer(Request $request, Loan $loan)
    {
        $request->validate([
            'return_notes' => 'nullable|string|max:1000',
        ]);

        // Check if user has permission to return loans
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to return loans to officers.');
        }

        // Update loan status
        $loan->update([
            'status' => 'pending',
            'approval_status' => 'pending',
            'returned_by' => auth()->id(),
            'returned_at' => now(),
            'notes' => $loan->notes . "\n\nReturned to loan officer: " . ($request->return_notes ?? 'Additional review required'),
        ]);

        SystemLog::log(
            'Loan returned to officer',
            'Loan ' . $loan->loan_number . ' has been returned to loan officer for review',
            'info',
            $loan,
            auth()->id(),
            ['return_notes' => $request->return_notes]
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan has been returned to the loan officer.');
    }

    /**
     * Put loan under review
     */
    public function putUnderReview(Loan $loan)
    {
        // Check if user has permission to review loans
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin', 'loan_officer'])) {
            return redirect()->back()->with('error', 'You do not have permission to review loans.');
        }

        // Update loan status
        $loan->update([
            'status' => 'under_review',
        ]);

        SystemLog::log(
            'Loan under review',
            'Loan ' . $loan->loan_number . ' is now under review',
            'info',
            $loan,
            auth()->id()
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan is now under review.');
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
                // Create a demo disbursement account for this branch (Liability type)
                $liabilityType = \App\Models\AccountType::where('name', 'Liability')->first();
                if (!$liabilityType) {
                    return redirect()->back()->with('error', 'Liability account type not configured.');
                }

                $branchName = $loan->branch?->name ?? 'Branch';
                $disbursementAccount = \App\Models\Account::create([
                    'name' => $branchName . ' Disbursement Account (Demo)',
                    'account_number' => 'DISB-' . str_pad((string)$loan->branch_id, 4, '0', STR_PAD_LEFT) . '-' . rand(100,999),
                    'account_type_id' => $liabilityType->id,
                    'organization_id' => $loan->organization_id,
                    'branch_id' => $loan->branch_id,
                    'balance' => 0,
                    'opening_balance' => 0,
                    'currency' => 'TZS',
                    'description' => 'Auto-created demo disbursement account for loan disbursement.',
                    'status' => 'active',
                    'opening_date' => now(),
                    'last_transaction_date' => now(),
                ]);
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
                'transaction_type' => 'principal_payment',
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
                if ($nextSchedule->paid_amount >= $nextSchedule->total_amount) {
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

        $transactionId = 'REP-' . date('YmdHis') . '-' . str_pad($loan->id, 6, '0', STR_PAD_LEFT);

        // Debit: Collection Account (Cash received - Asset increases)
        \App\Models\GeneralLedger::createTransaction(
            $transactionId . '-COLLECTION',
            $collectionAccount,
            'debit',
            $totalAmount,
            "Loan repayment received - {$loan->loan_number}",
            'LoanTransaction',
            $loan->id,
            auth()->id()
        );

        // Credit: Principal Account (Principal portion - Asset decreases)
        if ($principalAmount > 0) {
            \App\Models\GeneralLedger::createTransaction(
                $transactionId . '-PRINCIPAL',
                $principalAccount,
                'credit',
                $principalAmount,
                "Principal repayment - {$loan->loan_number}",
                'LoanTransaction',
                $loan->id,
                auth()->id()
            );
        }

        // Credit: Interest Revenue Account (Interest portion - Income increases)
        if ($interestAmount > 0) {
            \App\Models\GeneralLedger::createTransaction(
                $transactionId . '-INTEREST',
                $interestRevenueAccount,
                'credit',
                $interestAmount,
                "Interest income - {$loan->loan_number}",
                'LoanTransaction',
                $loan->id,
                auth()->id()
            );
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