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
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('returned_by')->nullable()->after('rejected_by');
            $table->timestamp('returned_at')->nullable()->after('returned_by');

            $table->foreign('returned_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['returned_by', 'returned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['returned_by', 'returned_at']);
            $table->dropForeign(['returned_by']);
            $table->dropColumn(['returned_by', 'returned_at']);
        });
    }
};


