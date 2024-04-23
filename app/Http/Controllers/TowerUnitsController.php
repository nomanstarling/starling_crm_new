<?php

namespace App\Http\Controllers;

use App\Models\tower_units;
use App\Models\towers;
use App\Models\cities;
use App\Models\sub_communities;
use App\Models\communities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TowerUnitsExport;

class TowerUnitsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $firstDate = tower_units::min('created_at');
        $cities = cities::where('country_id', '234')->orderBy('name')->get();
        return view('admin.tower_units.index', compact('cities', 'firstDate'));
    }

    public function show(){

    }

    // public function getList(Request $request)
    // {
    //     $city_id = $request->input('city_id');
    //     $community_id = $request->input('community_id');
    //     $sub_community_id = $request->input('sub_community_id');
    //     if ($city_id && $community_id && $sub_community_id) {
    //         $towers = towers::where('city_id', $city_id)->where('community_id', $community_id)->where('sub_community_id', $sub_community_id)->orderBy('name')->get();
    //     } else {
    //         $towers = towers::orderBy('name')->get();
    //     }

    //     return response()->json(['towers' => $towers]);
    // }
    
    public function getTowerUnits(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startCreatedDate = $request->query('startCreatedDate');
        $endCreatedDate = $request->query('endCreatedDate');

        $tower_units = tower_units::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });

        if ($startDate && $endDate) {
            $tower_units->where(function ($query) use ($startDate, $endDate) {
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
            $tower_units->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        $tower_units = $tower_units->latest('updated_at')
            ->with('country', 'city', 'community', 'sub_community', 'tower') // Eager load country and city relationships
            ->get();
        //return $communities;
        return response()->json(['tower_units' => $tower_units]);
    }

    public function edit(tower_units $unit)
    {
        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($unit) {
                $query->where('subject_id', $unit->id)
                    ->where('subject_type', get_class($unit));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $unit['activities'] = $activities;
        return response()->json(['tower_unit' => $unit]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'city_id' => 'required|string|max:255',
            'community_id' => 'required|string|max:255',
            'sub_community_id' => 'required|string|max:255',
            'tower_id' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:tower_units', // Add unique rule
        ]);
        
        // Create the data
        $tower_unit = tower_units::create([
            'name' => $request->input('name'),
            'country_id' => '234',
            'city_id' => $request->input('city_id'),
            'community_id' => $request->input('community_id'),
            'sub_community_id' => $request->input('sub_community_id'),
            'tower_id' => $request->input('tower_id'),
        ]);

        activity()
            ->performedOn($tower_unit)
            ->causedBy(auth()->user())
            ->withProperties('Tower created.')
            ->log('created');

        return response()->json(['message' => 'Tower Unit created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'city_id' => 'required|string|max:255',
            'community_id' => 'required|string|max:255',
            'sub_community_id' => 'required|string|max:255',
            'tower_id' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:tower_units,name,' . $id, // Add unique rule
        ]);

        // Find the Community
        $tower_unit = tower_units::findOrFail($id);

        $originalValues = $tower_unit->getOriginal();

        // Update the Community
        $tower_unit->update([
            'name' => $request->input('name'),
            'city_id' => $request->input('city_id'),
            'community_id' => $request->input('community_id'),
            'sub_community_id' => $request->input('sub_community_id'),
            'tower_id' => $request->input('tower_id'),
        ]);

        // Get the updated values after updating
        $updatedValues = $tower_unit->getAttributes();
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
                }
                if ($field == 'sub_community_id') {
                    $oldSubCommunityName = $oldValue ? sub_communities::find($oldValue)->name : 'empty';
                    $newSubCommunityName = $newValue ? sub_communities::find($newValue)->name : 'empty';
    
                    $logDetails[] = "sub community: $oldSubCommunityName to $newSubCommunityName";
                }
                if ($field == 'tower_id') {
                    $oldTowerName = $oldValue ? towers::find($oldValue)->name : 'empty';
                    $newTowerName = $newValue ? towers::find($newValue)->name : 'empty';
    
                    $logDetails[] = "tower: $oldTowerName to $newTowerName";
                }
                else {
                    $logDetails[] = "$field: $oldValue to $newValue";
                }
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($tower_unit)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Tower updated successfully.']);
    }

    // public function destroy(towers $tower)
    // {
    //     activity()
    //         ->performedOn($tower)
    //         ->causedBy(auth()->user())
    //         ->withProperties('Tower is deleted.')
    //         ->log('updated');

    //     // Delete the data
    //     $tower->delete();
    //     //notify()->success('User deleted successfully', 'Success');
    //     return response()->json(['message' => 'Tower deleted successfully']);
    // }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk delete.']);
        }

        try {
            foreach($itemIds as $itemId){
                $data = tower_units::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Tower Unit is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            tower_units::whereIn('id', $itemIds)->delete();

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
            tower_units::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = tower_units::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Tower Unit is restored through bulk action.')
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
                $data = tower_units::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Tower Unit is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            tower_units::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = tower_units::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Tower Unit is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = tower_units::whereIn('id', $itemIds)->update(['status' => 0]);
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
        $data = tower_units::with('country', 'city', 'community', 'sub_community', 'tower');
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
                $data->whereHas('sub_community', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->filters['sub_community_name'] . '%');
                });
            }

            if ($request->filters['tower_name'] != null) {
                $data->whereHas('tower', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->filters['tower_name'] . '%');
                });
            }

            if ($request->filters['unit_name'] != null) {
                $data->where('name', 'like', '%' . $request->filters['unit_name'] . '%');
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
        $filename = 'towers_units_export_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        $export = new TowerUnitsExport($data); // Use your Export class

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
