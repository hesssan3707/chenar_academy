<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_user_id',
        'title',
        'slug',
        'excerpt',
        'status',
        'published_at',
        'cover_media_id',
        'meta',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(PostBlock::class, 'post_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_categories', 'post_id', 'category_id');
    }
}
