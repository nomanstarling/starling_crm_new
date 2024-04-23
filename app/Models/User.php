<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\LaravelSettings\Settings;
use Spatie\Valuestore\Valuestore;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class User extends Authenticatable implements Searchable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, Impersonate;
    
    public function getSearchResult(): SearchResult
    {
        $url = route('users.index', ['id' => $this->id]);

        return new SearchResult(
            $this,
            $this->name,
            $url
        );
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'business_id',
        'refno',
        'name',
        'slug',
        'user_name',
        'email',
        'email_secondary',
        'phone',
        'phone_secondary',
        'designation',
        'gender',
        'extention',
        'rera_no',
        'brn',
        'status',
        'is_teamleader',
        'photo',
        'lang',
        'instagram',
        'facebook',
        'linkedin',
        'whatsapp',
        'desc',
        'calls_goal_month',
        'off_market_listing_goal_month',
        'published_listing_goal_month',
        'rental_percent',
        'sales_percent',
        'yearly_target',
        'email_verified_at',
        'password',
        'google_access_token',
        'deleted_at',
    ];

    // protected static $logAttributes =[
    //     'name',
    //     'user_name',
    //     'email',
    //     'email_secondary',
    //     'phone',
    //     'phone_secondary',
    //     'designation',
    //     'gender',
    //     'extention',
    //     'rera_no',
    //     'brn',
    //     'status',
    //     'photo',
    //     'instagram',
    //     'facebook',
    //     'linkedin',
    //     'whatsapp',
    //     'desc',
    //     'calls_goal_month',
    //     'off_market_listing_goal_month',
    //     'published_listing_goal_month',
    //     'rental_percent',
    //     'sales_percent',
    //     'yearly_target',
    // ];

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnlyDirty() // Log only attributes that have changed
    //         ->attributeOldValuesBeforeMutators() // Include old values before any mutations
    //         ->attributeNewValueBeforeMutators(); // Include new values before any mutations;
    // }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::updated(function ($user) {
    //         $changes = $user->getChanges();

    //         // Check for changes in the roles relationship
    //         // $newRoles = $user->roles->pluck('name')->first();

    //         // // Include role changes in the $changes array
    //         // $oldRoles = $user->getOriginal('roles')->pluck('name')->first();
    //         // $changes['role'] = [
    //         //     'old' => $oldRoles,
    //         //     'new' => $newRoles,
    //         // ];

    //         // Log the activity with a custom message
    //         if (!empty($changes)) {
    //             $user->logActivity('Attributes updated', $changes);
    //         }
    //     });
    // }


    // public function logActivity($description, $properties = [])
    // {
    //     activity('user')
    //         ->performedOn($this)
    //         ->causedBy(auth()->user()) // Log the authenticated user as the causer
    //         ->withProperties([
    //             'description' => $description,
    //             'changes' => $properties,
    //         ])
    //         ->log('updated');
    // }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function profileImage()
    {
        if ($this->photo) {
            return asset('public/storage/' . $this->photo);
        }
        return asset('assets/media/svg/avatars/blank-dark.svg');
    }

    public function getProfileImageUrlAttribute()
    {
        if ($this->photo) {
            return asset('public/storage/' . $this->photo);
        }
        return asset('assets/media/svg/avatars/blank-dark.svg');
    }

    public function listings()
    {
        return $this->hasMany(Listings::class, 'agent_id')->where(function ($query) {
            $query->where('marketing_agent_id', $this->id)
                ->orWhereNull('marketing_agent_id');
        });
    }

    public function leads()
    {
        return $this->hasMany(Leads::class, 'agent_id');
    }

    public function meetings(){
        return $this->hasManyThrough(Notes::class, Leads::class, 'agent_id', 'object_id', 'id')
            ->where('notes.object_type', Leads::class)
            ->where('notes.type', 'meeting');
    }

    public function viewings(){
        return $this->hasManyThrough(Notes::class, Leads::class, 'agent_id', 'object_id', 'id')
            ->where('notes.object_type', Leads::class)
            ->where('notes.type', 'viewing');
    }

    public function reminders(){
        return $this->hasManyThrough(Notes::class, Leads::class, 'agent_id', 'object_id', 'id')
            ->where('notes.object_type', Leads::class)
            ->where('notes.type', 'reminder');
    }

    public function calls()
    {
        return $this->hasMany(crm_calls::class, 'user_id');
    }

    public function outGoingCalls()
    {
        return $this->hasMany(crm_calls::class, 'user_id')->where('direction', 'outgoing');
    }

    public function ansCalls()
    {
        return $this->hasMany(crm_calls::class, 'user_id')->where('direction', 'outgoing')->where('duration', '>', 0);
    }

    public function calls_count()
    {
        return $this->hasMany(crm_calls::class, 'user_id')->count();
    }

    public function listingsCount()
    {
        return $this->listings()->count();
    }

    public function offMarket()
    {
        return $this->listings()->whereHas('status', function ($query) {
            $query->where('name', 'Available - Off-Market');
        });
    }

    public function leadsNotContacted()
    {
        return $this->leads()->whereHas('status', function ($query) {
            $query->where('name', 'Not Yet Contacted');
        });
    }

    public function validLeads(){
        return Statuses::where('type', 'Leads')->where('lead_type', 'Active')->pluck('id');
    }

    public function leadsInProgress()
    {
        // return $this->leads()->whereHas('status', function ($query) {
        //     $query->whereIn('id', $this->validLeads());
        // });

        return $this->leads()->whereHas('status', function ($query) {
            $query->whereIn('name', ['Qualified', 'Viewing', 'Follow Up']);
        });
    }
    
    public function leadsAttemptContact()
    {
        return $this->leads()->whereHas('status', function ($query) {
            $query->where('name', 'Attempted To Contacted');
        });
    }

    public function published()
    {
        return $this->listings()->whereHas('status', function ($query) {
            $query->where('name', 'Available - Published');
        });
    }

    public function callsGoal(){
        $settings = Valuestore::make(config('settings.path'))->get('calls_goal');
        $goal = $this->calls_goal_month != null || $this->calls_goal_month != '' ? $this->calls_goal_month : $settings;
        return $goal > 0 ? round($goal / 22) : $goal;
    }

    public function offMarketGoal(){
        $settings = Valuestore::make(config('settings.path'))->get('off_market_goal');
        $goal = $this->off_market_listing_goal_month != null || $this->off_market_listing_goal_month != '' ? $this->off_market_listing_goal_month : $settings;
        return round($goal / 22);
    }

    public function publishedGoal(){
        $settings = Valuestore::make(config('settings.path'))->get('published_goal');
        $goal = $this->published_listing_goal_month != null || $this->published_listing_goal_month != '' ? $this->published_listing_goal_month : $settings;
        return round($goal / 22);
    }

    public function login_history()
    {
        return $this->hasMany(Activity::class, 'causer_id')
            ->where('description', 'like', 'User logged in%')
            ->latest();
    }

    // public function activities()
    // {
    //     return $this->hasMany(Activity::class, 'causer_id')
    //         ->whereIn('description', ['created', 'updated'])
    //         ->orderBy('created_at', 'desc') // Order by created_at in descending order
    //         ->orderBy('id', 'desc') // Order by id in descending order as a tiebreaker
    //         ->with('causer'); // Load the 'causer' relation
    // }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'causer_id')
            ->whereIn('description', ['created', 'updated'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->with('causer');
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

    // public function clearActivitiesCache()
    // {
    //     $this->setRelation('activities', null);
    // }

    // public function change_log()
    // {
    //     return $this->hasMany(Activity::class, 'causer_id')
    //         ->whereIn('description', ['created', 'updated'])
    //         ->with('causer') // Load the 'causer' relation
    //         ->latest()
    //         ->get() // Retrieve results as a collection
    //         ->map(function ($activity) {
    //             $changes = optional($activity->properties)['changes'] ?? null;

    //             return [
    //                 'description' => $activity->description,
    //                 'changes' => $changes,
    //                 'causer_name' => optional($activity->causer)->name, // Get the causer's name or null if not available
    //                 'created_at' => Carbon::parse($activity->created_at)->diffForHumans(),
    //             ];
    //         });
    // }



    public function teams()
    {
        //return $this->belongsToMany(teams::class);
        return $this->belongsToMany(Teams::class, 'team_users', 'user_id', 'team_id');
    }

    public function team()
    {
        //return $this->teams()->first();
        return $this->belongsTo(Teams::class, 'team_leader', 'id');
    }
}
