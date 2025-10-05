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
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('account_classification', ['internal', 'external'])->default('internal')->after('status');
            $table->enum('external_account_type', ['receiver', 'giver'])->nullable()->after('account_classification');
            
            // Add indexes for better performance
            $table->index(['account_classification', 'organization_id']);
            $table->index(['external_account_type', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['account_classification', 'organization_id']);
            $table->dropIndex(['external_account_type', 'organization_id']);
            $table->dropColumn(['account_classification', 'external_account_type']);
        });
    }
};
