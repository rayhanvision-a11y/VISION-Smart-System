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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('client_id', 100)->nullable()->after('ticket_key');
            $table->string('client_name', 255)->nullable()->after('client_id');
            $table->string('complaint_source', 60)->nullable()->default('Phone')->after('client_name');
            $table->string('onu_power', 100)->nullable()->after('area');
            $table->string('forwarded_to', 100)->nullable()->after('onu_power');
            $table->unsignedInteger('google_sheet_row_id')->nullable()->after('forwarded_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'client_id',
                'client_name',
                'complaint_source',
                'onu_power',
                'forwarded_to',
                'google_sheet_row_id',
            ]);
        });
    }
};
