<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'office_id')) {
                $table->string('office_id', 60)->nullable()->after('designation');
                $table->index('office_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'office_id')) {
                $table->dropIndex(['office_id']);
                $table->dropColumn('office_id');
            }
        });
    }
};
