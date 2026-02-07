<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the status enum to include 'assessed' between 'under_review' and 'approved'
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('pending', 'under_review', 'assessed', 'approved', 'rejected', 'disbursed', 'active', 'overdue', 'completed', 'written_off', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('pending', 'under_review', 'approved', 'rejected', 'disbursed', 'active', 'overdue', 'completed', 'written_off', 'cancelled') DEFAULT 'pending'");
    }
};
