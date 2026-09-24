<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add area column to tickets
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'area')) {
                $table->string('area', 120)->nullable()->after('pop_office_id');
                $table->index('area', 'idx_tickets_area');
            }
        });

        // 2. Extend users.role enum to allow 'technician' (MySQL only; SQLite in tests uses string).
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
                'super_admin','admin','noc','reseller',
                'call_center','supervisor','senior_supervisor','technician'
            ) NOT NULL DEFAULT 'reseller'");
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'area')) {
                $table->dropIndex('idx_tickets_area');
                $table->dropColumn('area');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
                'super_admin','admin','noc','reseller',
                'call_center','supervisor','senior_supervisor'
            ) NOT NULL DEFAULT 'reseller'");
        }
    }
};
