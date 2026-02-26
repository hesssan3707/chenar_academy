<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booklet extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'product_id',
        'file_media_id',
        'sample_pdf_media_id',
        'preview_image_media_ids',
        'meta',
    ];

    protected $casts = [
        'preview_image_media_ids' => 'array',
        'meta' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function fileMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'file_media_id');
    }

    public function samplePdfMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'sample_pdf_media_id');
    }
}
