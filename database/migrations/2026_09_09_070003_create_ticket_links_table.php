<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('linked_ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->string('link_type', 50)->default('relates_to');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['ticket_id', 'linked_ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_links');
    }
};
