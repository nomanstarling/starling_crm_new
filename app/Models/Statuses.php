<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Statuses extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'type',
        'lead_type',
        'badge',
        'deleted_at',
    ];

    public function subStatuses()
    {
        return $this->hasMany(SubStatuses::class, 'status_id');
    }
}
