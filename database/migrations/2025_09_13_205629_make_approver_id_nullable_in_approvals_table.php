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
            // Drop the existing foreign key constraint
            $table->dropForeign(['approver_id']);
            
            // Make approver_id nullable
            $table->unsignedBigInteger('approver_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['approver_id']);
            
            // Make approver_id not nullable again
            $table->unsignedBigInteger('approver_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};