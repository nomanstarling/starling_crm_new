<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class amenities extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'amenities';

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'type',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class, 'subject_id')
            ->whereIn('description', ['created', 'updated'])
            ->orderBy('created_at', 'desc') // Order by created_at in descending order
            ->orderBy('id', 'desc') // Order by id in descending order as a tiebreaker
            ->with('causer'); // Load the 'causer' relation
    }
    
    // public function listings()
    // {
    //     return $this->belongsToMany(Listings::class, 'listings_amenities', 'amenity_id', 'listing_id');
    // }

    public function listings()
    {
        return $this->belongsToMany(Listings::class, 'listings_amenities', 'amenity_id', 'listing_id');
    }

    public function amenity()
    {
        return $this->belongsTo(amenities::class, 'amenity_id');
    }

}
