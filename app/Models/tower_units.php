<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class tower_units extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'country_id',
        'city_id',
        'community_id',
        'sub_community_id',
        'tower_id',
        'name',
        'slug',
        'lang',
        'status',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    public function country()
    {
        return $this->belongsTo(countries::class, 'country_id');
    }

    public function city()
    {
        return $this->belongsTo(cities::class, 'city_id');
    }

    public function community()
    {
        return $this->belongsTo(communities::class, 'community_id');
    }

    public function sub_community()
    {
        return $this->belongsTo(sub_communities::class, 'sub_community_id');
    }

    public function tower()
    {
        return $this->belongsTo(towers::class, 'tower_id');
    }

    public function country_name()
    {
        return $this->belongsTo(Countries::class, 'country_id')->select('name');
    }

    public function city_name()
    {
        return $this->belongsTo(cities::class, 'city_id')->select('name');
    }

    public function community_name()
    {
        return $this->belongsTo(communities::class, 'community_id')->select('name');
    }

    public function sub_community_name()
    {
        return $this->belongsTo(sub_communities::class, 'sub_community_id')->select('name');
    }

    public function tower_name()
    {
        return $this->belongsTo(towers::class, 'tower_id')->select('name');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'subject_id')
            ->whereIn('description', ['created', 'updated'])
            ->orderBy('created_at', 'desc') // Order by created_at in descending order
            ->orderBy('id', 'desc') // Order by id in descending order as a tiebreaker
            ->with('causer'); // Load the 'causer' relation
    }

    protected $appends = ['sales_listing_count', 'rental_listing_count', 'archive_listing_count'];

    // public function getCreatedHumanAttribute()
    // {
    //     return $this->created_at->diffForHumans();
    // }

    // public function getUpdatedHumanAttribute()
    // {
    //     return $this->updated_at->diffForHumans();
    // }

    public function getSalesListings(){
        return $this->hasMany(Listings::class, 'tower_id')->where(function ($query) {
            $query->where('property_for', 'sale');
        });
    }

    public function getSalesListingCountAttribute()
    {
        return $this->getSalesListings()->count();
    }

    public function getRentalListings(){
        return $this->hasMany(Listings::class, 'tower_id')->where(function ($query) {
            $query->where('property_for', 'rent');
        });
    }

    public function getRentalListingCountAttribute()
    {
        return $this->getRentalListings()->count();
    }

    public function getArchivedListings()
    {
        return $this->hasMany(Listings::class, 'tower_id')->withTrashed()->where(function ($query) {
            $query->whereNotNull('deleted_at');
        });
    }

    public function getArchiveListingCountAttribute()
    {
        return $this->getArchivedListings()->count();
    }
}
