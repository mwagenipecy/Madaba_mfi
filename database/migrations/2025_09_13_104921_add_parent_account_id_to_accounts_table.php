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
            $table->unsignedBigInteger('parent_account_id')->nullable()->after('account_type_id');
            $table->foreign('parent_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->index(['parent_account_id', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_account_id']);
            $table->dropIndex(['parent_account_id', 'organization_id']);
            $table->dropColumn('parent_account_id');
        });
    }
};
