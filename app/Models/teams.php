<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class teams extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teams';

    protected $fillable = [
        'business_id',
        'name',
        'team_leader',
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

    public function team_leader()
    {
        return $this->belongsTo(User::class, 'team_leader', 'id');
    }

    // public function users()
    // {
    //     return $this->belongsToMany(teams::class, 'team_users', 'team_id', 'user_id');
    // }

    // Team.php model
    public function users()
    {
        //return $this->belongsToMany(User::class, 'team_users', 'team_id', 'user_id');
        return $this->belongsToMany(User::class, 'team_users', 'team_id', 'user_id');
    }

}
