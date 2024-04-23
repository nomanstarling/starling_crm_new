<?php

namespace App\Http\Controllers;

use App\Models\listing_portals;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class ListingPortalsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $firstDate = listing_portals::min('created_at');
        return view('admin.portals.index', compact('firstDate'));
    }

    public function getList(Request $request)
    {
        $portals = listing_portals::orderBy('name')->get();

        $portalsWithDetails = $portals->map(function ($portal) {
            return array_merge($portal->toArray(), [
                'logo_image' => $portal->logoImage(),
            ]);
        });
        return response()->json(['portals' => $portalsWithDetails]);
    }

    public function getPortals(Request $request)
    {
        $status = $request->query('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startCreatedDate = $request->query('startCreatedDate');
        $endCreatedDate = $request->query('endCreatedDate');

        $portals = listing_portals::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });

        if ($startDate && $endDate) {
            $portals->where(function ($query) use ($startDate, $endDate) {
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
            $portals->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        $portals = $portals->withCount('listings')->latest('updated_at')->get();

        $portalsWithDetails = $portals->map(function ($portal) {
            return array_merge($portal->toArray(), [
                'logo_image' => $portal->logoImage()
            ]);
        });
        //return $communities;
        return response()->json(['portals' => $portalsWithDetails]);
    }

    public function edit(listing_portals $portal)
    {
        $portal['logo_image'] = $portal->logoImage();
        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($portal) {
                $query->where('subject_id', $portal->id)
                    ->where('subject_type', get_class($portal));
            })
            ->orderBy('created_at', 'desc')->where('properties', '!=', null)->Where('properties', '!=', '[]')
            ->get();
        $portal['activities'] = $activities;
        return response()->json(['portal' => $portal]);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255|unique:listing_portals',
            'is_paid' => 'required|string|max:255',
        ]);
        
        $name = $request->input('name');
        $slug = $request->input('slug');
        
        if (is_null($slug)) {
            $slug = Str::slug($name, "-");
        }

        $logoPath = null;
        if ($request->hasFile('avatar')) {
            $logoPath = $request->file('avatar')->store('uploads/portals/images', 'public');
        }

        // Create the data
        $data = listing_portals::create([
            'name' => $name,
            'is_paid' => $request->input('is_paid'),
            'slug' => $slug,
            'logo' => $logoPath
        ]);

        activity()
            ->performedOn($data)
            ->causedBy(auth()->user())
            ->withProperties('Portal created.')
            ->log('created');

        return response()->json(['message' => 'Portal created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:listing_portals,name,' . $id,
            'is_paid' => 'required|string|max:255',
        ]);

        // Find
        $data = listing_portals::findOrFail($id);

        $originalValues = $data->getOriginal();

        $name = $request->input('name');
        $slug = $request->input('slug');
        
        $slug = Str::slug($name, "-");

        $logoPath = $data->logo;
        if ($request->hasFile('avatar')) {
            $logoPath = $request->file('avatar')->store('uploads/portals/images', 'public');
        }

        // Update
        $data->update([
            'name' => $name,
            'is_paid' => $request->input('is_paid'),
            'slug' => $slug,
            'logo' => $logoPath
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
        
        return response()->json(['message' => 'Portal updated successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk delete.']);
        }

        try {
            foreach($itemIds as $itemId){
                $data = listing_portals::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Portal is deleted through bulk action.')
                    ->log('updated');
            }

            listing_portals::whereIn('id', $itemIds)->delete();

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
            listing_portals::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = listing_portals::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Portal is restored through bulk action.')
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
                $data = listing_portals::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Portal is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            listing_portals::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = listing_portals::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Portal is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = listing_portals::whereIn('id', $itemIds)->update(['status' => 0]);
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
