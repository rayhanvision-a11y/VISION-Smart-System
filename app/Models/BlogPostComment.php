<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPostComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_post_id',
        'user_id',
        'parent_id',
        'comment',
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(BlogPostComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(BlogPostComment::class, 'parent_id')->with('user', 'replies')->oldest();
    }
}
