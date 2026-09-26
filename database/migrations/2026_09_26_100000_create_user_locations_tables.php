<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedSmallInteger('accuracy_meters')->nullable();
            $table->unsignedTinyInteger('battery_level')->nullable();
            $table->decimal('speed_mps', 5, 2)->nullable();
            $table->boolean('is_sharing')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'updated_at'], 'idx_ul_user_updated');
        });

        Schema::create('user_location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamp('recorded_at')->useCurrent();
            $table->index(['user_id', 'recorded_at'], 'idx_ulh_user_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_location_history');
        Schema::dropIfExists('user_locations');
    }
};
