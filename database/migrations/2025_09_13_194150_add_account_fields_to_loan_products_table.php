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
        Schema::table('loan_products', function (Blueprint $table) {
            // Account fields for loan product
            $table->unsignedBigInteger('disbursement_account_id')->nullable()->after('sort_order'); // Account for loan disbursement
            $table->unsignedBigInteger('collection_account_id')->nullable()->after('disbursement_account_id'); // Account for collecting repayments
            $table->unsignedBigInteger('interest_revenue_account_id')->nullable()->after('collection_account_id'); // Account for interest revenue
            $table->unsignedBigInteger('principal_account_id')->nullable()->after('interest_revenue_account_id'); // Account for principal tracking
            
            // Foreign key constraints
            $table->foreign('disbursement_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('collection_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('interest_revenue_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('principal_account_id')->references('id')->on('accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropForeign(['disbursement_account_id']);
            $table->dropForeign(['collection_account_id']);
            $table->dropForeign(['interest_revenue_account_id']);
            $table->dropForeign(['principal_account_id']);
            
            $table->dropColumn([
                'disbursement_account_id',
                'collection_account_id', 
                'interest_revenue_account_id',
                'principal_account_id'
            ]);
        });
    }
};