<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_base_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->default('General');
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('youtube_id')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_base_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_base_articles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();

            $table->unique(['article_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_reads');
        Schema::dropIfExists('knowledge_base_articles');
    }
};
