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
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'real_account_id')) {
                $table->unsignedBigInteger('real_account_id')->nullable()->after('branch_id');
                $table->foreign('real_account_id')->references('id')->on('real_accounts')->onDelete('set null');
                $table->index(['real_account_id']);
            }
            
            if (!Schema::hasColumn('accounts', 'mapping_description')) {
                $table->text('mapping_description')->nullable()->after('real_account_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'real_account_id')) {
                $table->dropForeign(['real_account_id']);
                $table->dropIndex(['real_account_id']);
                $table->dropColumn('real_account_id');
            }
            
            if (Schema::hasColumn('accounts', 'mapping_description')) {
                $table->dropColumn('mapping_description');
            }
        });
    }
};