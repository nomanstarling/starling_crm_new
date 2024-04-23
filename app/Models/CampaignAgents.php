<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignAgents extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'agent_id',
        'campaign_id',
        'created_by',
        'updated_by',
        'deleted_at',
    ];
}
