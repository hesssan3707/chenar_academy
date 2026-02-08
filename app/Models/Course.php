<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'product_id',
        'body',
        'level',
        'total_duration_seconds',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class, 'course_product_id', 'product_id')->orderBy('sort_order')->orderBy('id');
    }
}
