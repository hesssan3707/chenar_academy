<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'title',
        'sort_order',
        'lesson_type',
        'media_id',
        'video_url',
        'content',
        'is_preview',
        'duration_seconds',
        'meta',
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'meta' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
