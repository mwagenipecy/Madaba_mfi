<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper function to check if index exists
        $indexExists = function ($tableName, $indexName) {
            try {
                $result = DB::select(
                    "SELECT COUNT(*) as count FROM information_schema.statistics 
                     WHERE table_schema = DATABASE() 
                     AND table_name = ? 
                     AND index_name = ?",
                    [$tableName, $indexName]
                );
                return $result[0]->count > 0;
            } catch (\Exception $e) {
                return false;
            }
        };

        // Add indexes to loan_schedules table
        if (!$indexExists('loan_schedules', 'idx_loan_schedules_status_paid_date')) {
            Schema::table('loan_schedules', function (Blueprint $table) {
                $table->index(['status', 'paid_date'], 'idx_loan_schedules_status_paid_date');
            });
        }

        if (!$indexExists('loan_schedules', 'idx_loan_schedules_loan_status_paid_date')) {
            Schema::table('loan_schedules', function (Blueprint $table) {
                $table->index(['loan_id', 'status', 'paid_date'], 'idx_loan_schedules_loan_status_paid_date');
            });
        }

        // Add indexes to loan_transactions table
        if (!$indexExists('loan_transactions', 'idx_loan_transactions_org_type_status_date')) {
            Schema::table('loan_transactions', function (Blueprint $table) {
                $table->index(['organization_id', 'transaction_type', 'status', 'transaction_date'], 'idx_loan_transactions_org_type_status_date');
            });
        }

        if (!$indexExists('loan_transactions', 'idx_loan_transactions_branch_type_status_date')) {
            Schema::table('loan_transactions', function (Blueprint $table) {
                $table->index(['branch_id', 'transaction_type', 'status', 'transaction_date'], 'idx_loan_transactions_branch_type_status_date');
            });
        }

        if (!$indexExists('loan_transactions', 'idx_loan_transactions_type_status_date')) {
            Schema::table('loan_transactions', function (Blueprint $table) {
                $table->index(['transaction_type', 'status', 'transaction_date'], 'idx_loan_transactions_type_status_date');
            });
        }

        // Add indexes to loans table
        if (!$indexExists('loans', 'idx_loans_org_branch_status')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->index(['organization_id', 'branch_id', 'status'], 'idx_loans_org_branch_status');
            });
        }

        if (!$indexExists('loans', 'idx_loans_org_status_amount')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->index(['organization_id', 'status', 'approved_amount'], 'idx_loans_org_status_amount');
            });
        }
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
