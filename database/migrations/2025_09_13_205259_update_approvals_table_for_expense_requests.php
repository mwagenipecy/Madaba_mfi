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
        Schema::table('approvals', function (Blueprint $table) {
            // Add organization and branch fields
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('organization_id');
            
            // Add foreign key constraints
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            
            // Update the type enum to include expense_request
            $table->dropColumn('type');
        });
        
        // Add the updated type column
        Schema::table('approvals', function (Blueprint $table) {
            $table->enum('type', ['fund_transfer', 'account_recharge', 'expense_request', 'other'])->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['organization_id', 'branch_id']);
            
            // Revert type column
            $table->dropColumn('type');
        });
        
        Schema::table('approvals', function (Blueprint $table) {
            $table->enum('type', ['fund_transfer', 'account_recharge', 'other'])->after('branch_id');
        });
    }
};