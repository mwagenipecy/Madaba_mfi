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
        Schema::create('account_recharges', function (Blueprint $table) {
            $table->id();
            $table->string('recharge_number')->unique(); // Unique recharge number
            $table->unsignedBigInteger('main_account_id'); // Main account being recharged
            $table->decimal('recharge_amount', 15, 2); // Total recharge amount
            $table->string('currency', 3)->default('USD'); // Currency
            $table->text('description'); // Recharge description
            $table->enum('status', ['pending', 'approved', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('requested_by'); // User who requested recharge
            $table->unsignedBigInteger('approved_by')->nullable(); // User who approved
            $table->timestamp('approved_at')->nullable(); // Approval timestamp
            $table->timestamp('completed_at')->nullable(); // Completion timestamp
            $table->text('rejection_reason')->nullable(); // Reason for rejection
            $table->json('distribution_plan')->nullable(); // How to distribute to branches
            $table->json('metadata')->nullable(); // Additional recharge data
            $table->timestamps();

            $table->foreign('main_account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'requested_by']);
            $table->index(['main_account_id', 'status']);
            $table->index('recharge_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_recharges');
    }
};
