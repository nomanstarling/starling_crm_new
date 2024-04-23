<?php

namespace App\Http\Controllers;

use App\Models\cities;
use App\Models\sub_communities;
use App\Models\communities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubCommunitiesExport;

class SubCommunitiesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $firstDate = sub_communities::min('created_at');
        $cities = cities::where('country_id', '234')->orderBy('name')->get();
        return view('admin.sub_communities.index', compact('cities', 'firstDate'));
    }

    public function show(){

    }

    // public function getList(Request $request)
    // {
    //     $city_id = $request->input('city_id');
    //     if ($city_id) {
    //         $communities = communities::where('city_id', $city_id)->orderBy('name')->get();
    //     } else {
    //         $communities = communities::orderBy('name')->get();
    //     }

    //     return response()->json(['communities' => $communities]);
    // }
    
    public function getList(Request $request)
    {
        $city_id = $request->input('city_id');
        $community_id = $request->input('community_id');
        if ($city_id && $community_id) {
            $sub_communities = sub_communities::where('city_id', $city_id)->where('community_id', $community_id)->orderBy('name')->get();
        } else {
            $sub_communities = sub_communities::orderBy('name')->get();
        }

        return response()->json(['sub_communities' => $sub_communities]);
    }

    public function getSubCommunities(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startCreatedDate = $request->query('startCreatedDate');
        $endCreatedDate = $request->query('endCreatedDate');

        $sub_communities = sub_communities::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });
        
        if ($startDate && $endDate) {
            $sub_communities->where(function ($query) use ($startDate, $endDate) {
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
            $sub_communities->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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
            $sub_communities->whereHas('country', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('country_name') . '%');
            });
        }

        if ($request->query('city_name') != null) {
            $sub_communities->whereHas('city', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('city_name') . '%');
            });
        }

        if ($request->query('community_name') != null) {
            $sub_communities->whereHas('community', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('community_name') . '%');
            });
        }

        if ($request->query('sub_community_name') != null) {
            $sub_communities->where('name', 'like', '%' . $request->query('sub_community_name'). '%');
        }
        
        // if ($request->query('searchSaleCount') != null) {
        //     //$sub_communities->where('sales_listing_count', $request->query('searchSaleCount'));
        //     $targetSaleCount = (int) $request->query('searchSaleCount');

        //     $sub_communities->whereHas('getSalesListings', function ($query) use ($targetSaleCount) {
        //         $query->havingRaw('COUNT(*)', '=', $targetSaleCount);
        //     });
        // }

        // if ($request->query('searchArchiveCount') != null) {
        //     $sub_communities->where('archive_listing_count', $request->query('searchArchiveCount'));
        // }

        // if ($request->query('searchRentCount') != null) {
        //     $sub_communities->where('rent_listing_count', $request->query('searchRentCount'));
        // }

        $perPage = $request->input('length', 20); // Number of records per page
        $totalRecords = $sub_communities->count(); // Fetch the total count

        $sub_communities = $sub_communities->latest('updated_at')
        ->with('country', 'city', 'community') // Eager load country and city relationships
        ->skip(($request->input('start', 0) / $perPage) * $perPage)
        ->take($perPage)
        ->get();
        
        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $sub_communities,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);
    }

    public function edit(sub_communities $sub_community)
    {
        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($sub_community) {
                $query->where('subject_id', $sub_community->id)
                    ->where('subject_type', get_class($sub_community));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $sub_community['activities'] = $activities;
        return response()->json(['sub_community' => $sub_community]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'city_id' => 'required|string|max:255',
            'community_id' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:sub_communities', // Add unique rule
        ]);
        
        // Create the data
        $sub_community = sub_communities::create([
            'name' => $request->input('name'),
            'country_id' => '234',
            'city_id' => $request->input('city_id'),
            'community_id' => $request->input('community_id'),
        ]);

        activity()
            ->performedOn($sub_community)
            ->causedBy(auth()->user())
            ->withProperties('Sub Community created.')
            ->log('created');

        return response()->json(['message' => 'Sub Community created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'city_id' => 'required|string|max:255',
            'community_id' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:sub_communities,name,' . $id, // Add unique rule
        ]);

        // Find the Community
        $sub_community = sub_communities::findOrFail($id);

        $originalValues = $sub_community->getOriginal();

        // Update the Community
        $sub_community->update([
            'name' => $request->input('name'),
            'city_id' => $request->input('city_id'),
            'community_id' => $request->input('community_id'),
        ]);

        // Get the updated values after updating
        $updatedValues = $sub_community->getAttributes();
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
                }
                if ($field == 'community_id') {
                    $oldCommunityName = $oldValue ? communities::find($oldValue)->name : 'empty';
                    $newCommunityName = $newValue ? communities::find($newValue)->name : 'empty';
    
                    $logDetails[] = "community: $oldCommunityName to $newCommunityName";
                } else {
                    $logDetails[] = "$field: $oldValue to $newValue";
                }
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($sub_community)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Sub Community updated successfully.']);
    }

    // public function destroy(sub_communities $sub_community)
    // {
    //     activity()
    //         ->performedOn($sub_community)
    //         ->causedBy(auth()->user())
    //         ->withProperties('Sub Community is deleted.')
    //         ->log('updated');

    //     // Delete the data
    //     $sub_community->delete();
    //     //notify()->success('User deleted successfully', 'Success');
    //     return response()->json(['message' => 'Sub Community deleted successfully']);
    // }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk delete.']);
        }

        try {

            $subRecordCheck = sub_communities::whereIn('id', $itemIds)->whereHas('towers')->pluck('name');

            if ($subRecordCheck->isNotEmpty()) {
                return response()->json(['error' => "Cannot delete. The following sub-communities have associated towers: " . $subRecordCheck->implode(', ')]);
            }

            foreach($itemIds as $itemId){
                $data = sub_communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Community is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            sub_communities::whereIn('id', $itemIds)->delete();

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
            sub_communities::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = sub_communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Community is restored through bulk action.')
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
                $data = sub_communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Community is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            sub_communities::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = sub_communities::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Sub Community is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = sub_communities::whereIn('id', $itemIds)->update(['status' => 0]);
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

    public function export(Request $request)
    {
        $itemIds = $request->input('item_ids');
        $data = sub_communities::with('country', 'city', 'community');
        // Check if any user IDs are provided
        if (!empty($itemIds)) {
            $data = $data->whereIn('id', $itemIds);
        }
        else{
            $status = $request->input('status', 1);
            $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

            $startDate = $request->filters['startDate'];
            $endDate = $request->filters['endDate'];

            $startCreatedDate = $request->filters['startCreatedDate'];
            $endCreatedDate = $request->filters['endCreatedDate'];

            $data = $data->when($status !== null, function ($query) use ($status) {
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
                $data->whereHas('country', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->filters['country_name'] . '%');
                });
            }

            if ($request->filters['city_name'] != null) {
                $data->whereHas('city', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->filters['city_name'] . '%');
                });
            }

            if ($request->filters['community_name'] != null) {
                $data->whereHas('community', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->filters['community_name'] . '%');
                });
            }

            if ($request->filters['sub_community_name'] != null) {
                $data->where('name', 'like', '%' . $request->filters['sub_community_name'] . '%');
            }

            if ($request->filters['sale_count'] != null) {
                $data->where('sales_listing_count', $request->filters['sale_count']);
            }

            if ($request->filters['rent_count'] != null) {
                $data->where('rent_listing_count', $request->filters['rent_count']);
            }

            if ($request->filters['archive_count']) {
                $data->where('archive_listing_count', $request->filters['archive_count']);
            }
        }
        $data = $data->latest('updated_at')->get();

        // Export the data using the Excel facade
        $filename = 'sub_communities_export_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        $export = new SubCommunitiesExport($data); // Use your Export class

        // Generate the Excel file
        $file = Excel::download($export, $filename)->getFile();

        // Get the file content
        $fileContent = file_get_contents($file);

        // Encode file content to base64
        $base64File = base64_encode($fileContent);

        // Return JSON response with file and filename
        return response()->json(['file' => $base64File, 'filename' => $filename]);
    }
}
