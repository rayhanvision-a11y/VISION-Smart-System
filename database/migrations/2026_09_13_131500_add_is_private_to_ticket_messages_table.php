<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_messages', 'is_private')) {
                $table->boolean('is_private')->default(false)->after('image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_messages', 'is_private')) {
                $table->dropColumn('is_private');
            }
        });
    }
};
