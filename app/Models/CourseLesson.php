<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_section_id',
        'title',
        'sort_order',
        'lesson_type',
        'media_id',
        'content',
        'is_preview',
        'duration_seconds',
        'meta',
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'meta' => 'array',
    ];
}
