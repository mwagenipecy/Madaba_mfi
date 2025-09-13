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
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('organization_id');
            
            // Add foreign key constraints
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            
            // Add indexes for better performance
            $table->index(['organization_id', 'transaction_date']);
            $table->index(['organization_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['organization_id', 'transaction_date']);
            $table->dropIndex(['organization_id', 'account_id']);
            $table->dropColumn(['organization_id', 'branch_id']);
        });
    }
};
