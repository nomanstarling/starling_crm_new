<?php

namespace App\Http\Controllers;

use App\Models\teams;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class TeamsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $firstDate = teams::min('created_at');
        return view('admin.teams.index', compact('firstDate'));
    }

    public function getTeams(Request $request)
    {
        $status = $request->input('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $startCreatedDate = $request->input('startCreatedDate');
        $endCreatedDate = $request->input('endCreatedDate');

        $teams = teams::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });
        if ($startDate && $endDate) {
            $teams->where(function ($query) use ($startDate, $endDate) {
                if ($startDate === $endDate) {
                    // When start date and end date are the same, use whereDate
                    $query->whereDate('updated_at', $startDate);
                } else {
                    // Use whereBetween for a range of dates
                    //$query->whereBetween('updated_at', [$startDate, $endDate]);
                    $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            });
        }

        if ($startCreatedDate && $endCreatedDate) {
            $teams->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
                if ($startCreatedDate === $endCreatedDate) {
                    // When start date and end date are the same, use whereDate
                    $query->whereDate('created_at', $startCreatedDate);
                } else {
                    // Use whereBetween for a range of dates
                    //$query->whereBetween('created_at', [$startCreatedDate, $endCreatedDate]);
                    $query->whereBetween('created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
                }
            });
        }

        if ($request->input('team_leader') != null) {
            $teams->whereHas('team_leader', function ($query) use ($request) {
                $query->where('name', 'like', '%' .$request->input('team_leader'). '%');
            });
        }
        
        if ($request->input('team') != null) {
            $teams->where('name', 'like', '%' . $request->input('team') . '%');
        }

        $totalRecords = $teams->count(); // Fetch the total count
        $perPage = $request->input('length', 10); // Number of records per page
        
        $teams = $teams->latest('updated_at')
            ->with('team_leader')
            ->skip(($request->input('start', 0) / $perPage) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($team) {
                // Include additional information for related models
                return array_merge( $team->toArray(), ['total_members' => count($team->users),]);
        });

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $teams,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:teams',
            'team_leader' => 'required',
        ]);
        
        $team = teams::create([
            'name' => $request->input('name'),
            'team_leader' => $request->input('team_leader'),
            'status' => true,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        // Get the selected agents from the form
        $selectedAgents = $request->input('agents', []);

        // Sync the selected agents with the team
        $team->users()->attach($selectedAgents);

        activity()
            ->performedOn($team)
            ->causedBy(auth()->user())
            ->withProperties('Team created.')
            ->log('created');

        return response()->json(['message' => 'Team created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\teams  $teams
     * @return \Illuminate\Http\Response
     */
    public function show(teams $teams)
    {
        //
    }

    public function edit(teams $team)
    {
        // Eager load documents
        $loaded = $team->load('users');
        
        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($loaded) {
                $query->where('subject_id', $loaded->id)
                    ->where('subject_type', get_class($loaded));
            })
            ->orderBy('created_at', 'desc')
            ->where('properties', '!=', null)
            ->where('properties', '!=', '[]')
            ->get();

        

        $loaded['activities'] = $activities;

        return response()->json(['team' => $loaded]);
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $id,
            'team_leader' => 'required',
            'agents' => 'array',
        ]);

        // Find the team
        $team = teams::findOrFail($id);

        $originalValues = $team->getOriginal();

        // Update the team
        $team->update([
            'name' => $request->input('name'),
            'team_leader' => $request->input('team_leader'),
            'updated_by' => auth()->user()->id,
        ]);

        // Get the selected agents from the form
        $selectedAgents = $request->input('agents', []);

        // Sync the selected agents with the team
        $team->users()->sync($selectedAgents);

        // Get the updated values after updating
        $updatedValues = $team->getAttributes();
        unset($updatedValues['updated_at']);

        // Log the changes
        $logDetails = [];

        foreach ($updatedValues as $field => $newValue) {
            $oldValue = $originalValues[$field];
    
            // Check if the field has changed
            if ($oldValue != $newValue) {
                // If the changed field is 'city_id', handle null values
                if ($field == 'team_leader') {
                    $oldName = $oldValue ? User::find($oldValue)->name : 'empty';
                    $newName = $newValue ? User::find($newValue)->name : 'empty';
    
                    $logDetails[] = "Team Leader : $oldName to $newName";
                } else {
                    $logDetails[] = "$field: $oldValue to $newValue";
                }
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($team)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Team updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\teams  $teams
     * @return \Illuminate\Http\Response
     */
    public function destroy(teams $teams)
    {
        //
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk delete.']);
        }

        try {

            foreach($itemIds as $itemId){
                $data = teams::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Team is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            teams::whereIn('id', $itemIds)->delete();

            return response()->json(['message' => 'Bulk delete successful']);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['error' => 'Error during bulk delete: ' . $e->getMessage()], 500);
        }
    }

    public function bulkRestore(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk restore users.']);
        }

        try {

            // Use the User model to delete users by IDs
            teams::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = teams::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Team is restored through bulk action.')
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
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk activation.']);
        }

        try {

            foreach($itemIds as $itemId){
                $data = teams::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Team is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            teams::whereIn('id', $itemIds)->update(['status' => 1]);

            return response()->json(['message' => 'Bulk activation successful']);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the update process
            return response()->json(['message' => 'Error during bulk activation: ' . $e->getMessage()], 500);
        }
    }

    public function bulkDeactivate(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk deactivation.']);
        }

        try {
            
            foreach($itemIds as $itemId){
                $data = teams::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Team is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = teams::whereIn('id', $itemIds)->update(['status' => 0]);
            if ($update > 0) {
                return response()->json(['message' => 'Bulk deactivation successful']);
            } else {
                return response()->json(['error' => 'No records were updated']);
            }

        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the update process
            return response()->json(['message' => 'Error during bulk deactivation: ' . $e->getMessage()], 500);
        }
    }
}
