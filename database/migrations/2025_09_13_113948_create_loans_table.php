<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            
            // Basic Loan Information
            $table->string('loan_number')->unique(); // Auto-generated loan ID
            $table->unsignedBigInteger('client_id'); // Client who applied for loan
            $table->unsignedBigInteger('loan_product_id'); // Loan product type
            $table->unsignedBigInteger('organization_id'); // Organization
            $table->unsignedBigInteger('branch_id')->nullable(); // Branch handling the loan
            $table->unsignedBigInteger('loan_officer_id')->nullable(); // Loan officer assigned
            
            // Loan Details
            $table->decimal('loan_amount', 15, 2); // Requested loan amount
            $table->decimal('approved_amount', 15, 2)->nullable(); // Approved loan amount
            $table->decimal('interest_rate', 5, 2); // Interest rate (from loan product or custom)
            $table->enum('interest_calculation_method', ['flat', 'reducing'])->default('flat');
            $table->integer('loan_tenure_months'); // Loan duration in months
            $table->enum('repayment_frequency', ['daily', 'weekly', 'monthly', 'quarterly'])->default('monthly');
            $table->date('application_date'); // Date loan was applied
            $table->date('approval_date')->nullable(); // Date loan was approved
            $table->date('disbursement_date')->nullable(); // Date loan was disbursed
            $table->date('first_payment_date')->nullable(); // First repayment date
            $table->date('maturity_date')->nullable(); // Loan maturity date
            
            // Financial Calculations
            $table->decimal('total_interest', 15, 2)->nullable(); // Total interest to be paid
            $table->decimal('total_amount', 15, 2)->nullable(); // Total amount to be repaid
            $table->decimal('monthly_payment', 15, 2)->nullable(); // Monthly payment amount
            $table->decimal('processing_fee', 15, 2)->default(0.00); // Processing fee
            $table->decimal('insurance_fee', 15, 2)->default(0.00); // Insurance fee
            $table->decimal('late_fee', 15, 2)->default(0.00); // Late payment fee
            $table->decimal('penalty_fee', 15, 2)->default(0.00); // Penalty fee
            $table->decimal('other_fees', 15, 2)->default(0.00); // Other miscellaneous fees
            
            // Loan Status and Workflow
            $table->enum('status', [
                'pending', 'under_review', 'approved', 'rejected', 'disbursed', 
                'active', 'overdue', 'completed', 'written_off', 'cancelled'
            ])->default('pending');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable(); // User who approved
            $table->unsignedBigInteger('rejected_by')->nullable(); // User who rejected
            $table->text('approval_notes')->nullable(); // Approval notes
            $table->text('rejection_reason')->nullable(); // Rejection reason
            
            // Payment Information
            $table->decimal('paid_amount', 15, 2)->default(0.00); // Total amount paid
            $table->decimal('outstanding_balance', 15, 2)->nullable(); // Outstanding balance
            $table->decimal('overdue_amount', 15, 2)->default(0.00); // Overdue amount
            $table->integer('overdue_days')->default(0); // Days overdue
            $table->integer('payments_made')->default(0); // Number of payments made
            $table->integer('total_payments')->nullable(); // Total number of payments expected
            
            // Collateral Information
            $table->boolean('requires_collateral')->default(false);
            $table->text('collateral_description')->nullable();
            $table->decimal('collateral_value', 15, 2)->nullable();
            $table->string('collateral_location')->nullable();
            
            // Additional Information
            $table->text('loan_purpose')->nullable(); // Purpose of the loan
            $table->text('guarantor_name')->nullable(); // Guarantor information
            $table->string('guarantor_phone')->nullable();
            $table->text('guarantor_address')->nullable();
            $table->text('notes')->nullable(); // Additional notes
            $table->json('metadata')->nullable(); // Additional flexible data
            
            // System Fields
            $table->timestamps();
            $table->softDeletes(); // For soft deletion
            
            // Foreign Keys and Indexes
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('loan_product_id')->references('id')->on('loan_products')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('loan_officer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['organization_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['loan_officer_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['loan_product_id', 'status']);
            $table->index(['application_date', 'organization_id']);
            $table->index(['approval_date', 'organization_id']);
            $table->index(['status', 'overdue_days']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
