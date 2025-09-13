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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Cash", "Bank", "Loan Portfolio", "Savings", "Investment"
            $table->string('code')->unique(); // e.g., "CASH", "BANK", "LOAN", "SAV", "INV"
            $table->text('description')->nullable();
            $table->enum('category', ['asset', 'liability', 'equity', 'income', 'expense']);
            $table->enum('balance_type', ['debit', 'credit']); // Normal balance type
            $table->boolean('is_main_account')->default(true); // Main account types vs sub-accounts
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
