<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return; // sqlite/others store status as a plain string column, no ENUM to alter
        }
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('in_progress','pending','waiting_for_customer_feedback','resolved') NOT NULL DEFAULT 'in_progress'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open','in_progress','pending','waiting_for_customer_feedback','resolved','closed','reopened') NOT NULL DEFAULT 'open'");
    }
};
