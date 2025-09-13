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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('client_number')->unique(); // Auto-generated client ID
            $table->enum('client_type', ['individual', 'group', 'business'])->default('individual');
            $table->unsignedBigInteger('organization_id'); // Belongs to organization
            $table->unsignedBigInteger('branch_id')->nullable(); // Optional branch assignment
            
            // Individual Client Information
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('national_id')->nullable(); // National ID number
            $table->string('passport_number')->nullable(); // Alternative ID
            
            // Group/Business Information
            $table->string('business_name')->nullable(); // For business/group clients
            $table->string('business_registration_number')->nullable();
            $table->enum('business_type', ['sole_proprietorship', 'partnership', 'corporation', 'cooperative', 'ngo', 'other'])->nullable();
            
            // Contact Information
            $table->string('phone_number')->nullable();
            $table->string('secondary_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('physical_address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('country')->default('Tanzania');
            $table->string('postal_code')->nullable();
            
            // KYC Information
            $table->enum('kyc_status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $table->date('kyc_verification_date')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable(); // User who verified
            $table->text('kyc_notes')->nullable();
            
            // Financial Information
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->string('income_source')->nullable(); // Employment, business, etc.
            $table->string('employer_name')->nullable();
            $table->text('employment_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Document Storage
            $table->json('documents')->nullable(); // Store document paths/info
            $table->json('kyc_documents')->nullable(); // KYC specific documents
            
            // Additional Information
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->integer('dependents')->default(0);
            $table->text('occupation')->nullable();
            $table->text('business_description')->nullable(); // For business clients
            $table->integer('years_in_business')->nullable(); // For business clients
            $table->decimal('annual_turnover', 15, 2)->nullable(); // For business clients
            
            // Status and Metadata
            $table->enum('status', ['active', 'inactive', 'suspended', 'blacklisted'])->default('active');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional flexible data
            $table->timestamps();
            $table->softDeletes(); // For soft deletion
            
            // Foreign Keys and Indexes
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['organization_id', 'client_type']);
            $table->index(['branch_id', 'status']);
            $table->index(['kyc_status', 'organization_id']);
            $table->index(['national_id', 'organization_id']);
            $table->index(['phone_number', 'organization_id']);
            $table->index(['email', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
