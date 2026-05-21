<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\RepaymentRecord;
use App\Models\Client;
use App\Models\LoanProduct;
use App\Models\Branch;
use App\Models\User;
use App\Models\Organization;
use App\Services\LoanRepaymentAllocator;
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
        $activeLoans = Loan::where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'overdue', 'disbursed'])
            ->count();
        $pendingApprovals = Loan::where('organization_id', $organizationId)->where('status', 'pending')->count();
        $overdueLoans = Loan::where('organization_id', $organizationId)->where('status', 'overdue')->count();

        // Get recent loans (wider list; KPI above reflects all on-book statuses)
        $recentLoans = Loan::where('organization_id', $organizationId)
            ->with(['client', 'loanProduct', 'branch'])
            ->latest()
            ->take(20)
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
            'loan_tenure_value' => 'nullable|numeric|min:1',
            'loan_tenure_months' => 'required|numeric|min:0.01',
            'processing_fee' => 'nullable|numeric|min:0',
            'processing_fee_amount' => 'nullable|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'interest_calculation_method' => 'nullable|in:flat,reducing',
            'repayment_frequency' => 'nullable|in:daily,weekly,monthly,quarterly',
            'branch_id' => 'nullable|exists:branches,id',
            'loan_officer_id' => 'nullable|exists:users,id',
            'purpose' => 'nullable|string|max:500',
            'collateral_description' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'custom_mode' => 'nullable|in:0,1',
            'custom_repayment_amount' => 'nullable|numeric|min:0',
            'custom_charge_type' => 'nullable|in:percent,fixed',
            'custom_charge_value' => 'nullable|numeric|min:0',
            'custom_charge_amount' => 'nullable|numeric|min:0',
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

        // Get tenure value - JavaScript converts it to months before submission
        $tenureInMonths = $request->loan_tenure_months ?? $loanProduct->min_tenure_months;
        
        // Calculate processing fee amount
        $processingFeeAmount = 0;
        if ($request->has('processing_fee_amount') && $request->processing_fee_amount) {
            $processingFeeAmount = $request->processing_fee_amount;
        } else {
            $processingFee = $loanProduct->processing_fee ?? 0;
            $processingFeeType = $loanProduct->processing_fee_type ?? 'fixed';
            
            if ($processingFeeType === 'percent') {
                $processingFeeAmount = ($request->loan_amount * $processingFee) / 100;
            } else {
                $processingFeeAmount = $processingFee;
            }
        }
        
        // Determine interest rate and custom charge
        $isCustomMode = $request->custom_mode === '1';
        $interestRate = $request->interest_rate ?? $loanProduct->interest_rate;
        $customChargeAmount = 0;
        
        // Build metadata for custom loan details
        $metadata = [];
        
        if ($isCustomMode) {
            $metadata['custom_mode'] = true;
            
            // Custom repayment amount per period
            if ($request->custom_repayment_amount && $request->custom_repayment_amount > 0) {
                $metadata['custom_repayment_amount'] = (float) $request->custom_repayment_amount;
            }
        }
        
        // Custom charge (available in both modes)
        if ($request->custom_charge_type && $request->custom_charge_value > 0) {
            $customChargeAmount = (float) ($request->custom_charge_amount ?? 0);
            
            // Recalculate server-side for safety
            if ($request->custom_charge_type === 'percent') {
                $customChargeAmount = ($request->loan_amount * $request->custom_charge_value) / 100;
            } else {
                $customChargeAmount = (float) $request->custom_charge_value;
            }
            
            $metadata['custom_charge'] = [
                'type' => $request->custom_charge_type,
                'value' => (float) $request->custom_charge_value,
                'amount' => $customChargeAmount,
            ];
        }
        
        // If custom repayment amount is set, keep the product interest rate as reference
        // The schedule generation will use the custom repayment amount from metadata directly
        // No need to back-calculate — the custom_repayment_amount in metadata drives the schedule
        
        // Create the loan
        $loan = Loan::create([
            'loan_number' => Loan::generateLoanNumber(),
            'client_id' => $request->client_id,
            'loan_product_id' => $request->loan_product_id,
            'organization_id' => $userOrganizationId,
            'branch_id' => $request->branch_id,
            'loan_officer_id' => $request->loan_officer_id ?? auth()->id(),
            'loan_amount' => $request->loan_amount,
            'interest_rate' => $interestRate,
            'loan_tenure_months' => $tenureInMonths,
            'interest_calculation_method' => $request->interest_calculation_method ?? $loanProduct->interest_calculation_method ?? 'flat',
            'repayment_frequency' => $request->repayment_frequency ?? $loanProduct->repayment_frequency ?? 'monthly',
            'processing_fee' => $processingFeeAmount,
            'insurance_fee' => $request->insurance_fee ?? 0,
            'other_fees' => $customChargeAmount,
            'application_date' => now()->toDateString(),
            'purpose' => $request->purpose,
            'collateral_description' => $request->collateral_description,
            'status' => 'pending',
            'approval_status' => 'pending',
            'notes' => $request->notes,
            'metadata' => !empty($metadata) ? $metadata : null,
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
            'first_payment_date' => 'nullable|date|after_or_equal:today',
        ]);

        // Check if user has permission to approve loans
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to approve loans.');
        }

        // Only assessed loans can be approved
        if ($loan->status !== 'assessed') {
            return redirect()->back()->with('error', 'Only assessed loans can be approved. Please complete the assessment first.');
        }

        $approvedAmount = $request->approved_amount ?? $loan->loan_amount;

        // Update loan status to active, set approved amount, and save schedule
        $loan->update([
            'status' => 'active',
            'approval_status' => 'approved',
            'approval_date' => now()->toDateString(),
            'approved_by' => auth()->id(),
            'approval_notes' => $request->approval_notes,
            'approved_amount' => $approvedAmount,
            'disbursement_date' => now()->toDateString(),
            'first_payment_date' => $request->first_payment_date ?? now()->addDay()->toDateString(),
            'outstanding_balance' => $approvedAmount,
        ]);

        // Generate and save the payment schedule
        $loan->refresh();
        $loan->calculateLoanSchedule();

        // Calculate maturity date from schedule
        $lastSchedule = $loan->schedules()->orderBy('installment_number', 'desc')->first();
        if ($lastSchedule) {
            $loan->update([
                'maturity_date' => $lastSchedule->due_date,
            ]);
        }

        SystemLog::log(
            'Loan approved and activated',
            'Loan ' . $loan->loan_number . ' has been approved, schedule generated, and set to active',
            'info',
            $loan,
            auth()->id(),
            ['approved_amount' => $approvedAmount, 'approval_notes' => $request->approval_notes]
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan has been approved, payment schedule saved, and loan is now active.');
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
     * Start review of a loan (pending → under_review)
     */
    public function putUnderReview(Loan $loan)
    {
        // Check if user has permission to review loans
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin', 'loan_officer'])) {
            return redirect()->back()->with('error', 'You do not have permission to review loans.');
        }

        if ($loan->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending loans can be put under review.');
        }

        // Update loan status
        $loan->update([
            'status' => 'under_review',
        ]);

        SystemLog::log(
            'Loan review started',
            'Loan ' . $loan->loan_number . ' review has been started',
            'info',
            $loan,
            auth()->id()
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan review has been started. You can now upload documents and perform assessment.');
    }

    /**
     * Complete assessment of a loan (under_review → assessed)
     */
    public function completeAssessment(Request $request, Loan $loan)
    {
        $request->validate([
            'assessment_notes' => 'nullable|string|max:2000',
        ]);

        // Check if user has permission
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin', 'loan_officer'])) {
            return redirect()->back()->with('error', 'You do not have permission to complete loan assessment.');
        }

        if ($loan->status !== 'under_review') {
            return redirect()->back()->with('error', 'Only loans under review can have their assessment completed.');
        }

        // Update loan status to assessed
        $metadata = is_array($loan->metadata) ? $loan->metadata : [];
        $metadata['assessment'] = [
            'completed_by' => auth()->id(),
            'completed_by_name' => auth()->user()->name,
            'completed_at' => now()->toDateTimeString(),
            'notes' => $request->assessment_notes,
        ];

        $loan->update([
            'status' => 'assessed',
            'metadata' => $metadata,
        ]);

        // Add assessment comment
        $comments = is_array($loan->comments) ? $loan->comments : [];
        $comments[] = [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_role' => auth()->user()->role,
            'comment_type' => 'assessment',
            'comment' => 'Assessment completed. ' . ($request->assessment_notes ?? ''),
            'created_at' => now()->toDateTimeString(),
        ];
        $loan->update(['comments' => $comments]);

        SystemLog::log(
            'Loan assessment completed',
            'Loan ' . $loan->loan_number . ' assessment has been completed and is ready for approval',
            'info',
            $loan,
            auth()->id(),
            ['assessment_notes' => $request->assessment_notes]
        );

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Assessment completed. Loan is now ready for approval.');
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
        
        // Calculate total interest percentage
        $totalInterestPercentage = 0;
        $loanFrequency = $loan->repayment_frequency ?? 'monthly';
        if ($loan->loan_amount > 0 && $loan->total_interest) {
            $totalInterestPercentage = ($loan->total_interest / $loan->loan_amount) * 100;
        } elseif ($loan->loan_amount > 0 && $loan->interest_rate && $loan->loan_tenure_months) {
            // Calculate if not set
            $rate = $loan->interest_rate / 100;
            if ($loan->interest_calculation_method === 'flat') {
                // For daily/weekly: rate is total % for the loan period
                // For monthly/quarterly: rate is per annum
                if (in_array($loanFrequency, ['daily', 'weekly'])) {
                    $totalInterest = $loan->loan_amount * $rate;
                } else {
                    $totalInterest = $loan->loan_amount * $rate * ($loan->loan_tenure_months / 12);
                }
            } else {
                // Reducing balance approximation
                $frequencyMultiplier = match($loanFrequency) {
                    'daily' => 30,
                    'weekly' => 4,
                    'quarterly' => 0.33,
                    default => 1,
                };
                $totalPayments = ceil($loan->loan_tenure_months * $frequencyMultiplier);
                
                // For daily/weekly: rate is total % for the loan period
                if (in_array($loanFrequency, ['daily', 'weekly'])) {
                    $periodicRate = $rate / $totalPayments;
                } else {
                    $periodicRate = match($loanFrequency) {
                        'quarterly' => $rate / 4,
                        default => $rate / 12,
                    };
                }
                
                if ($periodicRate > 0 && $totalPayments > 0) {
                    $periodicPayment = $loan->loan_amount * ($periodicRate * pow(1 + $periodicRate, $totalPayments)) / (pow(1 + $periodicRate, $totalPayments) - 1);
                    $totalInterest = ($periodicPayment * $totalPayments) - $loan->loan_amount;
                } else {
                    $totalInterest = 0;
                }
            }
            $totalInterestPercentage = ($totalInterest / $loan->loan_amount) * 100;
        }
        
        // Generate preview schedule if not saved yet
        // Show for approved loans or pending/under_review loans (for preview purposes)
        $previewSchedule = null;
        if ($loan->schedules->count() === 0) {
            // Use approved_amount if available, otherwise use loan_amount for preview
            $amountToUse = $loan->approved_amount ?? $loan->loan_amount;
            if ($amountToUse && $loan->interest_rate && $loan->loan_tenure_months) {
                // Temporarily set approved_amount for calculation if not set
                $originalApprovedAmount = $loan->approved_amount;
                if (!$loan->approved_amount) {
                    $loan->approved_amount = $loan->loan_amount;
                }
                $previewSchedule = $this->generatePreviewSchedule($loan);
                // Restore original value
                if (!$originalApprovedAmount) {
                    $loan->approved_amount = null;
                }
            }
        }
        
        // Prepare schedule adjustment data
        $frequency = $loan->repayment_frequency ?? 'monthly';
        $tenureMonths = $loan->loan_tenure_months;
        
        $scheduleAdjustment = [
            'frequency' => $frequency,
            'current_value' => match($frequency) {
                'daily' => round($tenureMonths * 30),
                'weekly' => round($tenureMonths * 4),
                'quarterly' => round($tenureMonths / 3),
                default => $tenureMonths,
            },
            'unit_label' => match($frequency) {
                'daily' => 'Days',
                'weekly' => 'Weeks',
                'quarterly' => 'Quarters',
                default => 'Months',
            },
            'min_value' => $loan->loanProduct ? match($frequency) {
                'daily' => round($loan->loanProduct->min_tenure_months * 30),
                'weekly' => round($loan->loanProduct->min_tenure_months * 4),
                'quarterly' => round($loan->loanProduct->min_tenure_months / 3),
                default => $loan->loanProduct->min_tenure_months,
            } : 1,
            'max_value' => $loan->loanProduct ? match($frequency) {
                'daily' => round($loan->loanProduct->max_tenure_months * 30),
                'weekly' => round($loan->loanProduct->max_tenure_months * 4),
                'quarterly' => round($loan->loanProduct->max_tenure_months / 3),
                default => $loan->loanProduct->max_tenure_months,
            } : 360,
            'can_adjust' => !in_array($loan->status, ['disbursed', 'active', 'overdue', 'completed', 'written_off', 'cancelled']),
            'total_installments' => $previewSchedule ? count($previewSchedule) : $loan->schedules->count(),
        ];

        return view('loans.show', compact('loan', 'totalInterestPercentage', 'previewSchedule', 'scheduleAdjustment'));
    }
    
    /**
     * Generate preview schedule without saving to database
     */
    private function generatePreviewSchedule(Loan $loan): array
    {
        // Check if custom repayment is available even without interest_rate
        $meta = is_array($loan->metadata) ? $loan->metadata : (is_string($loan->metadata) ? json_decode($loan->metadata, true) : []);
        $hasCustomRepayment = !empty($meta['custom_repayment_amount']) && $meta['custom_repayment_amount'] > 0;
        
        if (!$loan->approved_amount || !$loan->loan_tenure_months) {
            return [];
        }
        
        if (!$hasCustomRepayment && !$loan->interest_rate) {
            return [];
        }

        $principal = $loan->approved_amount;
        $rate = $loan->interest_rate / 100;
        $months = $loan->loan_tenure_months;
        $frequency = $loan->repayment_frequency ?? 'monthly';

        // Calculate payment frequency multiplier
        $frequencyMultiplier = match($frequency) {
            'daily' => 30,
            'weekly' => 4,
            'monthly' => 1,
            'quarterly' => 0.33,
            default => 1,
        };

        $totalPayments = ceil($months * $frequencyMultiplier);
        $schedule = [];
        $paymentDate = $loan->first_payment_date ? \Carbon\Carbon::parse($loan->first_payment_date) : now()->addDays(30);
        
        // Check if custom repayment amount is set in metadata
        $metadata = is_array($loan->metadata) ? $loan->metadata : (is_string($loan->metadata) ? json_decode($loan->metadata, true) : []);
        $customRepayment = $metadata['custom_repayment_amount'] ?? null;
        
        if ($customRepayment && $customRepayment > 0) {
            // Custom repayment schedule: fixed amount per installment
            $totalRepayment = $customRepayment * $totalPayments;
            $totalInterest = max(0, $totalRepayment - $principal);
            $principalPerInstallment = $principal / $totalPayments;
            $interestPerInstallment = $totalInterest / $totalPayments;
            
            for ($i = 1; $i <= $totalPayments; $i++) {
                $dueDate = $paymentDate->copy();
                if ($frequency === 'daily') {
                    $dueDate->addDays($i - 1);
                } elseif ($frequency === 'weekly') {
                    $dueDate->addWeeks($i - 1);
                } elseif ($frequency === 'monthly') {
                    $dueDate->addMonths($i - 1);
                } elseif ($frequency === 'quarterly') {
                    $dueDate->addMonths(($i - 1) * 3);
                }
                
                $schedule[] = [
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'principal_amount' => $principalPerInstallment,
                    'interest_amount' => $interestPerInstallment,
                    'total_amount' => $customRepayment,
                ];
            }
            
            return $schedule;
        }
        
        if ($loan->interest_calculation_method === 'flat') {
            // For daily/weekly: rate is total % for the loan period
            // For monthly/quarterly: rate is per annum
            if (in_array($frequency, ['daily', 'weekly'])) {
                $totalInterest = $principal * $rate;
            } else {
                $totalInterest = $principal * $rate * ($months / 12);
            }
            $monthlyPayment = ($principal + $totalInterest) / $totalPayments;
            $principalAmount = $principal / $totalPayments;
            $interestAmount = $totalInterest / $totalPayments;
            
            for ($i = 1; $i <= $totalPayments; $i++) {
                $dueDate = $paymentDate->copy();
                if ($frequency === 'daily') {
                    $dueDate->addDays($i - 1);
                } elseif ($frequency === 'weekly') {
                    $dueDate->addWeeks($i - 1);
                } elseif ($frequency === 'monthly') {
                    $dueDate->addMonths($i - 1);
                } elseif ($frequency === 'quarterly') {
                    $dueDate->addMonths(($i - 1) * 3);
                }
                
                $schedule[] = [
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'principal_amount' => $principalAmount,
                    'interest_amount' => $interestAmount,
                    'total_amount' => $principalAmount + $interestAmount,
                ];
            }
        } else {
            // Reducing balance
            // Calculate periodic rate based on frequency
            // For daily/weekly: rate is total % for the loan period, so divide by number of installments
            // For monthly/quarterly: rate is per annum, so divide into periodic rate
            $periodicRate = match($frequency) {
                'daily' => $rate / $totalPayments,
                'weekly' => $rate / $totalPayments,
                'monthly' => $rate / 12,
                'quarterly' => $rate / 4,
                default => $rate / 12,
            };
            
            $periodicPayment = $principal * ($periodicRate * pow(1 + $periodicRate, $totalPayments)) / (pow(1 + $periodicRate, $totalPayments) - 1);
            $remainingBalance = $principal;
            
            for ($i = 1; $i <= $totalPayments; $i++) {
                $interestAmount = $remainingBalance * $periodicRate;
                $principalAmount = $periodicPayment - $interestAmount;
                
                if ($i === $totalPayments) {
                    $principalAmount = $remainingBalance;
                    $periodicPayment = $principalAmount + $interestAmount;
                }
                
                $dueDate = $paymentDate->copy();
                if ($frequency === 'daily') {
                    $dueDate->addDays($i - 1);
                } elseif ($frequency === 'weekly') {
                    $dueDate->addWeeks($i - 1);
                } elseif ($frequency === 'monthly') {
                    $dueDate->addMonths($i - 1);
                } elseif ($frequency === 'quarterly') {
                    $dueDate->addMonths(($i - 1) * 3);
                } else {
                    // Default to monthly
                    $dueDate->addMonths($i - 1);
                }
                
                $schedule[] = [
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'principal_amount' => $principalAmount,
                    'interest_amount' => $interestAmount,
                    'total_amount' => $principalAmount + $interestAmount,
                ];
                
                $remainingBalance -= $principalAmount;
            }
        }
        
        return $schedule;
    }
    
    /**
     * Adjust loan schedule tenure/days and first payment date
     */
    public function adjustSchedule(Request $request, Loan $loan)
    {
        // Only allow adjustment for loans not yet disbursed
        if (in_array($loan->status, ['disbursed', 'active', 'overdue', 'completed', 'written_off', 'cancelled'])) {
            return redirect()->back()->with('error', 'Cannot adjust schedule for a loan that has already been disbursed or is active.');
        }

        $frequency = $loan->repayment_frequency ?? 'monthly';

        $rules = [
            'first_payment_date' => 'nullable|date|after_or_equal:today',
        ];

        // Validate tenure value based on frequency
        if ($frequency === 'daily') {
            $rules['tenure_value'] = 'required|integer|min:1|max:3650';
        } elseif ($frequency === 'weekly') {
            $rules['tenure_value'] = 'required|integer|min:1|max:520';
        } elseif ($frequency === 'quarterly') {
            $rules['tenure_value'] = 'required|integer|min:1|max:120';
        } else {
            $rules['tenure_value'] = 'required|integer|min:1|max:360';
        }

        $request->validate($rules);

        $tenureValue = (int) $request->tenure_value;

        // Convert tenure value to months for storage
        $tenureInMonths = match($frequency) {
            'daily' => $tenureValue / 30,
            'weekly' => $tenureValue / 4,
            'quarterly' => $tenureValue * 3,
            default => $tenureValue, // monthly
        };

        // Round to 2 decimal places
        $tenureInMonths = round($tenureInMonths, 2);

        // Enforce product min/max tenure if product exists
        if ($loan->loanProduct) {
            $minMonths = $loan->loanProduct->min_tenure_months;
            $maxMonths = $loan->loanProduct->max_tenure_months;
            
            if ($tenureInMonths < $minMonths || $tenureInMonths > $maxMonths) {
                $unitLabel = match($frequency) {
                    'daily' => 'days',
                    'weekly' => 'weeks',
                    'quarterly' => 'quarters',
                    default => 'months',
                };
                $minDisplay = match($frequency) {
                    'daily' => round($minMonths * 30),
                    'weekly' => round($minMonths * 4),
                    'quarterly' => round($minMonths / 3),
                    default => $minMonths,
                };
                $maxDisplay = match($frequency) {
                    'daily' => round($maxMonths * 30),
                    'weekly' => round($maxMonths * 4),
                    'quarterly' => round($maxMonths / 3),
                    default => $maxMonths,
                };
                
                return redirect()->back()->with('error', "Tenure must be between {$minDisplay} and {$maxDisplay} {$unitLabel} for this product.");
            }
        }

        // Update loan tenure
        $updateData = ['loan_tenure_months' => $tenureInMonths];

        if ($request->first_payment_date) {
            $updateData['first_payment_date'] = $request->first_payment_date;
        }

        $loan->update($updateData);

        // If the loan has existing schedules (approved loan), regenerate them
        if ($loan->schedules()->count() > 0 && $loan->approved_amount) {
            $loan->calculateLoanSchedule();
        }

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan schedule adjusted successfully.');
    }

    /**
     * Submit loan for review
     */
    public function submitForReview(Loan $loan)
    {
        // Check if loan has documents
        if (!$loan->documents || count($loan->documents) === 0) {
            return redirect()->back()->with('error', 'Please upload at least one document before submitting for review.');
        }
        
        // Update loan status
        $loan->update([
            'status' => 'under_review',
        ]);
        
        SystemLog::log(
            'Loan submitted for review',
            'Loan ' . $loan->loan_number . ' submitted for review',
            'info',
            $loan,
            auth()->id()
        );
        
        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan submitted for review successfully.');
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
            
            if ($loan->calculated_outstanding_amount <= 0) {
                return redirect()->back()->with('error', 'This loan has no outstanding balance.');
            }

            $allocator = new LoanRepaymentAllocator();
            $result = $allocator->allocate($loan, (float) $paymentAmount);

            if ($result['total_applied'] <= 0) {
                return redirect()->back()->with('error', 'No outstanding installments to apply this payment to.');
            }

            $principalAmount = $result['total_principal'];
            $interestAmount = $result['total_interest'];
            $appliedAmount = $result['total_applied'];
            $firstScheduleId = $result['allocations'][0]['schedule_id'] ?? null;

            $transaction = LoanTransaction::create([
                'loan_id' => $loan->id,
                'loan_schedule_id' => $firstScheduleId,
                'transaction_number' => LoanTransaction::generateTransactionNumber(),
                'transaction_type' => 'principal_payment',
                'amount' => $appliedAmount,
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

            $allocator->syncLoanTotals($loan);

            $this->recordLoanRepaymentInLedger($loan, $appliedAmount, $principalAmount, $interestAmount);

            // Build receipt data for session flash
            $loan->load('loanProduct', 'client');
            $organization = Organization::find($loan->organization_id);
            $processedBy = auth()->user();

            $receiptData = [
                'receipt_number' => $transaction->transaction_number,
                'date' => now()->format('M d, Y'),
                'time' => now()->format('h:i A'),
                'organization' => [
                    'name' => $organization->name ?? 'Organization',
                    'address' => $organization->address ?? '',
                    'city' => $organization->city ?? '',
                    'phone' => $organization->phone ?? '',
                    'email' => $organization->email ?? '',
                ],
                'client' => [
                    'name' => $loan->client ? ($loan->client->first_name . ' ' . $loan->client->last_name) : 'N/A',
                    'client_number' => $loan->client->client_number ?? 'N/A',
                    'phone' => $loan->client->phone_number ?? 'N/A',
                ],
                'loan' => [
                    'loan_number' => $loan->loan_number,
                    'product_name' => $loan->loanProduct->name ?? 'N/A',
                    'outstanding_before' => ($loan->outstanding_balance ?? 0) + $appliedAmount,
                    'outstanding_after' => $loan->outstanding_balance ?? 0,
                ],
                'payment' => [
                    'type' => 'Loan Repayment',
                    'amount' => $appliedAmount,
                    'method' => ucfirst(str_replace('_', ' ', $request->payment_method)),
                    'reference' => $request->payment_reference ?? 'N/A',
                    'notes' => $request->payment_notes ?? '',
                ],
                'processed_by' => $processedBy ? ($processedBy->first_name . ' ' . $processedBy->last_name) : 'System',
            ];

            return redirect()->back()
                ->with('success', 'Payment processed successfully. Amount: TZS ' . number_format($appliedAmount, 2))
                ->with('receipt', $receiptData);

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
        $request->validate([
            'closure_reason' => 'required|string|max:1000',
            'return_amount' => 'nullable|numeric|min:0',
            'forgiven_amount' => 'nullable|numeric|min:0',
        ]);

        // Check permission
        if (!in_array(auth()->user()->role, ['admin', 'manager', 'super_admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to close loans.');
        }

        // Only active, overdue, or disbursed loans can be closed
        if (!in_array($loan->status, ['active', 'overdue', 'disbursed'])) {
            return redirect()->back()->with('error', 'Only active, overdue, or disbursed loans can be closed.');
        }

        $returnAmount = (float) ($request->return_amount ?? 0);
        $forgivenAmount = (float) ($request->forgiven_amount ?? 0);
        $outstandingBalance = (float) ($loan->outstanding_balance ?? 0);

        // Store closure details in metadata
        $metadata = is_array($loan->metadata) ? $loan->metadata : [];
        $metadata['closure'] = [
            'outstanding_at_closure' => $outstandingBalance,
            'return_amount' => $returnAmount,
            'forgiven_amount' => $forgivenAmount,
            'closed_by' => auth()->id(),
            'closed_by_name' => auth()->user()->name,
            'closed_at' => now()->toDateTimeString(),
            'reason' => $request->closure_reason,
        ];

        $loan->update([
            'status' => 'completed',
            'closure_date' => now()->toDateString(),
            'closure_reason' => $request->closure_reason,
            'closed_by' => auth()->id(),
            'outstanding_balance' => max(0, $outstandingBalance - $returnAmount - $forgivenAmount),
            'write_off_amount' => $forgivenAmount > 0 ? $forgivenAmount : $loan->write_off_amount,
            'metadata' => $metadata,
        ]);

        // Mark remaining schedules as closed/waived
        $loan->schedules()
            ->where('status', '!=', 'paid')
            ->update(['status' => 'waived']);

        // Log the closure
        $comments = is_array($loan->comments) ? $loan->comments : [];
        $comments[] = [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_role' => auth()->user()->role,
            'comment_type' => 'closure',
            'comment' => "Loan closed. Return: TZS " . number_format($returnAmount, 2) 
                        . " | Forgiven: TZS " . number_format($forgivenAmount, 2) 
                        . " | Reason: " . $request->closure_reason,
            'created_at' => now()->toDateTimeString(),
        ];
        $loan->update(['comments' => $comments]);

        SystemLog::log(
            'Loan closed',
            'Loan ' . $loan->loan_number . ' has been closed. Return: TZS ' . number_format($returnAmount, 2) . ', Forgiven: TZS ' . number_format($forgivenAmount, 2),
            'info',
            $loan,
            auth()->id(),
            ['return_amount' => $returnAmount, 'forgiven_amount' => $forgivenAmount, 'closure_reason' => $request->closure_reason]
        );

        // Record closure in repayment_records so it appears on daily repayments report (who closed it, when)
        RepaymentRecord::create([
            'organization_id' => $loan->organization_id,
            'branch_id' => $loan->branch_id,
            'loan_id' => $loan->id,
            'client_id' => $loan->client_id,
            'loan_transaction_id' => null,
            'transaction_number' => 'CLOSE-' . now()->format('YmdHis') . '-' . $loan->id,
            'amount' => $returnAmount,
            'principal_amount' => $returnAmount,
            'interest_amount' => 0,
            'payment_method' => null,
            'payment_date' => now()->toDateString(),
            'recorded_by' => auth()->id(),
            'collection_account_id' => null,
            'reference_number' => null,
            'notes' => 'Loan closed. ' . ($request->closure_reason ?? '') . ($forgivenAmount > 0 ? ' Forgiven: TZS ' . number_format($forgivenAmount, 2) : ''),
            'payment_type' => 'loan_closure',
        ]);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan has been closed successfully.');
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