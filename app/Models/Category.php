<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'parent_id',
        'title',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];
}
