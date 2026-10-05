<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('squad_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 120);
            $table->string('label_bn', 120)->nullable();
            $table->string('icon', 10)->default('👷');
            $table->string('color', 20)->default('#64748b');
            $table->string('badge', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed with the existing hardcoded defaults so no feature breaks.
        // Idempotent: upsert by `key` so re-running the migration (or restoring from backup) won't duplicate rows.
        $now = now();
        $seeds = [
            ['key' => 'complain', 'label' => 'Complain Team', 'label_bn' => 'অভিযোগ সমাধান টিম',
             'icon' => '🛠️', 'color' => '#d97706', 'sort_order' => 10, 'is_active' => true,
             'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50',
             'created_at' => $now, 'updated_at' => $now],
            ['key' => 'new_connection', 'label' => 'New Connection Team', 'label_bn' => 'নতুন সংযোগ টিম',
             'icon' => '🔌', 'color' => '#059669', 'sort_order' => 20, 'is_active' => true,
             'badge' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
             'created_at' => $now, 'updated_at' => $now],
            ['key' => 'line_transfer', 'label' => 'Transfer Team', 'label_bn' => 'লাইন ট্রান্সফার টিম',
             'icon' => '🔄', 'color' => '#2563eb', 'sort_order' => 30, 'is_active' => true,
             'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50',
             'created_at' => $now, 'updated_at' => $now],
        ];
        foreach ($seeds as $row) {
            if (! \DB::table('squad_categories')->where('key', $row['key'])->exists()) {
                \DB::table('squad_categories')->insert($row);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('squad_categories');
    }
};
