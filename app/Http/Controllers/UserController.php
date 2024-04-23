<?php

// app\Http\Controllers\Admin\UserController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teams;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use App\Events\UserUpdated;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        return view('admin.users.index', compact('roles'));
    }

    // public function getUsers(Request $request)
    // {
    //     $status = $request->query('status', 1);
    //     $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

    //     $perPage = $request->input('length', 6); // Number of records per page

    //     // Query users with roles and apply where clause for status if it exists
    //     $usersQuery = User::with(['roles', 'login_history'])
    //         ->when($status !== null, function ($query) use ($status) {
    //             if ($status === 'deleted') {
    //                 // Retrieve only soft-deleted users
    //                 return $query->onlyTrashed();
    //             }
    //             else if ($status == 1 || $status == 0) {
    //                 // Include soft-deleted users if status is 'deleted'
    //                 return $query->where('status', $status);
    //             } 
    //         })
    //         ->latest('updated_at');
    //     $users = $usersQuery->paginate($perPage);
    //     //->get();

    //     $usersWithDetails = $users->map(function ($user) {
    //         $lastLogin = $user->login_history->where('description', 'User logged in')->first();
    
    //         return array_merge($user->toArray(), [
    //             'profile_image_url' => $user->profileImage(),
    //             'role' => $user->roles->isNotEmpty() ? $user->roles->first()->name : 'N/A',
    //             'status' => $user->status ? 'Active' : 'Inactive',
    //             'listings' => $user->listingsCount(),
    //             'last_login' => $lastLogin ? 'Logged in '.$lastLogin->created_at->diffForHumans() : 'Never logged in',
    //         ]);
    //     });

    //     //return response()->json(['users' => $usersWithDetails]);

    //     return response()->json([
    //         'draw' => $request->input('draw'),
    //         'recordsTotal' => User::count(), // Total records in the database
    //         'recordsFiltered' => $users->total(), // Total records after filtering (if any)
    //         'users' => $usersWithDetails,
    //     ]);
        
    // }


    public function getList(Request $request)
    {
        $users = User::orderBy('name')->get();

        $usersWithDetails = $users->map(function ($user) {
            return array_merge($user->toArray(), [
                'profile_image_url' => $user->profileImage(),
            ]);
        });
        return response()->json(['users' => $usersWithDetails]);
    }

    public function getUsers(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $perPage = $request->input('length', 100); // Number of records per page

        // Query users with roles and apply where clause for status if it exists
        $usersQuery = User::with(['roles', 'login_history'])
            ->when($status !== null, function ($query) use ($status) {
                if ($status === 'deleted') {
                    // Retrieve only soft-deleted users
                    return $query->onlyTrashed();
                } else if ($status == 1 || $status == 0) {
                    // Include soft-deleted users if status is 'deleted'
                    return $query->where('status', $status);
                }
            })
            ->latest('updated_at');

        if(auth()->user()->is_teamleader == true){
            $team = Teams::with('users')->where('team_leader', auth()->user()->id)->first();
            //$user_ids = $team->users->pluck('id');
            $user_ids = [];
            if ($team && $team->users->isNotEmpty()) {
                $user_ids = $team->users->pluck('id')->toArray();
            }
            if (!empty($user_ids)) {
                $users = $usersQuery->whereIn('id', $user_ids);
            }
        }

        $users = $usersQuery->paginate($perPage);

        $usersWithDetails = $users->map(function ($user) {
            $lastLogin = $user->login_history->where('description', 'User logged in')->first();

            return array_merge($user->toArray(), [
                'profile_image_url' => $user->profileImage(),
                'role' => $user->roles->isNotEmpty() ? $user->roles->first()->name : 'N/A',
                'status' => $user->status ? 'Active' : 'Inactive',
                'listings' => $user->listingsCount(),
                'calls_count' => $user->calls_count(),
                'last_login' => $lastLogin ? 'Logged in ' . $lastLogin->created_at->diffForHumans() : 'Never logged in',
            ]);
        });

        // return response()->json([
        //     'draw' => $request->input('draw'),
        //     'recordsTotal' => $users->total(), // Total records after filtering (if any)
        //     'recordsFiltered' => $users->total(),
        //     'data' => $usersWithDetails, // Renamed 'users' to 'data' for DataTables compatibility
        // ]);

        // Inside your controller method
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $users->total(),
            'recordsFiltered' => $users->total(),
            'data' => $usersWithDetails,
            'pagination' => [
                'total' => $users->total(),
                'perPage' => $users->perPage(),
                'currentPage' => $users->currentPage(),
            ],
        ]);

    }

    public function getTeamUsers(Request $request){
        $team_id = $request->input('team_id');
        $user_ids = [];
        if(auth()->user()->is_teamleader == true){
            $team = Teams::with('users')->where('team_leader', auth()->user()->id)->first();
            if ($team && $team->users->isNotEmpty()) {
                $user_ids = $team->users->pluck('id')->toArray();
            }
            //$user_ids = $team->users->pluck('id');
        }

        if(!empty($team_id)){
            $team = Teams::with('users')->where('team_leader', $team_id)->first();
            //$user_ids = $team->users->pluck('id');
            if ($team && $team->users->isNotEmpty()) {
                $user_ids = $team->users->pluck('id')->toArray();
            }
        }

        // Retrieve users based on user IDs if $user_ids is not empty, else retrieve all users
        if (!empty($user_ids)) {
            $users = User::whereIn('id', $user_ids)->get();
        } else {
            $users = User::all();
        }
        
        $users = $users->map(function ($user) {
            return array_merge($user->toArray(), [
                'profile_image_url' => $user->profileImage(),
            ]);
        });

        return response()->json(['users' => $users]);
    }


    // public function getUsers(Request $request)
    // {
    //     // Get parameters from DataTables
    //     $draw = $request->input('draw');
    //     $start = $request->input('start');
    //     $length = $request->input('length');
    //     $search = $request->input('search.value');
    //     $orderColumn = $request->input('order.0.column');
    //     $orderDirection = $request->input('order.0.dir');

    //     // Get the status value from the URL query parameters
    //     $status = $request->query('status', 'active');
        
    //     // Map the status value to a boolean or null
    //     $status = ($status == 'active') ? true : (($status == 'inactive') ? false : ($status == 'deleted' ? 'deleted' : null));

    //     // Query users with roles and apply where clause for status if it exists
    //     $usersQuery = User::with(['roles', 'activities'])
    //         ->when($status !== null, function ($query) use ($status) {
    //             if ($status === 'deleted') {
    //                 // Retrieve only soft-deleted users
    //                 return $query->onlyTrashed();
    //             } else if ($status === true || $status === false) {
    //                 // Include soft-deleted users if status is 'deleted'
    //                 return $query->where('status', $status);
    //             } 
    //         });

    //     // Apply search filter
    //     if ($search) {
    //         $usersQuery->where(function ($query) use ($search) {
    //             $query->where('name', 'like', "%$search%")
    //                 ->orWhere('email', 'like', "%$search%")
    //                 ->orWhere('phone', 'like', "%$search%")
    //                 ->orWhere('gender', 'like', "%$search%")
    //                 ->orWhere('brn', 'like', "%$search%")
    //                 ->orWhere('rera_no', 'like', "%$search%")
    //                 ->orWhere('extention', 'like', "%$search%")
    //                 ->orWhereHas('listings', function ($listingsQuery) use ($search) {
    //                     $listingsQuery->where('column_in_listings_table', 'like', "%$search%");
    //                 })
    //                 ->orWhereHas('roles', function ($roleQuery) use ($search) {
    //                     $roleQuery->where('name', 'like', "%$search%");
    //                 }); // Add more columns as needed
    //             // Add your search conditions for other columns...
    //         });
    //     }


    //     // Apply ordering
    //     if ($orderColumn !== null) {
    //         $orderColumn = $orderColumn == 1 ? 'name' : ($orderColumn == 2 ? 'role' : 'updated_at');
    //         $usersQuery->orderBy($orderColumn, $orderDirection);
    //     }

    //     // Get total records count
    //     $totalRecords = $usersQuery->count();

    //     // Get paginated records
    //     $users = $usersQuery->skip($start)->take($length)->get();

    //     $usersWithDetails = $users->map(function ($user) {
    //         $lastLogin = $user->activities->where('description', 'User logged in')->first();

    //         return array_merge($user->toArray(), [
    //             'profile_image_url' => $user->profileImage(),
    //             'role' => $user->roles->isNotEmpty() ? $user->roles->first()->name : 'N/A',
    //             'status' => $user->status ? 'Active' : 'Inactive',
    //             'listings' => $user->listingsCount(),
    //             'last_login' => $lastLogin ? 'Logged in '.$lastLogin->created_at->diffForHumans() : 'Never logged in',
    //         ]);
    //     });

    //     return response()->json([
    //         'draw' => $draw,
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $totalRecords,
    //         'users' => $usersWithDetails,
    //     ]);
    // }


    // public function edit(User $user)
    // {
    //     $userWithRoles = $user->load('roles');
    //     $userWithRoles['profile_image'] = $user->profileImage();
    //     $userWithRoles['activities'] = $userWithRoles->change_log();
    //     return $userWithRoles['activities'];
        
    //     return response()->json(['user' => $userWithRoles]);
    // }

    // public function edit(User $user)
    // {
    //     $userWithRoles = $user->load('roles', 'activities');
    //     $userWithRoles['profile_image'] = $user->profileImage();

    //     $activitiesWithChanges = $userWithRoles->activities->map(function ($activity) {
    //         // Include 'changes' property if it exists
    //         $changes = $activity->changes ?? null;

    //         return [
    //             'description' => $activity->description,
    //             'created_at' => $activity->created_at->diffForHumans(),
    //             'changes' => $changes,
    //         ];
    //     });

    //     $userWithRoles['activities'] = $activitiesWithChanges;

    //     return response()->json(['user' => $userWithRoles]);
    // }


    public function edit(User $user)
    {
        // Load roles and profile image for the user
        $userWithRoles = $user->load('roles');
        $userWithRoles['profile_image'] = $user->profileImage();

        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($user) {
                $query->where('subject_id', $user->id)
                    ->where('subject_type', get_class($user));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $userWithRoles['activities'] = $activities;
        // Include activities in the JSON response
        return response()->json([
            'user' => $userWithRoles,
        ]);
    }


    // public function show(Role $role)
    // {
    //     $permissions = Permission::all();

    //     return response()->json([
    //         'role' => $role,
    //         'permissions' => $permissions,
    //     ]);
    // }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'status' => 'required|boolean',
            'rera_no' => 'nullable|string|max:255',
            'brn' => 'nullable|string|max:255',
            'extention' => 'nullable|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name',
            'password' => 'nullable|string|min:3',
            'role' => 'nullable|exists:roles,id',
            'rental_percent' => 'nullable|numeric',
            'sales_percent' => 'nullable|numeric',
            'yearly_target' => 'nullable|numeric',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'phone_secondary' => 'nullable|string|max:255',
            'email_secondary' => 'nullable|email|max:255',
        ]);
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('uploads/users/images', 'public');
        }

        $is_teamleader = false;

        if ($request->has('is_teamleader')) {
            $is_teamleader = true;
        } else {
            $is_teamleader = false;
        }
        
        // Create the user
        $user = User::create([
            'name' => $request->input('name'),
            'type' => 'individual',
            'is_teamleader' => $is_teamleader,
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'designation' => $request->input('designation'),
            'gender' => $request->input('gender'),
            'status' => $request->input('status'),
            'rera_no' => $request->input('rera_no'),
            'brn' => $request->input('brn'),
            'extention' => $request->input('extention'),
            'user_name' => $request->input('user_name'),
            'password' => $request->input('password') ? Hash::make($request->input('password')) : null,
            'rental_percent' => $request->input('rental_percent'),

            'calls_goal_month' => $request->input('calls_goal_month'),
            'off_market_listing_goal_month' => $request->input('off_market_listing_goal_month'),
            'published_listing_goal_month' => $request->input('published_listing_goal_month'),

            'sales_percent' => $request->input('sales_percent'),
            'yearly_target' => $request->input('yearly_target'),
            
            'instagram' => $request->input('instagram'),
            'facebook' => $request->input('facebook'),
            'linkedin' => $request->input('linkedin'),
            'whatsapp' => $request->input('whatsapp'),
            'phone_secondary' => $request->input('phone_secondary'),
            'email_secondary' => $request->input('email_secondary'),
            'photo' => $avatarPath,
        ]);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties('User created.')
            ->log('created');

        // Attach the role to the user
        if ($request->has('role') && $request->input('role')) {
            $user->roles()->attach($request->input('role'));
        }

        // Add other fields as needed

        //notify()->success('User created successfully', 'Success');

        return response()->json(['message' => 'User created successfully']);
    }

    // public function update(Request $request, $id)
    // {
    //     // Validate the request data
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email,' . $id,
    //         'phone' => 'required|string|max:255',
    //         'designation' => 'nullable|string|max:255',
    //         'gender' => 'required|string|in:Male,Female',
    //         'status' => 'required|boolean',
    //         'rera_no' => 'nullable|string|max:255',
    //         'brn' => 'nullable|string|max:255',
    //         'extention' => 'nullable|string|max:255',
    //         'user_name' => 'required|string|max:255|unique:users,user_name,' . $id,
    //         'role' => 'nullable|exists:roles,id',
    //         'rental_percent' => 'nullable|numeric',
    //         'sales_percent' => 'nullable|numeric',
    //         'yearly_target' => 'nullable|numeric',
    //         'phone_secondary' => 'nullable|string|max:255',
    //         'email_secondary' => 'nullable|email|max:255',
    //     ]);

    //     // Find the user
    //     $user = User::findOrFail($id);
        
    //     $avatarPath = $user->photo;
    //     if ($request->hasFile('avatar')) {
    //         // Store the uploaded file
    //         $avatarPath = $request->file('avatar')->store('uploads/users/images', 'public');
    //     }

    //     // Get the original user data before the update
    //     // $originalData = $user->getOriginal();

    //     // // Check for changes in each column and create log entries for changed values
    //     // $changes = [];
    //     // foreach ($validatedData as $key => $value) {
    //     //     // Check if the key exists in the original data
    //     //     if (array_key_exists($key, $originalData) && $originalData[$key] != $value) {
    //     //         $changes[$key] = [
    //     //             'old' => $originalData[$key],
    //     //             'new' => $value,
    //     //         ];
    //     //     }
    //     // }

    //     // Update the user
    //     $user->update([
    //         'name' => $request->input('name'),
    //         'email' => $request->input('email'),
    //         'phone' => $request->input('phone'),
    //         'designation' => $request->input('designation'),
    //         'gender' => $request->input('gender'),
    //         'status' => $request->input('status'),
    //         'rera_no' => $request->input('rera_no'),
    //         'brn' => $request->input('brn'),
    //         'extention' => $request->input('extention'),
    //         'user_name' => $request->input('user_name'),
    //         'password' => $request->input('password') ? Hash::make($request->input('password')) : $user->password,
    //         'rental_percent' => $request->input('rental_percent'),
    //         'sales_percent' => $request->input('sales_percent'),
    //         'yearly_target' => $request->input('yearly_target'),
    //         'calls_goal_month' => $request->input('calls_goal_month'),
    //         'off_market_listing_goal_month' => $request->input('off_market_listing_goal_month'),
    //         'published_listing_goal_month' => $request->input('published_listing_goal_month'),
    //         'instagram' => $request->input('instagram'),
    //         'facebook' => $request->input('facebook'),
    //         'linkedin' => $request->input('linkedin'),
    //         'whatsapp' => $request->input('whatsapp'),
    //         'phone_secondary' => $request->input('phone_secondary'),
    //         'email_secondary' => $request->input('email_secondary'),
    //         'photo' => $avatarPath,
    //     ]);

    //     // Check for changes in 'role'
    //     // if ($request->has('role')) {
    //     //     $oldRoleName = $user->roles->first()->name;
    //     //     $newRole = Role::find($request->input('role'));
    //     //     $newRoleName = $newRole ? $newRole->name : null;

    //     //     if ($oldRoleName != $newRoleName) {
    //     //         $changes['role'] = [
    //     //             'old' => $oldRoleName,
    //     //             'new' => $newRoleName,
    //     //         ];
    //     //     }
    //     // }

    //     // Sync the user's roles
    //     $user->roles()->sync([$request->input('role')]);

    //     // Log changes only if there are any
    //     // if (!empty($changes)) {
    //     //     activity()
    //     //         ->performedOn($user)
    //     //         ->withProperties(['changes' => $changes])
    //     //         ->log('updated');
    //     // }
        
    //     //notify()->success('User updated successfully', 'Success');
    //     return response()->json(['message' => 'User updated successfully.']);
    // }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'status' => 'required|boolean',
            'rera_no' => 'nullable|string|max:255',
            'brn' => 'nullable|string|max:255',
            'extention' => 'nullable|string|max:255',
            'user_name' => 'required|string|max:255|unique:users,user_name,' . $id,
            'rental_percent' => 'nullable|numeric',
            'sales_percent' => 'nullable|numeric',
            'yearly_target' => 'nullable|numeric',
            'phone_secondary' => 'nullable|string|max:255',
            'email_secondary' => 'nullable|email|max:255',
        ]);

        // Find the user
        $user = User::findOrFail($id);
        $originalValues = $user->getOriginal();
        
        $avatarPath = $user->photo;
        if ($request->hasFile('avatar')) {
            // Store the uploaded file
            $avatarPath = $request->file('avatar')->store('uploads/users/images', 'public');
        }

        // Save the original roles before updating
        $originalRole = $user->roles->first();
        $originalUpdatedAt = $user->updated_at;

        $is_teamleader = false;

        if ($request->has('is_teamleader')) {
            $is_teamleader = true;
        } else {
            $is_teamleader = false;
        }
        $is_teamleader_old = $user->is_teamleader;
        // Update the user
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'designation' => $request->input('designation'),
            'gender' => $request->input('gender'),
            'status' => $request->input('status'),
            'is_teamleader' => $is_teamleader,
            'rera_no' => $request->input('rera_no'),
            'brn' => $request->input('brn'),
            'extention' => $request->input('extention'),
            'user_name' => $request->input('user_name'),
            'password' => $request->input('password') ? Hash::make($request->input('password')) : $user->password,
            'rental_percent' => $request->input('rental_percent'),
            'sales_percent' => $request->input('sales_percent'),
            'yearly_target' => $request->input('yearly_target'),
            'calls_goal_month' => $request->input('calls_goal_month'),
            'off_market_listing_goal_month' => $request->input('off_market_listing_goal_month'),
            'published_listing_goal_month' => $request->input('published_listing_goal_month'),
            'instagram' => $request->input('instagram'),
            'facebook' => $request->input('facebook'),
            'linkedin' => $request->input('linkedin'),
            'whatsapp' => $request->input('whatsapp'),
            'phone_secondary' => $request->input('phone_secondary'),
            'email_secondary' => $request->input('email_secondary'),
            'photo' => $avatarPath,
        ]);
    
        // Handle role change manually
        if ($request->has('role') && $request->input('role')) {
            $newRoleId = $request->input('role');
            $newRole = Role::find($newRoleId)->name;
        
            // Check if role has changed
            if ($originalRole && $newRoleId != $originalRole->id) {
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties(['updated role from ' . $originalRole->name . ' to ' . $newRole])
                    ->log('updated');

                // Synchronize the role and update the user's timestamp
                
                $user->touch(['updated_at' => $originalUpdatedAt]); // Update only the specified timestamps
            }
            $user->roles()->sync([$newRoleId]);
        }

        // Get the updated values after updating
        $updatedValues = $user->getAttributes();
        unset($updatedValues['updated_at']);

        // Log the changes
        $logDetails = [];

        foreach ($updatedValues as $field => $newValue) {
            $oldValue = $originalValues[$field];

            // Check if the field has changed
            if ($oldValue != $newValue) {
                if ($field == 'is_teamleader') {

                    $is_teamleader_old = $is_teamleader_old == true ? 'enabled' : 'disabled';
                    $is_teamleader = $is_teamleader == true ? 'enabled' : 'disabled';
    
                    $logDetails[] = "Team Leader: $is_teamleader_old to $is_teamleader";
                }
                else {
                    $logDetails[] = "$field: $oldValue to $newValue";
                }
                //$logDetails[] = "$field: $oldValue to $newValue";
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }

        // Trigger the event
        //event(new UserUpdated($user));
        //event(new UserUpdated('hello world'));

        //notify()->success('User updated successfully', 'Success');
        return response()->json(['message' => 'User updated successfully.']);
    }

    public function destroy(User $user)
    {
        // Detach all roles associated with the user
        $user->roles()->detach();

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties('User is deleted.')
            ->log('updated');

        // Delete the user
        $user->delete();
        //notify()->success('User deleted successfully', 'Success');
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $userIds = $request->input('user_ids');

        // Check if any user IDs are provided
        if (empty($userIds)) {
            return response()->json(['message' => 'No user IDs provided for bulk delete.']);
        }

        try {
            foreach($userIds as $userid){
                $user = User::findOrFail($userid);
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties('User is deleted through bulk action.')
                    ->log('updated');
            }
            // Use the User model to delete users by IDs
            User::whereIn('id', $userIds)->delete();

            return response()->json(['message' => 'Bulk delete successful']);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['message' => 'Error during bulk delete: ' . $e->getMessage()], 500);
        }
    }

    public function bulkRestore(Request $request)
    {
        $userIds = $request->input('user_ids');

        // Check if any user IDs are provided
        if (empty($userIds)) {
            return response()->json(['message' => 'No user IDs provided for bulk restore users.']);
        }

        try {

            // Use the User model to delete users by IDs
            User::whereIn('id', $userIds)->restore();

            foreach($userIds as $userid){
                $user = User::findOrFail($userid);
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties('User is restored through bulk action.')
                    ->log('updated');
            }

            return response()->json(['message' => 'Bulk restored successful']);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['message' => 'Error during bulk restoring: ' . $e->getMessage()], 500);
        }
    }

    public function bulkActivate(Request $request)
    {
        $userIds = $request->input('user_ids');

        // Check if any user IDs are provided
        if (empty($userIds)) {
            return response()->json(['message' => 'No user IDs provided for bulk activation.']);
        }

        try {
            foreach($userIds as $userid){
                $user = User::findOrFail($userid);
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties('User is activated through bulk action.')
                    ->log('updated');
            }
            // Use the User model to update the status to 1 for users by IDs
            User::whereIn('id', $userIds)->update(['status' => true]);

            return response()->json(['message' => 'Bulk activation successful']);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the update process
            return response()->json(['message' => 'Error during bulk activation: ' . $e->getMessage()], 500);
        }
    }

    public function bulkDeactivate(Request $request)
    {
        $userIds = $request->input('user_ids');

        // Check if any user IDs are provided
        if (empty($userIds)) {
            return response()->json(['message' => 'No user IDs provided for bulk deactivation.']);
        }

        try {
            foreach($userIds as $userid){
                $user = User::findOrFail($userid);
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties('User is deactivated through bulk action.')
                    ->log('updated');
            }
            // Use the User model to update the status to 0 for users by IDs
            User::whereIn('id', $userIds)->update(['status' => 0]);

            return response()->json(['message' => 'Bulk deactivation successful']);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the update process
            return response()->json(['message' => 'Error during bulk deactivation: ' . $e->getMessage()], 500);
        }
    }

}
