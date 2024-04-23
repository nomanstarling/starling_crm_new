<?php

namespace App\Http\Controllers;

use App\Models\owners;
use App\Models\countries;
use App\Models\cities;
use App\Models\Sources;
use App\Models\Notes;
use App\Models\media_gallery;
use App\Models\SubSources;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OwnersExport;
use Spatie\Valuestore\Valuestore;

class OwnersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->settings = Valuestore::make(config('settings.path'));
        $this->shortName = $this->settings->get('short_name');
    }
    
    public function index()
    {
        $this->authorize('owners_view');
        $firstDate = owners::min('created_at');
        $sources = Sources::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $countries = countries::orderBy('name')->get();
        $cities = cities::orderBy('name')->get();
        return view('admin.owners.index', compact('sources', 'firstDate', 'users', 'countries', 'cities'));
    }

    // public function getList(Request $request)
    // {
    //     $owners = owners::select('id', 'name', 'email', 'phone')->orderBy('name')->take(100)->get();

    //     // $owners = $owners->map(function ($owner) {
    //     //     // return array_merge($owner->toArray(), [
    //     //     //     'profile_image_url' => $owner->profileImage(),
    //     //     // ]);
    //     // });

    //     return response()->json(['owners' => $owners]);
    // }

    public function getList(Request $request)
    {
        $searchTerm = $request->input('q');
        $searchTermId = $request->input('id');
        $query = owners::select('refno', 'id', 'name', 'email', 'phone')->orderBy('name');

        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%')
                ->orWhere('refno', 'like', '%' . $searchTerm . '%')
                ->orWhere('phone', 'like', '%' . $searchTerm . '%');
        }
        if($searchTermId){
            $query->where('id', $searchTermId);
        }

        $owners = $query->limit(100)->get();
        return response()->json(['results' => $owners]);
    }

    
    public function getOwners(Request $request)
    {
        $status = $request->input('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $startCreatedDate = $request->input('startCreatedDate');
        $endCreatedDate = $request->input('endCreatedDate');

        $owners = owners::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });
        if ($startDate && $endDate) {
            $owners->where(function ($query) use ($startDate, $endDate) {
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
            $owners->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        if ($request->input('source_name') != null) {
            $owners->whereHas('source', function ($query) use ($request) {
                $query->where('name', $request->input('source_name'));
            });
        }

        if ($request->input('sub_source_name') != null) {
            $owners->whereHas('sub_source', function ($query) use ($request) {
                $query->where('name', $request->input('sub_source_name'));
            });
        }

        if ($request->input('created_by_name') != null) {
            $owners->whereHas('created_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('created_by_name'));
            });
        }

        if ($request->input('updated_by_name') != null) {
            $owners->whereHas('updated_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('updated_by_name'));
            });
        }

        if ($request->input('owner_name') != null) {
            $owners->where('name', 'like', '%' . $request->input('owner_name') . '%')
                 ->orWhere('email', 'like', '%' . $request->input('owner_name') . '%')
                 ->orWhere('phone', 'like', '%' . $request->input('owner_name') . '%');
        }            
        
        if ($request->input('refno') != null) {
            $owners->where('refno', 'like', '%' . $request->input('refno') . '%');
        }

        if ($request->input('whatsapp') != null) {
            $owners->where('whatsapp', 'like', '%' . $request->input('whatsapp') . '%');
        }

        $totalRecords = $owners->count(); // Fetch the total count
        $perPage = $request->input('length', 10); // Number of records per page
        
        $owners = $owners->latest('updated_at')
            ->with('notes', 'documents', 'updated_by_user', 'created_by_user', 'source', 'sub_source')
            ->skip(($request->input('start', 0) / $perPage) * $perPage)
            ->take($perPage)
            ->get();

        $owners = $owners->map(function ($owner) {
            return array_merge($owner->toArray(), [
                'profile_image_url' => $owner->profileImage(),
            ]);
        });

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $owners,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);

        ///return response()->json(['owners' => $owners]);
    }

    public function searchRefno(Request $request)
    {
        $refno = $request->input('refno');
        $orderByColumn = 'updated_at';

        // Use your model to search for the record based on refno
        $matchingRecord = owners::where('refno', $refno)->first();

        if ($matchingRecord) {
            $position = owners::where($orderByColumn, '>', $matchingRecord->$orderByColumn)
                ->orWhere(function ($query) use ($matchingRecord, $orderByColumn) {
                    $query->where($orderByColumn, $matchingRecord->$orderByColumn)
                        ->where('id', '<', $matchingRecord->id);
                })
                ->count();
            // Calculate the page number of the matching record
            $perPage = $request->query('length', 10);
            $pageNumber = ceil(($position + 1) / $perPage) - 1;

            return response()->json([
                'record' => $matchingRecord,
                'pageNumber' => $pageNumber,
            ]);
        } else {
            // No record found
            return response()->json(null);
        }
    }

    public function export(Request $request)
    {
        $itemIds = $request->input('item_ids');
        $owners = owners::with('notes', 'documents', 'source', 'sub_source');
        // Check if any user IDs are provided
        if (!empty($itemIds)) {
            $owners = $owners->whereIn('id', $itemIds);
        }
        else{
            $status = $request->input('status', 1);
            $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

            $startDate = $request->filters['startDate'];
            $endDate = $request->filters['endDate'];

            $startCreatedDate = $request->filters['startCreatedDate'];
            $endCreatedDate = $request->filters['endCreatedDate'];

            $owners = $owners->when($status !== null, function ($query) use ($status) {
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

            if ($request->filters['source_name'] != null) {
                $owners->whereHas('source', function ($query) use ($request) {
                    $query->where('name', $request->filters['source_name']);
                });
            }

            if ($request->filters['sub_source_name'] != null) {
                $owners->whereHas('sub_source', function ($query) use ($request) {
                    $query->where('name', $request->filters['sub_source_name']);
                });
            }

            if ($request->filters['created_by_name'] != null) {
                $owners->whereHas('created_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['created_by_name']);
                });
            }

            if ($request->filters['updated_by_name'] != null) {
                $owners->whereHas('updated_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['updated_by_name']);
                });
            }

            if ($request->filters['owner_name'] != null) {
                $owners->where('name', 'like', '%' . $request->filters['owner_name'] . '%')
                     ->orWhere('email', 'like', '%' . $request->filters['owner_name'] . '%')
                     ->orWhere('phone', 'like', '%' . $request->filters['owner_name'] . '%');
            }            
            
            if ($request->filters['refno'] != null) {
                $owners->where('refno', 'like', '%' . $request->filters['refno'] . '%');
            }

            if ($request->filters['whatsapp'] != null) {
                $owners->where('whatsapp', 'like', '%' . $request->filters['whatsapp'] . '%');
            }
        }
        $owners = $owners->latest('updated_at')->get();

        // Export the data using the Excel facade
        $filename = 'owners_export_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        $export = new OwnersExport($owners); // Use your Export class

        // Generate the Excel file
        $file = Excel::download($export, $filename)->getFile();

        // Get the file content
        $fileContent = file_get_contents($file);

        // Encode file content to base64
        $base64File = base64_encode($fileContent);

        // Return JSON response with file and filename
        return response()->json(['file' => $base64File, 'filename' => $filename]);
    }

    public function edit(owners $owner)
    {
        // Eager load documents
        $loadedOwner = $owner->load('documents', 'notes.created_by_user', 'created_by_user', 'updated_by_user');
        $loadedOwner['profile_image'] = $loadedOwner->profileImage();

        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($loadedOwner) {
                $query->where('subject_id', $loadedOwner->id)
                    ->where('subject_type', get_class($loadedOwner));
            })
            ->orderBy('created_at', 'desc')
            ->where('properties', '!=', null)
            ->where('properties', '!=', '[]')
            ->get();

        // Manually add file URL to each document
        $loadedOwner->documents->each(function ($document) {
            $document->file_url = asset('public/storage/' . $document->path);
        });

        $loadedOwner['activities'] = $activities;

        return response()->json(['owner' => $loadedOwner]);
    }

    private function getNextRefNo(){
        //$latestOwner = owners::latest()->first();
        $latestOwner = owners::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestOwner) {
            // Extract the numeric part of the existing refno
            $latestRefNo = $latestOwner->refno;
            
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            
            // Increment the numeric part
            $nextNumericPart = $numericPart + 1;
            // Generate the new refno
            $newRefNo = $this->shortName . '-O-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing records, start from 001
            $newRefNo = $this->shortName . '-O-001';
        }
        return $newRefNo;
    }

    public function storeAjax(Request $request){
        try {
            // Validate the request data as per your requirements
            $request->validate([
                'name' => 'required|string|max:255|unique:owners',
                'phone' => 'required|string|max:255|unique:owners',
            ]);

            $refno = $this->getNextRefNo();

            $dob = null;
            if($request->input('dob') != null){
                $dob = Carbon::parse($request->input('dob'))->format('Y-m-d');
            }
            // Create the data
            $owner = owners::create([
                'refno' => $refno,
                'title' => $request->input('title'),
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'whatsapp' => $request->input('whatsapp'),
                'email' => $request->input('email'),
                'dob' => $dob,
                'address' => $request->input('address'),
                'created_by' => auth()->user()->id,
            ]);

            activity()
                ->performedOn($owner)
                ->causedBy(auth()->user())
                ->withProperties('Owner created from listing page.')
                ->log('created');

            // Return a JSON response with the owner ID and success message
            return response()->json([
                'ownerId' => $owner->id,
                'success' => 'Owner added successfully.',
            ]);

        } catch (\Exception $e) {
            // Return an error response in case of an exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255|unique:owners',
            'phone' => 'required|string|max:255|unique:owners',
            'email' => 'required|string|max:255|unique:owners',
        ]);

        $photo = null;
        if ($request->hasFile('avatar')) {
            $photo = $request->file('avatar')->store('uploads/owners/images', 'public');
        }
        $dob = null;
        if($request->input('dob') != null){
            $dob = Carbon::parse($request->input('dob'))->format('Y-m-d');
        }

        $refnoType = $this->shortName.'-O-';
        
        $refno = $this->getNextRefNo();
        // Create the data
        $owner = owners::create([
            'refno' => $refno,
            'title' => $request->input('title'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'whatsapp' => $request->input('whatsapp'),
            'email' => $request->input('email'),
            'dob' => $dob,
            'company' => $request->input('company'),
            'designation' => $request->input('designation'),
            'religion' => $request->input('religion'),
            'website' => $request->input('website'),
            'source_id' => $request->input('source_id'),
            'sub_source_id' => $request->input('sub_source_id'),
            'photo' => $photo,
            'country_id' => $request->input('country_id'),
            'city_id' => $request->input('city_id'),
            'address' => $request->input('address'),
            'created_by' => auth()->user()->id,
        ]);

        if ($request->hasFile('file')) {
            $files = $request->file('file');
    
            foreach ($files as $index => $file) {
    
                $filename = $file->store('uploads/owners/'.$refno.'/documents', 'public');
                $fileType = $file->getClientOriginalExtension();
                $originalName = $file->getClientOriginalName();
                $size = $file->getSize();
    
                media_gallery::create([
                    'object' => 'document',
                    'alt' => $request->input('document_name')[$index],
                    'object_id' => $owner->id,
                    'object_type' => owners::class,
                    'path' => $filename,
                    'file_name' => $originalName,
                    'file_type' => $fileType,
                    'status' => true,
                    'featured' => false,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);
            }
        }

        if ($request->filled('note_values')) {
            $noteValues = $request->input('note_values');
    
            if (is_array($noteValues) && count($noteValues) > 0) {
                foreach ($noteValues as $note) {
                    Notes::create([
                        'object' => 'Notes',
                        'object_id' => $owner->id,
                        'object_type' => owners::class,
                        'note' => $note,
                        'status' => true,
                        'created_by' => auth()->user()->id,
                        'updated_by' => auth()->user()->id,
                    ]);
                }
            }
        }

        activity()
            ->performedOn($owner)
            ->causedBy(auth()->user())
            ->withProperties('Owner created.')
            ->log('created');

        return response()->json(['message' => 'Owner created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:owners,name,'.$id,
            'phone' => 'required|string|max:255|unique:owners,phone,'.$id,
            'email' => 'required|string|max:255|unique:owners,email,'.$id,
        ]);

        // Find the Owner
        $owner = owners::findOrFail($id);

        // Retrieve the existing documents
        $existingDocuments = $owner->documents;

        $photo = $owner->photo;;
        if ($request->hasFile('avatar')) {
            $photo = $request->file('avatar')->store('uploads/owners/images', 'public');
        }

        $originalValues = $owner->getOriginal();

        $dob = $owner->dob;
        if($request->input('dob') != null){
            $dob = Carbon::parse($request->input('dob'))->format('Y-m-d');
        }

        // Update the Owner
        $owner->update([
            'title' => $request->input('title'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'whatsapp' => $request->input('whatsapp'),
            'email' => $request->input('email'),
            'dob' => $dob,
            'company' => $request->input('company'),
            'designation' => $request->input('designation'),
            'religion' => $request->input('religion'),
            'website' => $request->input('website'),
            'source_id' => $request->input('source_id'),
            'sub_source_id' => $request->input('sub_source_id'),
            'photo' => $photo,
            'country_id' => $request->input('country_id'),
            'city_id' => $request->input('city_id'),
            'address' => $request->input('address'),
            'updated_by' => auth()->user()->id,
        ]);
        // Sync the documents
        if ($request->hasFile('file')) {
            $files = $request->file('file');
    
            foreach ($files as $index => $file) {
    
                $filename = $file->store('uploads/owners/documents', 'public');
                $fileType = $file->getClientOriginalExtension();
                $originalName = $file->getClientOriginalName();
                $size = $file->getSize();

                $document_note = new media_gallery;
                $document_note->object = 'document';
                $document_note->alt = $request->input('document_name')[$index];
                $document_note->object_id = $owner->id;
                $document_note->object_type = owners::class;
                $document_note->path = $filename;
                $document_note->file_name = $originalName;
                $document_note->file_type = $fileType;
                $document_note->status = true;
                $document_note->featured = false;
                $document_note->created_by = auth()->user()->id;
                $document_note->updated_by = auth()->user()->id;
                $document_note->save();
            }
        }

        // Synchronize Notes
        $noteValues = $request->input('note_values', []);

        // Delete all existing notes for this owner
        $owner->notes()->forceDelete();
        // Save the received notes
        foreach ($noteValues as $noteValue) {
            $creat_note = new Notes;
            $creat_note->object = 'Notes';
            $creat_note->object_id = $owner->id;
            $creat_note->object_type = owners::class;
            $creat_note->note = $noteValue;
            $creat_note->status = true;
            $creat_note->created_by = auth()->user()->id;
            $creat_note->updated_by = auth()->user()->id;
            $creat_note->save();
        }

        // Update document names in media_gallery
        if ($request->has('document_id') && $request->has('document_names')) {
            $documentIds = $request->input('document_id');
            $documentNames = $request->input('document_names');

            foreach ($documentIds as $index => $documentId) {
                // Find the corresponding media_gallery record by document_id
                $mediaGallery = media_gallery::find($documentId);

                // Update the alt field with the corresponding document_name
                if ($mediaGallery) {
                    $mediaGallery->update(['alt' => $documentNames[$index]]);
                }
            }

            media_gallery::whereNotIn('id', $documentIds)->where('object_type', owners::class)->where('object_id', $owner->id)->delete();
        }

        // Get the updated values after updating
        $updatedValues = $owner->getAttributes();
        unset($updatedValues['updated_at']);
        unset($updatedValues['created_by']);
        unset($updatedValues['updated_by']);
        
        // Log the changes
        $logDetails = [];

        foreach ($updatedValues as $field => $newValue) {
            $oldValue = $originalValues[$field];
    
            // Check if the field has changed
            if ($oldValue != $newValue) {
                // If the changed field is 'city_id', handle null values
                if ($field == 'source_id') {
                    $oldSourceName = $oldValue ? Sources::find($oldValue)->name : 'empty';
                    $newSourceName = $newValue ? Sources::find($newValue)->name : 'empty';
    
                    $logDetails[] = "Source: $oldSourceName to $newSourceName";
                }
                else if ($field == 'sub_source_id') {
                    $oldSubSourceName = $oldValue ? SubSources::find($oldValue)->name : 'empty';
                    $newSubSourceName = $newValue ? SubSources::find($newValue)->name : 'empty';
    
                    $logDetails[] = "Sub Source: $oldSubSourceName to $newSubSourceName";
                }
                else {
                    $logDetails[] = "$field: $oldValue to $newValue";
                }
            }
        }

        if (!empty($logDetails)) {
            $logMessage = implode(', ', $logDetails);

            activity()
                ->performedOn($owner)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Owner updated successfully.']);
    }

    // Helper method to sync documents
    private function syncDocuments(Request $request, owners $owner, $existingDocuments)
    {
        // Check if documents are submitted in the request
        if ($request->has('document_name')) {
            $documents = $request->input('document_names');

            // Loop through submitted documents and sync/update them
            foreach ($documents as $index => $document) {
                $existingDocument = $existingDocuments[$index] ?? null;

                if ($existingDocument) {
                    // Update existing document
                    $existingDocument->update(['object' => $document]);
                } else {
                    // Create new document
                    $file = $request->file('file')[$index];
                    $filename = $file->store('uploads/owners/documents', 'public');
                    $fileType = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();

                    // media_gallery::create([
                    //     'object' => $document,
                    //     'object_id' => $owner->id,
                    //     'object_type' => owners::class,
                    //     'path' => $filename,
                    //     'file_name' => $originalName,
                    //     'file_type' => $fileType,
                    //     'status' => true,
                    //     'featured' => false,
                    //     'created_by' => auth()->user()->id,
                    // ]);

                    $document_note = new media_gallery;
                    $document_note->object = 'document';
                    $document_note->alt = $request->input('document_name')[$index];
                    $document_note->object_id = $owner->id;
                    $document_note->object_type = owners::class;
                    $document_note->path = $filename;
                    $document_note->file_name = $originalName;
                    $document_note->file_type = $fileType;
                    $document_note->status = true;
                    $document_note->featured = false;
                    $document_note->created_by = auth()->user()->id;
                    $document_note->updated_by = auth()->user()->id;
                    $document_note->save();
                }
            }

            // Remove documents that are not in the submitted list
            foreach ($existingDocuments as $existingDocument) {
                if (!in_array($existingDocument->object, $documents)) {
                    $existingDocument->delete();
                }
            }
        }
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk delete.']);
        }

        try {
            $subRecordCheck = owners::whereIn('id', $itemIds)->whereHas('listings')->pluck('name');

            if ($subRecordCheck->isNotEmpty()) {
                return response()->json(['error' => "Cannot delete. The following owners have associated listings: " . $subRecordCheck->implode(', ')]);
            }

            foreach($itemIds as $itemId){
                $data = owners::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Owner is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            owners::whereIn('id', $itemIds)->delete();

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
            owners::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = owners::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Owner is restored through bulk action.')
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
                $data = owners::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Owner is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            owners::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = owners::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Owner is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = owners::whereIn('id', $itemIds)->update(['status' => 0]);
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
