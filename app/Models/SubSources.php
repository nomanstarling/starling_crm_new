<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubSources extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'source_id',
        'name',
        'deleted_at',
    ];

    public function source()
    {
        return $this->belongsTo(Sources::class, 'source_id');
    }
}
