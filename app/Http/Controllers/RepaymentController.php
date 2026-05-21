<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\LoanSchedule;
use App\Models\RepaymentRecord;
use App\Models\GeneralLedger;
use App\Models\Account;
use App\Models\Organization;
use App\Services\LoanRepaymentAllocator;
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
            ->enabled()
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
                    'uuid' => $client->uuid,
                    'name' => $client->display_name,
                    'client_number' => $client->client_number,
                    'phone' => $client->phone_number,
                    'email' => $client->email,
                    'active_loans_count' => $activeLoans->count(),
                    'total_outstanding' => $activeLoans->sum(function ($loan) {
                        return $loan->calculated_outstanding_amount;
                    }),
                    'loans' => $activeLoans->map(function($loan) {
                        return [
                            'id' => $loan->id,
                            'loan_number' => $loan->loan_number,
                            'product_name' => $loan->loanProduct->name ?? 'N/A',
                            'outstanding_balance' => $loan->calculated_outstanding_amount,
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

        if ($client->organization_id !== $organizationId) {
            return response()->json(['message' => 'Client not found.'], 404);
        }
        
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
                'uuid' => $client->uuid,
                'name' => $client->display_name,
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
                    'outstanding_balance' => $loan->calculated_outstanding_amount,
                    'status' => $loan->status,
                    'next_due_amount' => $nextSchedule ? $nextSchedule->total_amount : 0,
                    'next_due_date' => $nextSchedule ? $nextSchedule->due_date : null,
                    'total_overdue' => $loan->schedules->where('status', 'overdue')->sum('outstanding_amount'),
                    'schedules' => $loan->schedules->map(function($schedule) {
                        $schedule->refreshStatus();
                        return [
                            'id' => $schedule->id,
                            'installment_number' => $schedule->installment_number,
                            'due_date' => $schedule->due_date->format('Y-m-d'),
                            'principal_amount' => $schedule->principal_amount,
                            'interest_amount' => $schedule->interest_amount,
                            'paid_principal' => $schedule->paid_principal_amount ?? 0,
                            'paid_interest' => $schedule->paid_interest_amount ?? 0,
                            'remaining_principal' => $schedule->remaining_principal,
                            'remaining_interest' => $schedule->remaining_interest,
                            'total_amount' => $schedule->total_amount,
                            'paid_amount' => $schedule->paid_amount,
                            'outstanding_amount' => $schedule->remaining_total,
                            'status' => $schedule->status,
                            'is_due' => $schedule->is_due_reached,
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
            $allocationResult = ['allocations' => [], 'unallocated' => 0, 'total_applied' => 0];

            // Process loan repayment
            if (in_array($request->payment_type, ['loan_repayment', 'both'])) {
                $loan = Loan::findOrFail($request->loan_id);
                
                if ($loan->calculated_outstanding_amount <= 0) {
                    throw new \Exception('This loan has no outstanding balance.');
                }

                $loanPaymentAmount = min($remainingAmount, $loan->calculated_outstanding_amount);
                
                $allocationResult = $this->processLoanPayment($loan, $loanPaymentAmount, $request, $organizationId);
                $remainingAmount -= $allocationResult['total_applied'];
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

            // Gather receipt data
            $client = Client::find($request->client_id);
            $loan = $request->loan_id ? Loan::with('loanProduct')->find($request->loan_id) : null;
            $charge = ($request->payment_type === 'charge_payment' && $request->charge_id) ? LoanTransaction::find($request->charge_id) : null;
            $organization = Organization::find($organizationId);
            $processedBy = auth()->user();
            $collectionAccount = Account::find($request->collection_account_id);

            // Get the transaction number from the most recent transaction for this loan
            $transactionNumber = LoanTransaction::where('loan_id', $request->loan_id)
                ->where('status', 'completed')
                ->latest()
                ->value('transaction_number') ?? ('RCP-' . date('YmdHis'));

            $receiptData = [
                'receipt_number' => $transactionNumber,
                'date' => now()->format('M d, Y'),
                'time' => now()->format('h:i A'),
                'organization' => [
                    'name' => $organization->name ?? 'Organization',
                    'address' => $organization->address ?? '',
                    'city' => $organization->city ?? '',
                    'phone' => $organization->phone ?? '',
                    'email' => $organization->email ?? '',
                    'logo' => $organization->logo_path ?? null,
                ],
                'client' => [
                    'name' => $client ? ($client->first_name . ' ' . $client->last_name) : 'N/A',
                    'client_number' => $client->client_number ?? 'N/A',
                    'phone' => $client->phone_number ?? 'N/A',
                ],
                'loan' => $loan ? [
                    'loan_number' => $loan->loan_number,
                    'product_name' => $loan->loanProduct->name ?? 'N/A',
                    'outstanding_before' => min(
                        $loan->total_required_repayment,
                        $loan->calculated_outstanding_amount + ($paymentAmount - $remainingAmount)
                    ),
                    'outstanding_after' => $loan->calculated_outstanding_amount ?? 0,
                ] : null,
                'charge' => $charge ? [
                    'type' => $charge->transaction_type,
                    'description' => $charge->notes ?? 'N/A',
                ] : null,
                'payment' => [
                    'type' => ucfirst(str_replace('_', ' ', $request->payment_type)),
                    'amount' => $paymentAmount,
                    'processed_amount' => $paymentAmount - $remainingAmount,
                    'remaining' => $remainingAmount,
                    'method' => ucfirst(str_replace('_', ' ', $request->payment_method)),
                    'reference' => $request->payment_reference ?? 'N/A',
                    'account' => $collectionAccount->name ?? 'N/A',
                    'notes' => $request->payment_notes ?? '',
                ],
                'processed_by' => $processedBy ? ($processedBy->first_name . ' ' . $processedBy->last_name) : 'System',
            ];

            $allocationDetails = $allocationResult['allocations'] ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully. Amount: TZS ' . number_format($paymentAmount - $remainingAmount, 2),
                'processed_amount' => $paymentAmount - $remainingAmount,
                'remaining_amount' => $remainingAmount,
                'allocation' => $allocationDetails,
                'unallocated' => $allocationResult['unallocated'] ?? 0,
                'receipt' => $receiptData,
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
     * Process loan payment with schedule-aware allocation.
     */
    private function processLoanPayment(Loan $loan, $amount, Request $request, $organizationId)
    {
        $allocator = new LoanRepaymentAllocator();
        $result = $allocator->allocate($loan, (float) $amount);

        if ($result['total_applied'] <= 0) {
            throw new \Exception('No outstanding installments to apply this payment to.');
        }

        $principalAmount = $result['total_principal'];
        $interestAmount = $result['total_interest'];
        $appliedAmount = $result['total_applied'];
        $firstScheduleId = $result['allocations'][0]['schedule_id'] ?? null;

        $allocationSummary = collect($result['allocations'])->map(function ($row) {
            $mode = $row['allocation_mode'] === 'due_interest_first' ? 'Due: interest→principal' : 'Early: principal→interest';
            return "#{$row['installment_number']} ({$mode}): TZS " . number_format($row['total'], 2)
                . " [P: " . number_format($row['principal'], 2) . ", I: " . number_format($row['interest'], 2) . "]";
        })->implode('; ');

        $notes = trim(($request->payment_notes ?? '') . "\nAllocation: " . $allocationSummary);

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
            'reference_number' => $request->payment_reference,
            'notes' => $notes,
            'processed_by' => auth()->id(),
            'organization_id' => $organizationId,
            'branch_id' => $loan->branch_id,
            'status' => 'completed',
        ]);

        RepaymentRecord::create([
            'organization_id' => $organizationId,
            'branch_id' => $loan->branch_id,
            'loan_id' => $loan->id,
            'client_id' => $loan->client_id,
            'loan_transaction_id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'amount' => $appliedAmount,
            'principal_amount' => $principalAmount,
            'interest_amount' => $interestAmount,
            'payment_method' => $request->payment_method,
            'payment_date' => $transaction->transaction_date,
            'recorded_by' => auth()->id(),
            'collection_account_id' => $request->collection_account_id ?? null,
            'reference_number' => $request->payment_reference,
            'notes' => $notes,
            'payment_type' => 'loan_repayment',
        ]);

        $allocator->syncLoanTotals($loan);

        return $result;
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
        $transaction = LoanTransaction::create([
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

        // Record in dedicated repayment_records table (who did the transaction)
        RepaymentRecord::create([
            'organization_id' => $organizationId,
            'branch_id' => $charge->loan->branch_id,
            'loan_id' => $charge->loan_id,
            'client_id' => $charge->loan->client_id,
            'loan_transaction_id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'amount' => $amount,
            'principal_amount' => 0,
            'interest_amount' => 0,
            'payment_method' => $request->payment_method,
            'payment_date' => $transaction->transaction_date,
            'recorded_by' => auth()->id(),
            'collection_account_id' => $request->collection_account_id ?? null,
            'reference_number' => $request->payment_reference,
            'notes' => $request->payment_notes . ' - Charge payment for: ' . $charge->transaction_type,
            'payment_type' => 'charge_payment',
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
