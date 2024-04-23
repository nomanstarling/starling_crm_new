<?php

namespace App\Http\Controllers;

use App\Models\Campaigns;

use App\Models\communities;
use App\Models\sub_communities;
use App\Models\towers;
use App\Models\Sources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class CampaignsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $firstDate = Campaigns::min('created_at');
        $sources = Sources::get();
        return view('admin.campaigns.index', compact('firstDate', 'sources'));
    }

    public function getCampaigns(Request $request)
    {
        $status = $request->input('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $startCreatedDate = $request->input('startCreatedDate');
        $endCreatedDate = $request->input('endCreatedDate');

        $campaigns = Campaigns::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });
        if ($startDate && $endDate) {
            $campaigns->where(function ($query) use ($startDate, $endDate) {
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
            $campaigns->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        // if ($request->input('team_leader') != null) {
        //     $teams->whereHas('team_leader', function ($query) use ($request) {
        //         $query->where('name', 'like', '%' .$request->input('team_leader'). '%');
        //     });
        // }
        
        if ($request->input('campaign') != null) {
            $campaigns->where('name', 'like', '%' . $request->input('campaign') . '%');
        }

        $totalRecords = $campaigns->count(); // Fetch the total count
        $perPage = $request->input('length', 10); // Number of records per page
        
        $campaigns = $campaigns->latest('updated_at')
            ->with('community', 'sub_community', 'tower', 'source')
            ->skip(($request->input('start', 0) / $perPage) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($campaign) {
                // Include additional information for related models
                return array_merge( $campaign->toArray(), ['total_members' => count($campaign->users),]);
        });

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $campaigns,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:campaigns',
        ]);
        
        $isAutoAssignChecked = $request->has('auto_assign') && $request->input('auto_assign') == 1;

        $campaign = Campaigns::create([
            'name' => $request->input('name'),
            'target_name' => $request->input('target_name'),

            'community_id' => $request->input('community_id'),
            'sub_community_id' => $request->input('sub_community_id'),
            'tower_id' => $request->input('tower_id'),
            'source_id' => $request->input('source_id'),
            'auto_assign' => $isAutoAssignChecked ? true : false,
            'auto_assign_after' => $request->input('auto_assign_after'),
            'status' => true,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        // Get the selected agents from the form
        $selectedAgents = $request->input('agents', []);

        // Sync the selected agents with the team
        $campaign->users()->attach($selectedAgents);

        activity()
            ->performedOn($campaign)
            ->causedBy(auth()->user())
            ->withProperties('Campaign created.')
            ->log('created');

        return response()->json(['message' => 'Campaign created successfully']);
    }

    public function edit(Campaigns $campaign)
    {
        // Eager load documents
        $loaded = $campaign->load('users');
        
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

        return response()->json(['campaign' => $loaded]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:campaigns,name,' . $id,
            'agents' => 'array',
        ]);

        $isAutoAssignChecked = $request->has('auto_assign') && $request->input('auto_assign') == 1;

        // Find the team
        $campaign = Campaigns::findOrFail($id);

        $originalValues = $campaign->getOriginal();

        // Update the team
        $campaign->update([
            'name' => $request->input('name'),
            'target_name' => $request->input('target_name'),
            'community_id' => $request->input('community_id'),
            'sub_community_id' => $request->input('sub_community_id'),
            'tower_id' => $request->input('tower_id'),
            'source_id' => $request->input('source_id'),
            'auto_assign' => $isAutoAssignChecked ? true : false,
            'auto_assign_after' => $request->input('auto_assign_after'),
            'updated_by' => auth()->user()->id,
        ]);

        // Get the selected agents from the form
        $selectedAgents = $request->input('agents', []);

        // Sync the selected agents with the team
        $campaign->users()->sync($selectedAgents);

        // Get the updated values after updating
        $updatedValues = $campaign->getAttributes();
        unset($updatedValues['updated_at']);

        // Log the changes
        $logDetails = [];

        foreach ($updatedValues as $field => $newValue) {
            $oldValue = $originalValues[$field];
    
            // Check if the field has changed
            if ($oldValue != $newValue) {
                // If the changed field is 'city_id', handle null values
                if ($field == 'source_id') {
                    $oldName = $oldValue ? Sources::find($oldValue)->name : 'empty';
                    $newName = $newValue ? Sources::find($newValue)->name : 'empty';
    
                    $logDetails[] = "Source : $oldName to $newName";
                }
                else if ($field == 'community_id') {
                    $oldName = $oldValue ? communities::find($oldValue)->name : 'empty';
                    $newName = $newValue ? communities::find($newValue)->name : 'empty';
    
                    $logDetails[] = "Community : $oldName to $newName";
                }
                else if ($field == 'sub_community_id') {
                    $oldName = $oldValue ? sub_communities::find($oldValue)->name : 'empty';
                    $newName = $newValue ? sub_communities::find($newValue)->name : 'empty';
    
                    $logDetails[] = "Sub Community : $oldName to $newName";
                }
                else if ($field == 'tower_id') {
                    $oldName = $oldValue ? towers::find($oldValue)->name : 'empty';
                    $newName = $newValue ? towers::find($newValue)->name : 'empty';
    
                    $logDetails[] = "Tower : $oldName to $newName";
                } else {
                    $logDetails[] = "$field: $oldValue to $newValue";
                }
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($campaign)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Campaign updated successfully.']);
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
                $data = Campaigns::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Campaign is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            Campaigns::whereIn('id', $itemIds)->delete();

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
            Campaigns::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = Campaigns::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Campaign is restored through bulk action.')
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
                $data = Campaigns::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Campaign is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            Campaigns::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = Campaigns::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Campaign is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = Campaigns::whereIn('id', $itemIds)->update(['status' => 0]);
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
