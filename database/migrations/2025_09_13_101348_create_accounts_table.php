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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Account name
            $table->string('account_number')->unique(); // Unique account number
            $table->unsignedBigInteger('account_type_id'); // Foreign key to account_types
            $table->unsignedBigInteger('organization_id'); // Belongs to organization
            $table->unsignedBigInteger('branch_id')->nullable(); // Branch-specific account (null for main accounts)
            $table->decimal('balance', 15, 2)->default(0.00); // Current balance
            $table->decimal('opening_balance', 15, 2)->default(0.00); // Opening balance
            $table->string('currency', 3)->default('USD'); // Currency code
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'closed'])->default('active');
            $table->timestamp('opening_date')->nullable();
            $table->timestamp('last_transaction_date')->nullable();
            $table->json('metadata')->nullable(); // Additional account-specific data
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_type_id')->references('id')->on('account_types')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            
            $table->index(['organization_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['account_type_id', 'organization_id']);
            $table->index('account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
