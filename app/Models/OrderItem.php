<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_type',
        'product_title',
        'quantity',
        'unit_price',
        'total_price',
        'currency',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
