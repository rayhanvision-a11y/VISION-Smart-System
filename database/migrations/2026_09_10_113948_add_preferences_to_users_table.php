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
            $table->string('theme_preference', 10)->default('system')->after('role');
            $table->string('locale', 5)->nullable()->after('theme_preference');
            $table->string('timezone', 60)->nullable()->after('locale');
            $table->boolean('notify_on_assign')->default(true)->after('timezone');
            $table->boolean('notify_on_resolve')->default(true)->after('notify_on_assign');
            $table->boolean('notify_on_message')->default(true)->after('notify_on_resolve');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'theme_preference', 'locale', 'timezone',
                'notify_on_assign', 'notify_on_resolve', 'notify_on_message',
            ]);
        });
    }
};
