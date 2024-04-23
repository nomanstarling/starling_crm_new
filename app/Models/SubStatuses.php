<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubStatuses extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'badge',
        'status_id',
        'deleted_at',
    ];

    public function crmStatus()
    {
        return $this->belongsTo(Statuses::class, 'status_id');
    }
}
