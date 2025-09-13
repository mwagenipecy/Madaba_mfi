<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('registration_number')->unique()->nullable();
            $table->string('license_number')->unique()->nullable();
            $table->enum('type', ['microfinance_bank', 'cooperative_society', 'ngo', 'credit_union', 'other']);
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('Nigeria');
            $table->string('postal_code')->nullable();
            $table->decimal('authorized_capital', 15, 2)->nullable();
            $table->date('incorporation_date')->nullable();
            $table->json('regulatory_info')->nullable(); // Store regulatory approvals
            $table->string('logo_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_approval'])->default('pending_approval');
            $table->text('description')->nullable();
            $table->json('settings')->nullable(); // Store organization-specific settings
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'type']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
