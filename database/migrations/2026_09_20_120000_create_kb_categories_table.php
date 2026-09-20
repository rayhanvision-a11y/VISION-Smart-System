<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default categories
        $defaults = [
            'নেটওয়ার্ক টিউটোরিয়াল',
            'বিলিং গাইড',
            'সফ্টওয়্যার সেটআপ',
            'সার্ভার ব্যবস্থাপনা',
            'সাধারণ প্রশ্নাবলী',
            'Billing',
            'App Server',
            'Smart Form',
            'Tutorial',
            'General',
        ];

        foreach ($defaults as $name) {
            $slug = Str::slug($name);
            if (!$slug) {
                $slug = Str::slug(str_replace(' ', '-', $name));
            }
            if (!$slug) {
                $slug = 'cat-' . md5($name);
            }
            DB::table('kb_categories')->insertOrIgnore([
                'name'       => $name,
                'slug'       => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kb_categories');
    }
};
