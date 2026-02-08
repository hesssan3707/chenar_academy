<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'part_type',
        'title',
        'sort_order',
        'media_id',
        'content',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
