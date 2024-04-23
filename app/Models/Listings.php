<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Listings extends Model implements Searchable
{
    use HasFactory, SoftDeletes;

    public function getSearchResult(): SearchResult
    {
        $url = route('listings.index', ['id' => $this->refno]);

        return new SearchResult(
            $this,
            $this->refno,
            $url
        );
    }

    protected $fillable = [
        'business_id',
        'refno',
        'external_refno',
        'title',
        'old_refno',
        'desc',
        'brochure_desc',
        'property_for',
        'property_type',
        'category_id',
        'country_id',
        'city_id',
        'community_id',
        'sub_community_id',
        'tower_id',
        'unit_id',
        'plot_no',
        'plot_area',
        'unit_type',
        'unit_no',
        'floor_no',
        'bua',
        'location',
        'lead_gen',
        'international',
        'poa',
        'project_status_id',
        'completion_date',
        'parking',
        'beds',
        'baths',
        'furnished',
        'latitude',
        'longitude',
        'status_id',
        'status_reason',
        'hot',
        'exclusive',
        'price',
        'frequency',
        'occupancy_id',
        'cheques',
        'sort_order',
        'currency',
        'created_by',
        'agent_id',
        'marketing_agent_id',
        'owner_id',
        'rera_permit',
        'expiry_date',
        'next_availability_date',
        'available_date',
        'developer_id',
        'updated_by',
        'view',
        'video_link',
        'live_tour_link',
        'meta_title',
        'meta_desc',
        'lang',
        'is_sold',
        'is_let',
        'import_source',
        'import_file',
        'import_old_data',
        'import_type',
        'deleted_at',
        'published_at',
        'created_at',
    ];

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function city()
    {
        return $this->belongsTo(cities::class, 'city_id');
    }

    public function owner()
    {
        return $this->belongsTo(owners::class, 'owner_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'subject_id')
            ->whereIn('description', ['created', 'updated'])
            ->orderBy('created_at', 'desc') // Order by created_at in descending order
            ->orderBy('id', 'desc') // Order by id in descending order as a tiebreaker
            ->with('causer'); // Load the 'causer' relation
    }

    public function notes()
    {
        return $this->morphMany(Notes::class, 'object')->latest('updated_at');
    }

    public function documents()
    {
        return $this->morphMany(media_gallery::class, 'object')->where('object', 'document');
    }

    public function images()
    {
        return $this->morphMany(media_gallery::class, 'object')->where('object', 'image')->orderBy('sort_order', 'asc');
    }

    // public function featuredImage()
    // {
    //     return $this->morphOne(media_gallery::class, 'object')
    //         ->where('object', 'image')
    //         ->orderBy('sort_order', 'asc')
    //         ->take(1);
    // }

    public function featuredImage()
    {
        $placeholderPath = asset('assets/media/property_placeholder.webp');

        $featuredImage = $this->morphOne(media_gallery::class, 'object')
                    ->where('object', 'image')
                    ->orderBy('sort_order', 'asc')
                    ->take(1)
                    ->first();

        return $featuredImage ? asset('public/storage/'.$featuredImage->path) : $placeholderPath;
    }




    // public function media()
    // {
    //     return $this->hasMany(media_gallery::class, 'listing_id');
    // }

    public function mediaGalleries()
    {
        return $this->morphMany(media_gallery::class, 'object');
    }

    public function status()
    {
        return $this->belongsTo(Statuses::class, 'status_id');
    }

    public function prop_type()
    {
        return $this->belongsTo(property_type::class, 'property_type');
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

    public function marketing_agent()
    {
        return $this->belongsTo(User::class, 'marketing_agent_id');
    }

    public function listing_agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function project_status()
    {
        return $this->belongsTo(project_status::class, 'project_status_id');
    }

    public function occupancy()
    {
        return $this->belongsTo(listing_occupancies::class, 'occupancy_id');
    }

    public function developer()
    {
        return $this->belongsTo(developers::class, 'developer_id');
    }

    public function category()
    {
        return $this->belongsTo(property_category::class, 'category_id');
    }
    
    public function amenities()
    {
        return $this->belongsToMany(amenities::class, 'listings_amenities', 'listing_id', 'amenity_id')->with('amenity');
    }

    public function portals()
    {
        return $this->belongsToMany(listing_portals::class, 'listings_portal_ids', 'listing_id', 'portal_id');
    }

    public function location()
    {
        $location = '';

        if ($this->tower && $this->tower != null) {
            $location .= $this->tower->name . ', ';
        }

        if ($this->sub_community && $this->sub_community != null) {
            $location .= $this->sub_community->name . ', ';
        }

        if ($this->community && $this->community != null) {
            $location .= $this->community->name;
        }

        return $location;
    }

    public function location_details()
    {
        $location = '';

        if ($this->tower && $this->tower != null) {
            $location .= $this->tower->name . ', ';
        }

        if ($this->sub_community && $this->sub_community != null) {
            $location .= $this->sub_community->name . ', ';
        }

        if ($this->community && $this->community != null) {
            $location .= $this->community->name;
        }

        return $location;
    }


    
}
