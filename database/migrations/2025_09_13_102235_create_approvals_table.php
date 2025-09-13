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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approval_number')->unique(); // Unique approval number
            $table->enum('type', ['fund_transfer', 'account_recharge', 'other']); // Type of approval
            $table->string('reference_type'); // Model type (FundTransfer, AccountRecharge, etc.)
            $table->unsignedBigInteger('reference_id'); // ID of the reference record
            $table->unsignedBigInteger('requested_by'); // User who requested approval
            $table->unsignedBigInteger('approver_id'); // User who should approve
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('description'); // Approval description
            $table->text('approval_notes')->nullable(); // Notes from approver
            $table->timestamp('approved_at')->nullable(); // Approval timestamp
            $table->json('metadata')->nullable(); // Additional approval data
            $table->timestamps();

            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['type', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['approver_id', 'status']);
            $table->index('approval_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
