<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class cities extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'country_id',
        'business_id',
        'name',
        'slug',
        'code',
        'lang',
        'status',
        'created_by',
        'updated_by',
        'deleted_at',
    ];
}
