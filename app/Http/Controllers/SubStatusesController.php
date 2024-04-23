<?php

namespace App\Http\Controllers;

use App\Models\SubStatuses;
use App\Models\Statuses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class SubStatusesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $statuses = Statuses::get();
        $firstDate = SubStatuses::min('created_at');
        return view('admin.sub_statuses.index', compact('firstDate', 'statuses'));
    }
    
    public function getCrmSubStatuses(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startCreatedDate = $request->query('startCreatedDate');
        $endCreatedDate = $request->query('endCreatedDate');

        $sub_statuses = SubStatuses::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });

        if ($startDate && $endDate) {
            $sub_statuses->where(function ($query) use ($startDate, $endDate) {
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
            $sub_statuses->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        $sub_statuses = $sub_statuses->with('crmStatus')->latest('updated_at')->get();
        //return $communities;
        return response()->json(['sub_statuses' => $sub_statuses]);
    }

    public function getList(Request $request)
    {
        $status_id = $request->input('status_id');
        $sub_statuses = SubStatuses::where('status_id', $status_id)->orderBy('name')->get();

        return response()->json(['sub_statuses' => $sub_statuses]);
    }

    public function edit(SubStatuses $sub_status)
    {
        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($sub_status) {
                $query->where('subject_id', $sub_status->id)
                    ->where('subject_type', get_class($sub_status));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $sub_status['activities'] = $activities;
        return response()->json(['sub_status' => $sub_status]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        // $request->validate([
        //     'name' => 'required|string|max:255|unique:sub_sources',
        //     'source_id' => 'required|string|max:255',
        // ]);

        $request->validate([
            'name' => 'required|string|max:255|unique:sub_statuses,name,NULL,id,status_id,' . $request->input('status_id'),
            'status_id' => 'required|string|max:255',
        ]);        
        
        // Create the data
        $data = SubStatuses::create([
            'name' => $request->input('name'),
            'badge' => $request->input('badge'),
            'status_id' => $request->input('status_id'),
        ]);

        activity()
            ->performedOn($data)
            ->causedBy(auth()->user())
            ->withProperties('CRM Sub Status created.')
            ->log('created');

        return response()->json(['message' => 'CRM Sub Status created successfully']);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:sub_statuses,name,' . $id . ',id,status_id,' . $request->input('status_id'),
            'status_id' => 'required|string|max:255',
        ]);        

        // Find
        $data = SubStatuses::findOrFail($id);

        $originalValues = $data->getOriginal();

        // Update
        $data->update([
            'name' => $request->input('name'),
            'badge' => $request->input('badge'),
            'status_id' => $request->input('status_id'),
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
        
        return response()->json(['message' => 'Sub Status updated successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk delete.']);
        }

        try {
            foreach($itemIds as $itemId){
                $data = SubStatuses::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Status is deleted through bulk action.')
                    ->log('updated');
            }

            SubStatuses::whereIn('id', $itemIds)->delete();

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
            SubStatuses::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = SubStatuses::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Status is restored through bulk action.')
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
                $data = SubStatuses::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Status is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            SubStatuses::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = SubStatuses::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Status is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = SubStatuses::whereIn('id', $itemIds)->update(['status' => 0]);
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
