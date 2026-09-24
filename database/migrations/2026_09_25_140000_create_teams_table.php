<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('is_active', 'idx_teams_active');
        });

        // Seed with existing hardcoded teams so old user records keep working
        foreach (['IT Team', 'NOC team', 'Call center', 'Supervisor Team'] as $t) {
            \DB::table('teams')->insertOrIgnore([
                'name' => $t,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
