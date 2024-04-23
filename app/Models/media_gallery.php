<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class media_gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'object',
        'object_id',
        'object_type',
        'path',
        'file_name',
        'file_type',
        'alt',
        'sort_order',
        'status',
        'featured',
        'floor_plan',
        'watermark',
        'is_cropped',
        'tag',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    public function notable()
    {
        return $this->morphTo('object');
    }

    // MediaGallery model
    public function object()
    {
        return $this->morphTo();
    }

    public function listing()
    {
        return $this->morphTo('object');
    }

}
