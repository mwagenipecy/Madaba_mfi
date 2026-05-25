<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference_number')->unique();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->enum('type', ['land', 'vehicle', 'equipment', 'livestock', 'property', 'savings', 'other'])->default('other');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('estimated_value', 15, 2);
            $table->decimal('lending_ratio', 5, 2)->default(70.00);
            $table->string('location')->nullable();
            $table->string('identifier')->nullable();
            $table->string('document_path')->nullable();
            $table->enum('status', ['available', 'pledged', 'released'])->default('available');
            $table->timestamp('pledged_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('loan_id')->references('id')->on('loans')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['organization_id', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaterals');
    }
};
