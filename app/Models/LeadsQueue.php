<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadsQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'lead_id',
        'body'
    ];
}
