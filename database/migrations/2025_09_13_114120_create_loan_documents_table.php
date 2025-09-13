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
        Schema::create('loan_documents', function (Blueprint $table) {
            $table->id();
            
            // Loan Reference
            $table->unsignedBigInteger('loan_id');
            
            // Document Information
            $table->string('document_name'); // Name of the document
            $table->string('document_type'); // Type of document (application, contract, collateral, etc.)
            $table->string('file_path'); // Path to the uploaded file
            $table->string('file_name'); // Original file name
            $table->string('file_extension'); // File extension
            $table->integer('file_size'); // File size in bytes
            $table->string('mime_type'); // MIME type of the file
            
            // Document Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->boolean('is_required')->default(false); // Whether this document is required
            $table->date('expiry_date')->nullable(); // Document expiry date if applicable
            
            // Additional Information
            $table->text('description')->nullable(); // Document description
            $table->text('notes')->nullable(); // Additional notes
            $table->unsignedBigInteger('uploaded_by'); // User who uploaded the document
            $table->unsignedBigInteger('verified_by')->nullable(); // User who verified the document
            $table->date('verified_date')->nullable(); // Date when document was verified
            
            $table->timestamps();
            
            // Foreign Keys and Indexes
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['loan_id', 'document_type']);
            $table->index(['status', 'is_required']);
            $table->index(['expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_documents');
    }
};
