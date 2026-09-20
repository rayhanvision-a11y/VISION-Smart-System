<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#4f46e5');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default categories
        $defaults = [
            ['name' => 'Line Fault',     'slug' => 'line_fault',     'description' => 'Issues related to physical fiber/copper line connection', 'color' => '#ef4444'],
            ['name' => 'Router Issue',   'slug' => 'router_issue',   'description' => 'Router configuration, Wi-Fi or hardware fault',            'color' => '#f59e0b'],
            ['name' => 'New Connection', 'slug' => 'new_connection', 'description' => 'Requests for new line installation or setup',              'color' => '#10b981'],
            ['name' => 'Billing',        'slug' => 'billing',        'description' => 'Payment, invoice or package upgrade queries',             'color' => '#6366f1'],
            ['name' => 'Other',          'slug' => 'other',          'description' => 'General queries and unclassified issues',                  'color' => '#64748b'],
        ];

        $now = now();
        foreach ($defaults as $cat) {
            DB::table('ticket_categories')->insert([
                'name'        => $cat['name'],
                'slug'        => $cat['slug'],
                'description' => $cat['description'],
                'color'       => $cat['color'],
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};
