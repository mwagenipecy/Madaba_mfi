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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Branch name
            $table->string('code')->unique(); // Branch code (e.g., BR001, BR002)
            $table->text('description')->nullable(); // Branch description
            $table->unsignedBigInteger('organization_id'); // Belongs to organization
            $table->string('address')->nullable(); // Branch address
            $table->string('city')->nullable(); // Branch city
            $table->string('state')->nullable(); // Branch state
            $table->string('country')->nullable(); // Branch country
            $table->string('postal_code')->nullable(); // Postal code
            $table->string('phone')->nullable(); // Branch phone
            $table->string('email')->nullable(); // Branch email
            $table->string('manager_name')->nullable(); // Branch manager name
            $table->string('manager_email')->nullable(); // Branch manager email
            $table->string('manager_phone')->nullable(); // Branch manager phone
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('established_date')->nullable(); // When branch was established
            $table->timestamps();
            $table->softDeletes(); // For disabling branches

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->index(['organization_id', 'status']);
            $table->index(['code', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
