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
        Schema::table('real_accounts', function (Blueprint $table) {
            // Drop foreign key and index first
            $table->dropForeign(['account_id']);
            $table->dropIndex(['account_id', 'provider_type']);
            
            // Remove account_id column
            $table->dropColumn('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('real_accounts', function (Blueprint $table) {
            // Add back account_id column
            $table->unsignedBigInteger('account_id')->after('id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id', 'provider_type']);
        });
    }
};
