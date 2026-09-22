<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','noc','supervisor','senior_supervisor','call_center','reseller') DEFAULT 'reseller'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','noc','call_center','reseller') DEFAULT 'reseller'");
        }
    }
};
