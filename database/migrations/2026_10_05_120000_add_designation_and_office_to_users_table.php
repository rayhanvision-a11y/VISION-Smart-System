<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 120)->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'pop_office_id')) {
                $table->unsignedBigInteger('pop_office_id')->nullable()->after('designation');
                $table->index('pop_office_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pop_office_id')) {
                $table->dropIndex(['pop_office_id']);
                $table->dropColumn('pop_office_id');
            }
            if (Schema::hasColumn('users', 'designation')) {
                $table->dropColumn('designation');
            }
        });
    }
};
