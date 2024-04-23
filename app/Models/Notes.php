<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notes extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'object',
        'object_id',
        'object_type',
        'note',
        'type',
        'event_date',
        'status',
        'created_by',
        'updated_by',
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    public function notable()
    {
        return $this->morphTo('object');
    }

    public function lead()
    {
        return $this->hasOne(Leads::class, 'id', 'object_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected $appends = ['created_human', 'updated_human'];

    public function getCreatedHumanAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getUpdatedHumanAttribute()
    {
        return $this->updated_at->diffForHumans();
    }
}
