<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'category',
        'excerpt',
        'content',
        'featured_image',
        'youtube_video_url',
        'views_count',
        'likes_count',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'views_count' => 'integer',
            'likes_count' => 'integer',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(BlogPostLike::class, 'blog_post_id');
    }

    public function comments()
    {
        return $this->hasMany(BlogPostComment::class, 'blog_post_id')->whereNull('parent_id')->with('user', 'replies')->latest();
    }

    public function allCommentsCount(): int
    {
        return $this->hasMany(BlogPostComment::class, 'blog_post_id')->count();
    }

    public function userReaction(?User $user): ?string
    {
        if (! $user) {
            return null;
        }
        $like = $this->likes()->where('user_id', $user->id)->first();

        return $like ? ($like->reaction_type ?? 'like') : null;
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Get YouTube Embed URL if valid
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (empty($this->youtube_video_url)) {
            return null;
        }

        $url = trim($this->youtube_video_url);

        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        return null;
    }

    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();

        return $count ? "{$slug}-".($count + 1) : $slug;
    }

    public function getYoutubeThumbnailUrlAttribute(): ?string
    {
        if (empty($this->youtube_video_url)) {
            return null;
        }
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', trim($this->youtube_video_url), $matches)) {
            return 'https://img.youtube.com/vi/'.$matches[1].'/maxresdefault.jpg';
        }

        return null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->featured_image) {
            if (str_starts_with($this->featured_image, 'http://') || str_starts_with($this->featured_image, 'https://')) {
                return $this->featured_image;
            }

            return asset('storage/'.$this->featured_image);
        }

        return $this->youtube_thumbnail_url;
    }
}
