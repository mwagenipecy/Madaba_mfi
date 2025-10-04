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
        Schema::create('account_real_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('real_account_id');
            $table->string('mapping_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('real_account_id')->references('id')->on('real_accounts')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate mappings
            $table->unique(['account_id', 'real_account_id']);
            
            // Indexes for performance
            $table->index(['account_id', 'is_active']);
            $table->index(['real_account_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_real_account_mappings');
    }
};
