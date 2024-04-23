<?php

namespace App\Http\Controllers;

use App\Models\developers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class DevelopersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $firstDate = developers::min('created_at');
        return view('admin.developers.index', compact('firstDate'));
    }
    
    public function getDevelopers(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startCreatedDate = $request->query('startCreatedDate');
        $endCreatedDate = $request->query('endCreatedDate');

        $developers = developers::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });

        if ($startDate && $endDate) {
            $developers->where(function ($query) use ($startDate, $endDate) {
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
            $developers->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
                if ($startCreatedDate === $endCreatedDate) {
                    // When start date and end date are the same, use whereDate
                    $query->whereDate('created_at', $startCreatedDate);
                } else {
                    // Use whereBetween for a range of dates
                    //$query->whereBetween('created_at', ['2024-02-13', '2024-02-19']);
                    $query->whereBetween('created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
                }
            });
        }

        $developers = $developers->latest('updated_at')->get();
        $developers = $developers->map(function ($developer) {
            $developer->logo_image = $developer->logoImage();
            //unset($developer->logo); // Remove the original logo field from the result set
            return $developer;
        });
        //return $communities;
        return response()->json(['developers' => $developers]);
    }

    public function edit(developers $developer)
    {
        $developer['logoImage'] = $developer->logoImage();
        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($developer) {
                $query->where('subject_id', $developer->id)
                    ->where('subject_type', get_class($developer));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $developer['activities'] = $activities;
        return response()->json(['developer' => $developer]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255|unique:developers',
        ]);

        $logo = null;
        if ($request->hasFile('avatar')) {
            $logo = $request->file('avatar')->store('uploads/developers/images', 'public');
        }

        // Create the data
        $data = developers::create([
            'name' => $request->input('name'),
            'country_id' => '234',
            'logo' => $logo,
        ]);

        activity()
            ->performedOn($data)
            ->causedBy(auth()->user())
            ->withProperties('Developer created.')
            ->log('created');

        return response()->json(['message' => 'Developer created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:developers,name,' . $id,
        ]);

        // Find
        $data = developers::findOrFail($id);

        $originalValues = $data->getOriginal();

        $logo = $data->logo;
        if ($request->hasFile('avatar')) {
            // Store the uploaded file
            $logo = $request->file('avatar')->store('uploads/developers/images', 'public');
        }
        
        $data->update([
            'name' => $request->input('name'),
            'logo' => $logo,
        ]);

        // Get the updated values after updating
        $updatedValues = $data->getAttributes();
        unset($updatedValues['updated_at']);

        // Log the changes
        $logDetails = [];

        foreach ($updatedValues as $field => $newValue) {
            $oldValue = $originalValues[$field];
    
            // Check if the field has changed
            if ($oldValue != $newValue) {
                $logDetails[] = "$field: $oldValue to $newValue";
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($data)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Developer updated successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk delete.']);
        }

        try {
            foreach($itemIds as $itemId){
                $data = developers::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Developer is deleted through bulk action.')
                    ->log('updated');
            }

            developers::whereIn('id', $itemIds)->delete();

            return response()->json(['message' => 'Bulk delete successful']);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['message' => 'Error during bulk delete: ' . $e->getMessage()], 500);
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
            developers::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = developers::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Developer is restored through bulk action.')
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
                $data = developers::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Developer is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            developers::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = developers::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Developer is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = developers::whereIn('id', $itemIds)->update(['status' => 0]);
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
