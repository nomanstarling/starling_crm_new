<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class listings_amenities extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'listing_id',
        'amenity_id',
        'sort_order',
        'deleted_at',
    ];

    // In the ListingsAmenities model
    // public function amenity()
    // {
    //     return $this->belongsTo(amenities::class, 'amenity_id');
    // }

}
