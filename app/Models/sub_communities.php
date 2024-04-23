<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class sub_communities extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'country_id',
        'city_id',
        'community_id',
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

    public function activities()
    {
        return $this->hasMany(Activity::class, 'subject_id')
            ->whereIn('description', ['created', 'updated'])
            ->orderBy('created_at', 'desc') // Order by created_at in descending order
            ->orderBy('id', 'desc') // Order by id in descending order as a tiebreaker
            ->with('causer'); // Load the 'causer' relation
    }

    protected $appends = ['created_human', 'updated_human', 'sales_listing_count', 'rental_listing_count', 'archive_listing_count'];

    public function getCreatedHumanAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getUpdatedHumanAttribute()
    {
        return $this->updated_at->diffForHumans();
    }

    public function getSalesListings(){
        return $this->hasMany(Listings::class, 'sub_community_id')->where(function ($query) {
            $query->where('property_for', 'sale');
        });
    }

    public function getSalesListingCountAttribute()
    {
        return $this->getSalesListings()->count();
    }

    public function getRentalListings(){
        return $this->hasMany(Listings::class, 'sub_community_id')->where(function ($query) {
            $query->where('property_for', 'rent');
        });
    }

    public function getRentalListingCountAttribute()
    {
        return $this->getRentalListings()->count();
    }

    public function getArchivedListings()
    {
        return $this->hasMany(Listings::class, 'sub_community_id')->withTrashed()->where(function ($query) {
            $query->whereNotNull('deleted_at');
        });
    }

    public function getArchiveListingCountAttribute()
    {
        return $this->getArchivedListings()->count();
    }

    public function towers()
    {
        return $this->hasMany(towers::class, 'sub_community_id');
    }
}
