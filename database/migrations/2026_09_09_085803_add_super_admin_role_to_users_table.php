<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return; // sqlite/others store role as a plain string column, no ENUM to alter
        }
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','noc','reseller') DEFAULT 'reseller'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','noc','reseller') DEFAULT 'reseller'");
    }
};
