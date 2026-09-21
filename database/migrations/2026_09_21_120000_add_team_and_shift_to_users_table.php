<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('team')->nullable()->after('role'); // e.g., 'IT Team', 'NOC team', 'Call center', 'Supervisor Team'
            $table->string('current_shift')->default('day_shift')->after('team'); // 'day_shift', 'night_shift', 'day_off'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['team', 'current_shift']);
        });
    }
};
