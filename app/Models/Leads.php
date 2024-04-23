<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Leads extends Model implements Searchable
{
    use HasFactory, SoftDeletes;

    public function getSearchResult(): SearchResult
    {
        $url = route('leads.index', ['id' => $this->refno]);

        return new SearchResult(
            $this,
            $this->refno,
            $url
        );
    }

    protected $fillable = [
        'business_id',
        'refno',
        'old_refno',
        'listing_refno',
        'listing_id',
        'contact_id',
        'finance',
        'priority',
        'source_id',
        'sub_source_id',
        'lead_type',
        'status_id',
        'sub_status_id',
        'enquiry_date',
        'created_by',
        'emailopt',
        'hotlead',
        'assigned_date',
        'accepted_date',
        'reassigned_from',
        'agent_id',
        'seller_agent',
        'buyer_agent',
        'seller_contact',
        'buyer_contact',
        'seller_commission_percent',
        'buyer_commission_percent',
        'seller_commission_amount',
        'buyer_commission_amount',
        'deal_price',
        'assign_status',
        'accept_status',
        'comment',
        'import_source',
        'ipaddress',
        'referal_url',
        'pfmessage',
        'external_agent',
        'move_in_date',
        'last_call',
        'verified_by',
        'lead_stage',
        'leads_exported',
        'lmstocrm',
        'parent_lead',
        'last_match',
        'email_send',
        'campaign_id',
        'processlog',
        'hash',
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

    public function contact()
    {
        return $this->belongsTo(Contacts::class, 'contact_id');
    }

    public function property()
    {
        return $this->belongsTo(listings::class, 'listing_id');
    }

    public function property_details()
    {
        return $this->belongsTo(LeadDetails::class, 'lead_id');
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

    public function status()
    {
        return $this->belongsTo(Statuses::class, 'status_id');
    }

    public function sub_status()
    {
        return $this->belongsTo(SubStatuses::class, 'sub_status_id');
    }

    public function lead_agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function source()
    {
        return $this->belongsTo(Sources::class, 'source_id');
    }

    public function sub_source()
    {
        return $this->belongsTo(SubSources::class, 'sub_source_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaigns::class, 'campaign_id');
    }

    public function lead_details()
    {
        return $this->hasOne(LeadDetails::class, 'lead_id');
    }
}
