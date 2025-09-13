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
        Schema::table('loans', function (Blueprint $table) {
            // Disbursement tracking
            $table->unsignedBigInteger('disbursement_account_id')->nullable()->after('branch_id'); // Source account for disbursement
            $table->string('disbursement_reference')->nullable()->after('disbursement_account_id'); // Reference for disbursement
            
            // Loan closure and write-off
            $table->date('closure_date')->nullable()->after('maturity_date');
            $table->text('closure_reason')->nullable()->after('closure_date');
            $table->unsignedBigInteger('closed_by')->nullable()->after('closure_reason');
            
            // Loan restructuring
            $table->boolean('is_restructured')->default(false)->after('closed_by');
            $table->unsignedBigInteger('original_loan_id')->nullable()->after('is_restructured'); // Reference to original loan if restructured
            $table->text('restructure_reason')->nullable()->after('original_loan_id');
            $table->unsignedBigInteger('restructured_by')->nullable()->after('restructure_reason');
            $table->date('restructure_date')->nullable()->after('restructured_by');
            
            // Loan top-up
            $table->boolean('is_top_up')->default(false)->after('restructure_date');
            $table->unsignedBigInteger('original_loan_id_for_topup')->nullable()->after('is_top_up'); // Reference to original loan if top-up
            $table->decimal('top_up_amount', 15, 2)->default(0.00)->after('original_loan_id_for_topup');
            $table->date('top_up_date')->nullable()->after('top_up_amount');
            $table->unsignedBigInteger('top_up_processed_by')->nullable()->after('top_up_date');
            
            // Write-off details
            $table->decimal('write_off_amount', 15, 2)->default(0.00)->after('top_up_processed_by');
            $table->date('write_off_date')->nullable()->after('write_off_amount');
            $table->text('write_off_reason')->nullable()->after('write_off_date');
            $table->unsignedBigInteger('write_off_by')->nullable()->after('write_off_reason');
            
            // Additional foreign keys
            $table->foreign('disbursement_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('original_loan_id')->references('id')->on('loans')->onDelete('set null');
            $table->foreign('restructured_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('original_loan_id_for_topup')->references('id')->on('loans')->onDelete('set null');
            $table->foreign('top_up_processed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('write_off_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['disbursement_account_id']);
            $table->dropForeign(['closed_by']);
            $table->dropForeign(['original_loan_id']);
            $table->dropForeign(['restructured_by']);
            $table->dropForeign(['original_loan_id_for_topup']);
            $table->dropForeign(['top_up_processed_by']);
            $table->dropForeign(['write_off_by']);
            
            $table->dropColumn([
                'disbursement_account_id',
                'disbursement_reference',
                'closure_date',
                'closure_reason',
                'closed_by',
                'is_restructured',
                'original_loan_id',
                'restructure_reason',
                'restructured_by',
                'restructure_date',
                'is_top_up',
                'original_loan_id_for_topup',
                'top_up_amount',
                'top_up_date',
                'top_up_processed_by',
                'write_off_amount',
                'write_off_date',
                'write_off_reason',
                'write_off_by',
            ]);
        });
    }
};
