<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class countries extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'business_id',
        'slug',
        'code',
        'lang',
        'currency',
        'status',
        'created_by',
        'updated_by',
        'deleted_at',
    ];
}
