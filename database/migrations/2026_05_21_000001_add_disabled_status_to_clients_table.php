<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `clients` MODIFY `status` ENUM('active', 'inactive', 'suspended', 'blacklisted', 'disabled') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::table('clients')->where('status', 'disabled')->update(['status' => 'inactive']);

        DB::statement("ALTER TABLE `clients` MODIFY `status` ENUM('active', 'inactive', 'suspended', 'blacklisted') NOT NULL DEFAULT 'active'");
    }
};
