<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Daily opening/closing of cash and mobile wallet; next day opening uses previous closing.
     * Variance (deficit/excess) is computed when opening differs from previous day's closing.
     */
    public function up(): void
    {
        Schema::create('daily_till_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('record_date')->comment('Business day');
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('opening_mobile_wallet', 15, 2)->default(0);
            $table->decimal('closing_cash', 15, 2)->nullable();
            $table->decimal('closing_mobile_wallet', 15, 2)->nullable();
            $table->unsignedBigInteger('opening_recorded_by');
            $table->timestamp('opening_recorded_at')->nullable();
            $table->unsignedBigInteger('closing_recorded_by')->nullable();
            $table->timestamp('closing_recorded_at')->nullable();
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('opening_recorded_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('closing_recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['organization_id', 'branch_id', 'record_date']);
            $table->index(['record_date', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_till_records');
    }
};
