<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `tickets` MODIFY `category` VARCHAR(100) NOT NULL DEFAULT 'other'");
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('category', 100)->default('other')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `tickets` MODIFY `category` ENUM('line_fault', 'router_issue', 'new_connection', 'billing', 'other') NOT NULL DEFAULT 'other'");
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('category')->default('other')->change();
            });
        }
    }
};
