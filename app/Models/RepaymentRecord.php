<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepaymentRecord extends Model
{
    protected $table = 'repayment_records';

    protected $fillable = [
        'organization_id',
        'branch_id',
        'loan_id',
        'client_id',
        'loan_transaction_id',
        'transaction_number',
        'amount',
        'principal_amount',
        'interest_amount',
        'payment_method',
        'payment_date',
        'recorded_by',
        'collection_account_id',
        'reference_number',
        'notes',
        'payment_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function loanTransaction(): BelongsTo
    {
        return $this->belongsTo(LoanTransaction::class, 'loan_transaction_id');
    }

    /** User who recorded this repayment (who did the transaction) */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function collectionAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'collection_account_id');
    }
}
