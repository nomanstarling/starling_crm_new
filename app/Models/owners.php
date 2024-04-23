<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class owners extends Model implements Searchable
{
    use HasFactory, SoftDeletes;

    public function getSearchResult(): SearchResult
    {
        $url = route('owners.index', ['refno' => $this->refno]);

        return new SearchResult(
            $this,
            $this->name,
            $this->refno,
            $url
        );
    }

    protected $fillable = [
        'business_id',
        'refno',
        'old_refno',
        'title',
        'name',
        'phone',
        'whatsapp',
        'email',
        'nationality',
        'dob',
        'company',
        'designation',
        'religion',
        'lang',
        'website',
        'source_id',
        'sub_source_id',
        'status',
        'photo',
        'country_id',
        'city_id',
        'address',
        'created_by',
        'updated_by',
        'deleted_at',
    ];

    public function profileImage()
    {
        if ($this->photo) {
            return asset('public/storage/' . $this->photo);
        }
        return asset('assets/media/svg/avatars/blank-dark.svg');
    }

    public function source()
    {
        return $this->belongsTo(Sources::class, 'source_id');
    }

    public function sub_source()
    {
        return $this->belongsTo(SubSources::class, 'sub_source_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function listings()
    {
        return $this->hasMany(listings::class, 'owner_id');
    }
}
