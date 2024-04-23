<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaigns extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'campaigns';

    protected $fillable = [
        'business_id',
        'name',
        'target_name',

        'target_name',
        'community_id',
        'sub_community_id',
        'tower_id',
        'source_id',
        'assignment_pointer',
        'match_count',
        'auto_assign',
        'auto_assign_after',
        'agents',

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

    public function source()
    {
        return $this->belongsTo(Sources::class, 'source_id');
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'campaign_agents', 'campaign_id', 'agent_id');
    }
}
