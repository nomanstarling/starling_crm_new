<?php

namespace App\Http\Controllers;

use App\Models\listing_occupancies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class ListingOccupanciesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $firstDate = listing_occupancies::min('created_at');
        return view('admin.occupencies.index', compact('firstDate'));
    }
    
    public function getOccupancies(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startCreatedDate = $request->query('startCreatedDate');
        $endCreatedDate = $request->query('endCreatedDate');

        $listing_occupancies = listing_occupancies::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });

        if ($startDate && $endDate) {
            $listing_occupancies->where(function ($query) use ($startDate, $endDate) {
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
            $listing_occupancies->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        $listing_occupancies = $listing_occupancies->latest('updated_at')->get();
        //return $communities;
        return response()->json(['listing_occupancies' => $listing_occupancies]);
    }

    public function edit(listing_occupancies $occupancy)
    {
        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($occupancy) {
                $query->where('subject_id', $occupancy->id)
                    ->where('subject_type', get_class($occupancy));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $occupancy['activities'] = $activities;
        return response()->json(['occupancy' => $occupancy]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255|unique:listing_occupancies',
        ]);
        
        // Create the data
        $data = listing_occupancies::create([
            'name' => $request->input('name'),
        ]);

        activity()
            ->performedOn($data)
            ->causedBy(auth()->user())
            ->withProperties('Occupency created.')
            ->log('created');

        return response()->json(['message' => 'Occupency created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:listing_occupancies,name,' . $id,
        ]);

        // Find
        $data = listing_occupancies::findOrFail($id);

        $originalValues = $data->getOriginal();

        // Update
        $data->update([
            'name' => $request->input('name'),
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
        
        return response()->json(['message' => 'Occupency updated successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk delete.']);
        }

        try {
            foreach($itemIds as $itemId){
                $data = listing_occupancies::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Occupency is deleted through bulk action.')
                    ->log('updated');
            }

            listing_occupancies::whereIn('id', $itemIds)->delete();

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
            listing_occupancies::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = listing_occupancies::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Occupency is restored through bulk action.')
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
                $data = listing_occupancies::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Occupency is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            listing_occupancies::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = listing_occupancies::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Occupency is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = listing_occupancies::whereIn('id', $itemIds)->update(['status' => 0]);
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
