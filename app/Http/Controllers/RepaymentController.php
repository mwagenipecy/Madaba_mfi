<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\LoanSchedule;
use App\Models\GeneralLedger;
use App\Models\Account;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepaymentController extends Controller
{
    /**
     * Show repayment interface
     */
    public function index()
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get collection accounts for payment processing
        $collectionAccounts = Account::where('organization_id', $organizationId)
            ->where('name', 'like', '%collection%')
            ->orWhere('name', 'like', '%cash%')
            ->orWhere('name', 'like', '%bank%')
            ->where('status', 'active')
            ->get();

        return view('repayments.index', compact('collectionAccounts'));
    }

    /**
     * Search clients for repayment
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('q');
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;

        $clients = Client::where('organization_id', $organizationId)
            ->where(function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('client_number', 'like', "%{$query}%")
                  ->orWhere('phone_number', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->with(['loans' => function($query) {
                $query->whereIn('status', ['active', 'overdue'])
                      ->with(['loanProduct', 'schedules' => function($q) {
                          $q->whereIn('status', ['pending', 'overdue', 'partial'])
                            ->orderBy('due_date');
                      }]);
            }])
            ->limit(10)
            ->get();

        return response()->json([
            'clients' => $clients->map(function($client) {
                $activeLoans = $client->loans->filter(function($loan) {
                    return in_array($loan->status, ['active', 'overdue']);
                });

                return [
                    'id' => $client->id,
                    'name' => $client->first_name . ' ' . $client->last_name,
                    'client_number' => $client->client_number,
                    'phone' => $client->phone_number,
                    'email' => $client->email,
                    'active_loans_count' => $activeLoans->count(),
                    'total_outstanding' => $activeLoans->sum('outstanding_balance'),
                    'loans' => $activeLoans->map(function($loan) {
                        return [
                            'id' => $loan->id,
                            'loan_number' => $loan->loan_number,
                            'product_name' => $loan->loanProduct->name ?? 'N/A',
                            'outstanding_balance' => $loan->outstanding_balance,
                            'status' => $loan->status,
                            'next_due_amount' => $loan->schedules->where('status', 'pending')->first()->total_amount ?? 0,
                            'next_due_date' => $loan->schedules->where('status', 'pending')->first()->due_date ?? null,
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * Get client details and loans for repayment
     */
    public function getClientDetails(Request $request, Client $client)
    {
        $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
        
        // Get active loans with schedules
        $loans = $client->loans()
            ->where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'overdue'])
            ->with([
                'loanProduct',
                'schedules' => function($query) {
                    $query->whereIn('status', ['pending', 'overdue', 'partial'])
                          ->orderBy('due_date');
                },
                'transactions' => function($query) {
                    $query->whereIn('transaction_type', ['principal_payment', 'interest_payment'])
                          ->latest()
                          ->limit(5);
                }
            ])
            ->get();

        // Get outstanding charges (loan transactions with pending charges)
        $charges = LoanTransaction::whereHas('loan', function($query) use ($client, $organizationId) {
                $query->where('client_id', $client->id)
                      ->where('organization_id', $organizationId);
            })
            ->whereIn('transaction_type', ['penalty_fee', 'late_fee', 'processing_fee', 'insurance_fee'])
            ->where('status', 'pending')
            ->with(['loan.loanProduct'])
            ->get();

        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->first_name . ' ' . $client->last_name,
                'client_number' => $client->client_number,
                'phone' => $client->phone_number,
                'email' => $client->email,
            ],
            'loans' => $loans->map(function($loan) {
                $nextSchedule = $loan->schedules->first();
                return [
                    'id' => $loan->id,
                    'loan_number' => $loan->loan_number,
                    'product_name' => $loan->loanProduct->name ?? 'N/A',
                    'outstanding_balance' => $loan->outstanding_balance,
                    'status' => $loan->status,
                    'next_due_amount' => $nextSchedule ? $nextSchedule->total_amount : 0,
                    'next_due_date' => $nextSchedule ? $nextSchedule->due_date : null,
                    'total_overdue' => $loan->schedules->where('status', 'overdue')->sum('outstanding_amount'),
                    'schedules' => $loan->schedules->take(5)->map(function($schedule) {
                        return [
                            'id' => $schedule->id,
                            'installment_number' => $schedule->installment_number,
                            'due_date' => $schedule->due_date,
                            'total_amount' => $schedule->total_amount,
                            'paid_amount' => $schedule->paid_amount,
                            'outstanding_amount' => $schedule->outstanding_amount,
                            'status' => $schedule->status,
                        ];
                    })
                ];
            }),
            'charges' => $charges->map(function($charge) {
                return [
                    'id' => $charge->id,
                    'loan_number' => $charge->loan->loan_number,
                    'charge_type' => $charge->transaction_type,
                    'amount' => $charge->amount,
                    'due_date' => $charge->transaction_date,
                    'status' => $charge->status,
                    'description' => $charge->notes,
                ];
            })
        ]);
    }

    /**
     * Process repayment
     */
    public function processRepayment(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'payment_type' => 'required|in:loan_repayment,charge_payment,both',
            'payment_amount' => 'required|numeric|min:0.01',
            'collection_account_id' => 'required|exists:accounts,id',
            'payment_method' => 'required|string|in:cash,bank_transfer,mobile_money,check,other',
            'payment_reference' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string|max:1000',
            'loan_id' => 'required_if:payment_type,loan_repayment,both|nullable|exists:loans,id',
            'charge_id' => 'required_if:payment_type,charge_payment|nullable|exists:loan_transactions,id',
            'schedule_id' => 'nullable|exists:loan_schedules,id',
        ]);

        try {
            DB::beginTransaction();

            $organizationId = auth()->user()->organization_id ?? Organization::first()?->id;
            $paymentAmount = $request->payment_amount;
            $remainingAmount = $paymentAmount;

            // Process loan repayment
            if (in_array($request->payment_type, ['loan_repayment', 'both'])) {
                $loan = Loan::findOrFail($request->loan_id);
                
                if ($loan->outstanding_balance <= 0) {
                    throw new \Exception('This loan has no outstanding balance.');
                }

                $loanPaymentAmount = min($remainingAmount, $loan->outstanding_balance);
                
                // Process loan payment
                $this->processLoanPayment($loan, $loanPaymentAmount, $request, $organizationId);
                $remainingAmount -= $loanPaymentAmount;
            }

            // Process charge payment
            if (in_array($request->payment_type, ['charge_payment', 'both']) && $remainingAmount > 0) {
                $charge = LoanTransaction::findOrFail($request->charge_id);
                
                if ($charge->status === 'completed') {
                    throw new \Exception('This charge has already been paid.');
                }

                $chargePaymentAmount = min($remainingAmount, $charge->amount);
                
                // Process charge payment
                $this->processChargePayment($charge, $chargePaymentAmount, $request, $organizationId);
                $remainingAmount -= $chargePaymentAmount;
            }

            // Record in general ledger
            $this->recordPaymentInLedger($request, $paymentAmount, $organizationId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully. Amount: TZS ' . number_format($paymentAmount, 2),
                'processed_amount' => $paymentAmount - $remainingAmount,
                'remaining_amount' => $remainingAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process loan payment
     */
    private function processLoanPayment(Loan $loan, $amount, Request $request, $organizationId)
    {
        // Calculate principal and interest portions
        $principalAmount = 0;
        $interestAmount = 0;
        
        // Get the next due schedule
        $nextSchedule = $loan->schedules()->where('status', 'pending')->orderBy('due_date')->first();
        
        if ($nextSchedule) {
            // If payment is less than or equal to scheduled amount, split proportionally
            if ($amount <= $nextSchedule->outstanding_amount) {
                $principalAmount = $amount * ($nextSchedule->principal_amount / $nextSchedule->total_amount);
                $interestAmount = $amount * ($nextSchedule->interest_amount / $nextSchedule->total_amount);
            } else {
                // If payment exceeds scheduled amount, apply to principal
                $interestAmount = $nextSchedule->interest_amount;
                $principalAmount = $amount - $interestAmount;
            }
        } else {
            // No schedule, apply to principal
            $principalAmount = $amount;
        }

        // Create loan transaction
        $transaction = LoanTransaction::create([
            'loan_id' => $loan->id,
            'loan_schedule_id' => $nextSchedule ? $nextSchedule->id : null,
            'transaction_number' => LoanTransaction::generateTransactionNumber(),
            'transaction_type' => 'principal_payment', // Use the correct enum value
            'amount' => $amount,
            'principal_amount' => $principalAmount,
            'interest_amount' => $interestAmount,
            'transaction_date' => now(),
            'payment_method' => $request->payment_method,
            'reference_number' => $request->payment_reference,
            'notes' => $request->payment_notes,
            'processed_by' => auth()->id(),
            'organization_id' => $organizationId,
            'branch_id' => $loan->branch_id,
            'status' => 'completed',
        ]);

        // Update loan outstanding balance
        $loan->outstanding_balance -= $principalAmount;
        $loan->paid_amount += $amount;
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
            $nextSchedule->paid_amount += $amount;
            if ($nextSchedule->paid_amount >= $nextSchedule->total_amount) {
                $nextSchedule->status = 'paid';
                $nextSchedule->paid_date = now();
            } else {
                $nextSchedule->status = 'partial';
            }
            $nextSchedule->save();
        }
    }

    /**
     * Process charge payment
     */
    private function processChargePayment(LoanTransaction $charge, $amount, Request $request, $organizationId)
    {
        // Update charge status
        if ($amount >= $charge->amount) {
            $charge->status = 'completed';
            $charge->notes = $charge->notes . ' - Paid: ' . now()->format('Y-m-d H:i:s');
        } else {
            $charge->status = 'partial';
            $charge->notes = $charge->notes . ' - Partial payment: TZS ' . number_format($amount, 2);
        }
        $charge->save();

        // Create a separate payment transaction record
        LoanTransaction::create([
            'loan_id' => $charge->loan_id,
            'transaction_number' => LoanTransaction::generateTransactionNumber(),
            'transaction_type' => 'principal_payment', // Use principal_payment as the payment transaction type
            'amount' => $amount,
            'fee_amount' => $amount,
            'transaction_date' => now(),
            'payment_method' => $request->payment_method,
            'reference_number' => $request->payment_reference,
            'notes' => $request->payment_notes . ' - Charge payment for: ' . $charge->transaction_type,
            'processed_by' => auth()->id(),
            'organization_id' => $organizationId,
            'branch_id' => $charge->loan->branch_id,
            'status' => 'completed',
        ]);
    }

    /**
     * Record payment in general ledger
     */
    private function recordPaymentInLedger(Request $request, $amount, $organizationId)
    {
        $collectionAccount = Account::findOrFail($request->collection_account_id);
        $transactionId = 'REP-' . date('YmdHis') . '-' . str_pad(substr(microtime(), 2, 4), 4, '0', STR_PAD_LEFT);

        // Debit: Collection Account (Cash received - Asset increases)
        GeneralLedger::createTransaction(
            $transactionId . '-COLLECTION',
            $collectionAccount,
            'debit',
            $amount,
            "Payment received - {$request->payment_type}",
            'Payment',
            null,
            auth()->id()
        );

        // Get loan product accounts for credit entries
        if (in_array($request->payment_type, ['loan_repayment', 'both']) && $request->loan_id) {
            $loan = Loan::find($request->loan_id);
            $loanProduct = $loan->loanProduct;
            
            if ($loanProduct && $loanProduct->principalAccount) {
                // Credit: Principal Account (Asset decreases - loan receivable reduces)
                GeneralLedger::createTransaction(
                    $transactionId . '-PRINCIPAL',
                    $loanProduct->principalAccount,
                    'credit',
                    $amount,
                    "Loan repayment - {$loan->loan_number}",
                    'LoanTransaction',
                    $loan->id,
                    auth()->id()
                );
            }
        }
    }
}
