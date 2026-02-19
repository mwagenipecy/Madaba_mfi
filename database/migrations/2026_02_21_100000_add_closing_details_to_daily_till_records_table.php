<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_till_records', function (Blueprint $table) {
            $table->decimal('amount_collected', 15, 2)->nullable()->after('closing_mobile_wallet')->comment('Total collected during the day');
            $table->decimal('loans_given', 15, 2)->nullable()->after('amount_collected')->comment('Total loans given this day');
            $table->json('expenses')->nullable()->after('loans_given')->comment('[{amount, description}, ...]');
        });
    }

    public function down(): void
    {
        Schema::table('daily_till_records', function (Blueprint $table) {
            $table->dropColumn(['amount_collected', 'loans_given', 'expenses']);
        });
    }
};
