<?php

use App\Models\LoanSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->decimal('paid_principal_amount', 15, 2)->default(0)->after('paid_amount');
            $table->decimal('paid_interest_amount', 15, 2)->default(0)->after('paid_principal_amount');
        });

        LoanSchedule::query()->each(function (LoanSchedule $schedule) {
            if ($schedule->status === 'paid') {
                $schedule->paid_principal_amount = $schedule->principal_amount;
                $schedule->paid_interest_amount = $schedule->interest_amount;
            } elseif ($schedule->paid_amount > 0 && $schedule->total_amount > 0) {
                $ratio = $schedule->paid_amount / $schedule->total_amount;
                $schedule->paid_principal_amount = round($schedule->principal_amount * $ratio, 2);
                $schedule->paid_interest_amount = round($schedule->paid_amount - $schedule->paid_principal_amount, 2);
            } else {
                $schedule->paid_principal_amount = 0;
                $schedule->paid_interest_amount = 0;
            }

            $schedule->outstanding_amount = max(0,
                ($schedule->principal_amount - $schedule->paid_principal_amount)
                + ($schedule->interest_amount - $schedule->paid_interest_amount)
            );
            $schedule->save();
        });
    }

    public function down(): void
    {
        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->dropColumn(['paid_principal_amount', 'paid_interest_amount']);
        });
    }
};
