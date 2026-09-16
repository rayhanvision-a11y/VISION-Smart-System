<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->foreignId('reply_to_id')->nullable()->after('sender_id')
                ->constrained('ticket_messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reply_to_id');
        });
    }
};
