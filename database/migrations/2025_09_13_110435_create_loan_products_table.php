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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Loan product name
            $table->string('code')->unique(); // Unique product code
            $table->text('description')->nullable(); // Product description
            $table->unsignedBigInteger('organization_id'); // Belongs to organization
            $table->decimal('min_amount', 15, 2); // Minimum loan amount
            $table->decimal('max_amount', 15, 2); // Maximum loan amount
            $table->decimal('interest_rate', 5, 2); // Interest rate percentage
            $table->enum('interest_type', ['fixed', 'variable'])->default('fixed'); // Interest type
            $table->integer('min_tenure_months'); // Minimum tenure in months
            $table->integer('max_tenure_months'); // Maximum tenure in months
            $table->decimal('processing_fee', 15, 2)->default(0.00); // Processing fee
            $table->decimal('late_fee', 15, 2)->default(0.00); // Late payment fee
            $table->enum('repayment_frequency', ['daily', 'weekly', 'monthly', 'quarterly'])->default('monthly');
            $table->integer('grace_period_days')->default(0); // Grace period in days
            $table->json('eligibility_criteria')->nullable(); // JSON criteria
            $table->json('required_documents')->nullable(); // Required documents
            $table->boolean('requires_collateral')->default(false); // Requires collateral
            $table->decimal('collateral_ratio', 5, 2)->nullable(); // Collateral to loan ratio
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('is_featured')->default(false); // Featured product
            $table->integer('sort_order')->default(0); // Display order
            $table->json('metadata')->nullable(); // Additional product data
            $table->timestamps();
            $table->softDeletes(); // For disabling products

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->index(['organization_id', 'status']);
            $table->index(['code', 'organization_id']);
            $table->index(['status', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
