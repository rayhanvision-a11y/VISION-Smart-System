<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('action');
            $table->foreignId('old_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_history');
    }
};
