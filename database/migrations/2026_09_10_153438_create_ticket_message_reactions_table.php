<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_message_id')->constrained('ticket_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('emoji', 10);
            $table->timestamps();
            $table->unique(['ticket_message_id', 'user_id', 'emoji']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_message_reactions');
    }
};
