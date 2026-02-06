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
        // Check if deleted_at column already exists
        $columnExists = false;
        try {
            $result = DB::select(
                "SELECT COUNT(*) as count FROM information_schema.columns 
                 WHERE table_schema = DATABASE() 
                 AND table_name = ? 
                 AND column_name = 'deleted_at'",
                ['expense_requests']
            );
            $columnExists = $result[0]->count > 0;
        } catch (\Exception $e) {
            // If check fails, assume column doesn't exist
            $columnExists = false;
        }

        if (!$columnExists) {
            Schema::table('expense_requests', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};