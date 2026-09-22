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
        if (Schema::hasTable('blog_post_likes')) {
            Schema::table('blog_post_likes', function (Blueprint $table) {
                if (! Schema::hasColumn('blog_post_likes', 'reaction_type')) {
                    $table->string('reaction_type', 20)->default('like')->after('user_id');
                }
            });
        }

        if (Schema::hasTable('blog_post_comments')) {
            Schema::table('blog_post_comments', function (Blueprint $table) {
                if (! Schema::hasColumn('blog_post_comments', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('blog_post_id')->constrained('blog_post_comments')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('blog_post_likes')) {
            Schema::table('blog_post_likes', function (Blueprint $table) {
                if (Schema::hasColumn('blog_post_likes', 'reaction_type')) {
                    $table->dropColumn('reaction_type');
                }
            });
        }

        if (Schema::hasTable('blog_post_comments')) {
            Schema::table('blog_post_comments', function (Blueprint $table) {
                if (Schema::hasColumn('blog_post_comments', 'parent_id')) {
                    $table->dropForeign(['parent_id']);
                    $table->dropColumn('parent_id');
                }
            });
        }
    }
};
