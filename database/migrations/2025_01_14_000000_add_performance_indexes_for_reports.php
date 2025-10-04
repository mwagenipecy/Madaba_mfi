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
        Schema::table('loan_schedules', function (Blueprint $table) {
            // Add composite index for efficient querying in reports
            $table->index(['status', 'paid_date'], 'idx_loan_schedules_status_paid_date');
            $table->index(['loan_id', 'status', 'paid_date'], 'idx_loan_schedules_loan_status_paid_date');
        });

        Schema::table('loan_transactions', function (Blueprint $table) {
            // Add composite index for efficient querying in reports
            $table->index(['organization_id', 'transaction_type', 'status', 'transaction_date'], 'idx_loan_transactions_org_type_status_date');
            $table->index(['branch_id', 'transaction_type', 'status', 'transaction_date'], 'idx_loan_transactions_branch_type_status_date');
            $table->index(['transaction_type', 'status', 'transaction_date'], 'idx_loan_transactions_type_status_date');
        });

        Schema::table('loans', function (Blueprint $table) {
            // Add composite index for efficient querying in reports
            $table->index(['organization_id', 'branch_id', 'status'], 'idx_loans_org_branch_status');
            $table->index(['organization_id', 'status', 'approved_amount'], 'idx_loans_org_status_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_loan_schedules_status_paid_date');
            $table->dropIndex('idx_loan_schedules_loan_status_paid_date');
        });

        Schema::table('loan_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_loan_transactions_org_type_status_date');
            $table->dropIndex('idx_loan_transactions_branch_type_status_date');
            $table->dropIndex('idx_loan_transactions_type_status_date');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('idx_loans_org_branch_status');
            $table->dropIndex('idx_loans_org_status_amount');
        });
    }
};
