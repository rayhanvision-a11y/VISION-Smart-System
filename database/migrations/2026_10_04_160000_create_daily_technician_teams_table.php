<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_technician_teams', function (Blueprint $table) {
            $table->id();
            $table->date('duty_date')->index();
            $table->string('category', 60)->default('complain'); // complain, new_connection, line_transfer, etc.
            $table->string('team_name', 100)->nullable();
            $table->foreignId('leader_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('member_1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('member_2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('area', 120)->nullable();
            $table->string('vehicle_no', 60)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['duty_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_technician_teams');
    }
};
