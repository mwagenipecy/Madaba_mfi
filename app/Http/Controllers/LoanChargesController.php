<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\LoanSchedule;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanChargesController extends Controller
{
    /**
     * Display a listing of loan charges
     */
    public function index()
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        
        // Get loans with their charges and arrears information
        $loans = Loan::where('organization_id', $organizationId)
            ->with(['client', 'loanProduct', 'loanTransactions' => function($query) {
                $query->whereIn('transaction_type', ['interest', 'penalty', 'late_fee', 'processing_fee'])
                      ->orderBy('transaction_date', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate summary statistics
        $totalOutstandingCharges = LoanTransaction::whereHas('loan', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->whereIn('transaction_type', ['interest', 'penalty', 'late_fee', 'processing_fee'])
            ->where('status', 'pending')
            ->sum('amount');

        $totalCollectedCharges = LoanTransaction::whereHas('loan', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->whereIn('transaction_type', ['interest', 'penalty', 'late_fee', 'processing_fee'])
            ->where('status', 'completed')
            ->sum('amount');

        $loansWithArrears = Loan::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereHas('loanTransactions', function($query) {
                $query->whereIn('transaction_type', ['penalty', 'late_fee'])
                      ->where('status', 'pending');
            })
            ->count();

        return view('loan-charges.index', compact(
            'loans', 
            'totalOutstandingCharges', 
            'totalCollectedCharges', 
            'loansWithArrears'
        ));
    }

    /**
     * Show the form for creating a new charge
     */
    public function create()
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        
        $loans = Loan::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with(['client', 'loanProduct'])
            ->orderBy('loan_number')
            ->get();

        return view('loan-charges.create', compact('loans'));
    }

    /**
     * Store a newly created charge
     */
    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'transaction_type' => 'required|in:interest,penalty,late_fee,processing_fee',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'transaction_date' => 'required|date|before_or_equal:today',
        ], [
            'loan_id.required' => 'Please select a loan.',
            'loan_id.exists' => 'Selected loan does not exist.',
            'transaction_type.required' => 'Please select a charge type.',
            'transaction_type.in' => 'Invalid charge type selected.',
            'amount.required' => 'Charge amount is required.',
            'amount.numeric' => 'Charge amount must be a valid number.',
            'amount.min' => 'Charge amount must be greater than 0.',
            'description.required' => 'Description is required.',
            'transaction_date.required' => 'Transaction date is required.',
            'transaction_date.before_or_equal' => 'Transaction date cannot be in the future.',
        ]);

        // Verify loan belongs to user's organization
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        $loan = Loan::where('id', $request->loan_id)
                   ->where('organization_id', $organizationId)
                   ->first();

        if (!$loan) {
            return redirect()->back()
                ->withErrors(['loan_id' => 'Selected loan does not belong to your organization.'])
                ->withInput();
        }

        // Create the charge transaction
        $transaction = LoanTransaction::create([
            'loan_id' => $loan->id,
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('loan-charges.index')
            ->with('success', 'Loan charge added successfully.');
    }

    /**
     * Display the specified charge
     */
    public function show(LoanTransaction $loanTransaction)
    {
        $loanTransaction->load(['loan.client', 'loan.loanProduct', 'createdBy']);
        
        return view('loan-charges.show', compact('loanTransaction'));
    }

    /**
     * Update charge status (mark as collected)
     */
    public function updateStatus(Request $request, LoanTransaction $loanTransaction)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $loanTransaction->update([
            'status' => $request->status,
            'notes' => $request->notes,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        $statusText = ucfirst($request->status);
        return redirect()->back()
            ->with('success', "Charge marked as {$statusText} successfully.");
    }

    /**
     * Show loans with arrears
     */
    public function arrears()
    {
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        
        $loans = Loan::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with(['client', 'loanProduct', 'loanTransactions' => function($query) {
                $query->whereIn('transaction_type', ['penalty', 'late_fee'])
                      ->where('status', 'pending')
                      ->orderBy('transaction_date', 'desc');
            }])
            ->whereHas('loanTransactions', function($query) {
                $query->whereIn('transaction_type', ['penalty', 'late_fee'])
                      ->where('status', 'pending');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalArrearsAmount = LoanTransaction::whereHas('loan', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->whereIn('transaction_type', ['penalty', 'late_fee'])
            ->where('status', 'pending')
            ->sum('amount');

        return view('loan-charges.arrears', compact('loans', 'totalArrearsAmount'));
    }

    /**
     * Bulk update charges status
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'charge_ids' => 'required|array|min:1',
            'charge_ids.*' => 'exists:loan_transactions,id',
            'status' => 'required|in:pending,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        
        $updatedCount = LoanTransaction::whereIn('id', $request->charge_ids)
            ->whereHas('loan', function($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

        $statusText = ucfirst($request->status);
        return redirect()->back()
            ->with('success', "{$updatedCount} charges marked as {$statusText} successfully.");
    }

    /**
     * Process payment for a charge
     */
    public function processPayment(Request $request, LoanTransaction $loanTransaction)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $loanTransaction->amount,
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        // Verify the charge belongs to user's organization
        $organizationId = Auth::user()->organization_id ?? Organization::first()?->id;
        $loan = $loanTransaction->loan;
        
        if ($loan->organization_id !== $organizationId) {
            return redirect()->back()
                ->withErrors(['error' => 'You do not have permission to process this payment.']);
        }

        // Check if charge is already paid
        if ($loanTransaction->status === 'completed') {
            return redirect()->back()
                ->withErrors(['error' => 'This charge has already been paid.']);
        }

        DB::beginTransaction();
        try {
            // Update the charge status
            $loanTransaction->update([
                'status' => 'completed',
                'notes' => $request->notes,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Create a payment transaction record
            LoanTransaction::create([
                'loan_id' => $loanTransaction->loan_id,
                'transaction_type' => 'payment',
                'amount' => $request->payment_amount,
                'description' => "Payment for {$loanTransaction->transaction_type}: {$loanTransaction->description}",
                'transaction_date' => now(),
                'status' => 'completed',
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'created_by' => Auth::id(),
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Update loan outstanding balance
            $loan->outstanding_balance = max(0, $loan->outstanding_balance - $request->payment_amount);
            $loan->save();

            DB::commit();

            return redirect()->route('loan-charges.show', $loanTransaction)
                ->with('success', 'Payment processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to process payment. Please try again.']);
        }
    }
}
