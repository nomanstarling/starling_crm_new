<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class listing_occupancies extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'listing_occupancies';

    protected $fillable = [
        'business_id',
        'name',
        'sort_order',
        'slug',
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

    protected $appends = ['sales_listing_count', 'rental_listing_count', 'archive_listing_count'];

    public function getSalesListings(){
        return $this->hasMany(Listings::class, 'occupancy_id')->where(function ($query) {
            $query->where('property_for', 'sale');
        });
    }

    public function getSalesListingCountAttribute()
    {
        return $this->getSalesListings()->count();
    }

    public function getRentalListings(){
        return $this->hasMany(Listings::class, 'occupancy_id')->where(function ($query) {
            $query->where('property_for', 'rent');
        });
    }

    public function getRentalListingCountAttribute()
    {
        return $this->getRentalListings()->count();
    }

    public function getArchivedListings()
    {
        return $this->hasMany(Listings::class, 'occupancy_id')->withTrashed()->where(function ($query) {
            $query->whereNotNull('deleted_at');
        });
    }

    public function getArchiveListingCountAttribute()
    {
        return $this->getArchivedListings()->count();
    }
}
