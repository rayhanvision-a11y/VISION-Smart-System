<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KbCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name);
        if (!$base) {
            $base = Str::slug(str_replace(' ', '-', $name));
        }
        if (!$base) {
            $base = 'cat-' . substr(md5($name), 0, 8);
        }

        $slug = $base;
        $count = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $count++;
        }
        return $slug;
    }
}
