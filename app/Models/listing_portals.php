<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class listing_portals extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'is_paid',
        'status',
        'logo',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    public function logoImage()
    {
        if ($this->logo) {
            return asset('public/storage/' . $this->logo);
        }
        return asset('assets/media/svg/avatars/blank-dark.svg');
    }

    public function listings()
    {
        return $this->belongsToMany(Listings::class, 'listings_portal_ids', 'portal_id', 'listing_id');
    }

    
}
