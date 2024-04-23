<?php

namespace App\Http\Controllers;

use App\Models\communities;
use App\Models\countries;
use App\Models\cities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CommunitiesExport;


class CommunitiesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $firstDate = communities::min('created_at');
        $cities = cities::where('country_id', '234')->orderBy('name')->get();
        return view('admin.communities.index', compact('cities', 'firstDate'));
    }

    public function show(){

    }

    public function getList(Request $request)
    {
        $city_id = $request->input('city_id');
        if ($city_id) {
            $communities = communities::where('city_id', $city_id)->orderBy('name')->get();
        } else {
            $communities = communities::orderBy('name')->get();
        }

        return response()->json(['communities' => $communities]);
    }
    
    public function getCommunities(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startCreatedDate = $request->query('startCreatedDate');
        $endCreatedDate = $request->query('endCreatedDate');

        $communities = communities::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });
        if ($startDate && $endDate) {
            $communities->where(function ($query) use ($startDate, $endDate) {
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
            $communities->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        if ($request->query('country_name') != null) {
            $communities->whereHas('country', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('country_name') . '%');
            });
        }

        if ($request->query('city_name') != null) {
            $communities->whereHas('city', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('city_name') . '%');
            });
        }

        if ($request->query('community_name') != null) {
            $communities->where('name', 'like', '%' . $request->query('community_name'). '%');
        }
        
        // $communities = $communities->latest('updated_at')
        //     ->with('country', 'city') // Eager load country and city relationships
        //     ->get();
        $perPage = $request->input('length', 20); // Number of records per page
        $totalRecords = $communities->count(); // Fetch the total count

        $communities = $communities->latest('updated_at')
        ->with('country', 'city') // Eager load country and city relationships
        ->skip(($request->input('start', 0) / $perPage) * $perPage)
        ->take($perPage)
        ->get();

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $communities,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);
        // return response()->json(['communities' => $communities]);
    }

    public function export(Request $request)
    {
        $itemIds = $request->input('item_ids');
        $communities = communities::with('country', 'city');
        // Check if any user IDs are provided
        if (!empty($itemIds)) {
            $communities = $communities->whereIn('id', $itemIds);
        }
        else{
            $status = $request->input('status', 1);
            $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

            $startDate = $request->filters['startDate'];
            $endDate = $request->filters['endDate'];

            $startCreatedDate = $request->filters['startCreatedDate'];
            $endCreatedDate = $request->filters['endCreatedDate'];

            $communities = $communities->when($status !== null, function ($query) use ($status) {
                if ($status === 'deleted') {
                    return $query->onlyTrashed();
                } elseif ($status == 1 || $status == 0) {
                    return $query->where('status', $status);
                }
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                //return $query->whereBetween('updated_at', [$startDate, $endDate]);
                return $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->when($startCreatedDate && $endCreatedDate, function ($query) use ($startCreatedDate, $endCreatedDate) {
                //return $query->whereBetween('created_at', [$startCreatedDate, $endCreatedDate]);
                return $query->whereBetween('created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
            });

            // Additional filtering based on specific search values
            if ($request->filters['country_name'] != null) {
                $communities->whereHas('country', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->filters['country_name'] . '%');
                });
            }

            if ($request->filters['city_name'] != null) {
                $communities->whereHas('city', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->filters['city_name'] . '%');
                });
            }

            if ($request->filters['community_name'] != null) {
                $communities->where('name', 'like', '%' . $request->filters['community_name'] . '%');
            }

            if ($request->filters['sale_count'] != null) {
                $communities->where('sales_listing_count', $request->filters['sale_count']);
            }

            if ($request->filters['rent_count'] != null) {
                $communities->where('rent_listing_count', $request->filters['rent_count']);
            }

            if ($request->filters['archive_count']) {
                $communities->where('archive_listing_count', $request->filters['archive_count']);
            }
        }
        $communities = $communities->latest('updated_at')->get();

        // Export the data using the Excel facade
        $filename = 'communities_export_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        $export = new CommunitiesExport($communities); // Use your Export class

        // Generate the Excel file
        $file = Excel::download($export, $filename)->getFile();

        // Get the file content
        $fileContent = file_get_contents($file);

        // Encode file content to base64
        $base64File = base64_encode($fileContent);

        // Return JSON response with file and filename
        return response()->json(['file' => $base64File, 'filename' => $filename]);
    }

    public function edit(communities $community)
    {
        // return $community; // Remove or comment out this line
        //$community = $community->load('activities');

        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($community) {
                $query->where('subject_id', $community->id)
                    ->where('subject_type', get_class($community));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $community['activities'] = $activities;
        return response()->json(['community' => $community]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            // 'country_id' => 'required|string|max:255',
            'city_id' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:communities', // Add unique rule
        ]);
        
        // Create the data
        $community = communities::create([
            'name' => $request->input('name'),
            'country_id' => '234',
            'city_id' => $request->input('city_id'),
        ]);

        activity()
            ->performedOn($community)
            ->causedBy(auth()->user())
            ->withProperties('Community created.')
            ->log('created');

        return response()->json(['message' => 'Community created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            // 'country_id' => 'required|string|max:255',
            'city_id' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:communities,name,' . $id,
        ]);

        // Find the Community
        $community = communities::findOrFail($id);

        $originalValues = $community->getOriginal();

        // Update the Community
        $community->update([
            'name' => $request->input('name'),
            'city_id' => $request->input('city_id'),
        ]);

        // Get the updated values after updating
        $updatedValues = $community->getAttributes();
        unset($updatedValues['updated_at']);

        // Log the changes
        $logDetails = [];

        foreach ($updatedValues as $field => $newValue) {
            $oldValue = $originalValues[$field];
    
            // Check if the field has changed
            if ($oldValue != $newValue) {
                // If the changed field is 'city_id', handle null values
                if ($field == 'city_id') {
                    $oldCityName = $oldValue ? cities::find($oldValue)->name : 'empty';
                    $newCityName = $newValue ? cities::find($newValue)->name : 'empty';
    
                    $logDetails[] = "city: $oldCityName to $newCityName";
                } else {
                    $logDetails[] = "$field: $oldValue to $newValue";
                }
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($community)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Community updated successfully.']);
    }

    // public function destroy(communities $community)
    // {
    //     activity()
    //         ->performedOn($community)
    //         ->causedBy(auth()->user())
    //         ->withProperties('Community is deleted.')
    //         ->log('updated');

    //     // Delete the data
    //     $community->delete();
    //     //notify()->success('User deleted successfully', 'Success');
    //     return response()->json(['message' => 'Community deleted successfully']);
    // }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk delete.']);
        }

        try {
            $subRecordCheck = communities::whereIn('id', $itemIds)->whereHas('sub_communities')->pluck('name');

            if ($subRecordCheck->isNotEmpty()) {
                return response()->json(['error' => "Cannot delete. The following communities have associated sub-communities: " . $subRecordCheck->implode(', ')]);
            }

            foreach($itemIds as $itemId){
                $data = communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Community is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            communities::whereIn('id', $itemIds)->delete();

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
            communities::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Community is restored through bulk action.')
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
                $data = communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Community is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            communities::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Community is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = communities::whereIn('id', $itemIds)->update(['status' => 0]);
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
