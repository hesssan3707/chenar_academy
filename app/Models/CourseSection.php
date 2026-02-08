<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_product_id',
        'title',
        'sort_order',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class, 'course_section_id')->orderBy('sort_order')->orderBy('id');
    }
}
