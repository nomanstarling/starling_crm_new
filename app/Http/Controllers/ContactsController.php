<?php

namespace App\Http\Controllers;

use App\Models\contacts;
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
use App\Exports\ContactsExport;
use Spatie\Valuestore\Valuestore;

class ContactsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->settings = Valuestore::make(config('settings.path'));
        $this->shortName = $this->settings->get('short_name');
    }
    
    public function index(Request $request)
    {
        if(!$request->query('type')) {
            $this->authorize('contacts_view_all');
        }

        if($request->query('type') && $request->query('type') == 'buyer') {
            $this->authorize('contacts_view_buyers');
        }

        if($request->query('type') && $request->query('type') == 'seller') {
            $this->authorize('contacts_view_sellers');
        }

        if($request->query('type') && $request->query('type') == 'tenant') {
            $this->authorize('contacts_view_tenants');
        }

        if($request->query('type') && $request->query('type') == 'landlord') {
            $this->authorize('contacts_view_landlords');
        }

        $firstDate = contacts::min('created_at');
        $sources = Sources::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $countries = countries::orderBy('name')->get();
        $cities = cities::orderBy('name')->get();
        return view('admin.contacts.index', compact('sources', 'firstDate', 'users', 'countries', 'cities'));
    }

    public function getList(Request $request)
    {
        $searchTerm = $request->input('q');
        $searchTermId = $request->input('id');
        $query = contacts::select('refno', 'id', 'title', 'contact_type', 'name', 'email', 'phone')->orderBy('name');

        if ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%')
                ->orWhere('refno', 'like', '%' . $searchTerm . '%')
                ->orWhere('phone', 'like', '%' . $searchTerm . '%');
        }
        if($searchTermId){
            $query->where('id', $searchTermId);
        }

        $contacts = $query->limit(100)->get();
        return response()->json(['results' => $contacts]);
    }

    
    public function getContacts(Request $request)
    {
        $status = $request->input('status', 1);
        $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

        $contact_type = $request->input('type', null);

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $startCreatedDate = $request->input('startCreatedDate');
        $endCreatedDate = $request->input('endCreatedDate');

        $contacts = contacts::when($status !== null, function ($query) use ($status) {
            if ($status === 'deleted') {
                return $query->onlyTrashed();
            }
            else if ($status == 1 || $status == 0) {
                return $query->where('status', $status);
            } 
        });
        if($contact_type != null){
            $contacts->where('contact_type', $contact_type);
        }
        if ($startDate && $endDate) {
            $contacts->where(function ($query) use ($startDate, $endDate) {
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
            $contacts->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
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

        if ($request->input('contact_type') != null) {
            $contacts->where('contact_type', $request->input('contact_type'));
        }

        if ($request->input('source_name') != null) {
            $contacts->whereHas('source', function ($query) use ($request) {
                $query->where('name', $request->input('source_name'));
            });
        }

        if ($request->input('sub_source_name') != null) {
            $contacts->whereHas('sub_source', function ($query) use ($request) {
                $query->where('name', $request->input('sub_source_name'));
            });
        }

        if ($request->input('created_by_name') != null) {
            $contacts->whereHas('created_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('created_by_name'));
            });
        }

        if ($request->input('updated_by_name') != null) {
            $contacts->whereHas('updated_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('updated_by_name'));
            });
        }

        if ($request->input('contact_name') != null) {
            $contacts->where('name', 'like', '%' . $request->input('contact_name') . '%')
                 ->orWhere('email', 'like', '%' . $request->input('contact_name') . '%')
                 ->orWhere('phone', 'like', '%' . $request->input('contact_name') . '%');
        }            
        
        if ($request->input('refno') != null) {
            $contacts->where('refno', 'like', '%' . $request->input('refno') . '%');
        }

        // if ($request->input('whatsapp') != null) {
        //     $owners->where('whatsapp', 'like', '%' . $request->input('whatsapp') . '%');
        // }

        $totalRecords = $contacts->count(); // Fetch the total count
        $perPage = $request->input('length', 10); // Number of records per page
        
        $contacts = $contacts->latest('updated_at')
            ->with('notes', 'documents', 'updated_by_user', 'created_by_user', 'source', 'sub_source')
            ->skip(($request->input('start', 0) / $perPage) * $perPage)
            ->take($perPage)
            ->get();

        $contacts = $contacts->map(function ($contact) {
            return array_merge($contact->toArray(), [
                'profile_image_url' => $contact->profileImage(),
            ]);
        });

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $contacts,
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
        $matchingRecord = contacts::where('refno', $refno)->first();

        if ($matchingRecord) {
            $position = contacts::where($orderByColumn, '>', $matchingRecord->$orderByColumn)
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
        $contacts = contacts::with('notes', 'documents', 'source', 'sub_source');
        // Check if any user IDs are provided
        if (!empty($itemIds)) {
            $contacts = $contacts->whereIn('id', $itemIds);
        }
        else{
            $status = $request->input('status', 1);
            $status = ($status == 'active') ? 1 : (($status == 'inactive') ? 0 : ($status == 'deleted' ? 'deleted' : 1));

            $startDate = $request->filters['startDate'];
            $endDate = $request->filters['endDate'];

            $startCreatedDate = $request->filters['startCreatedDate'];
            $endCreatedDate = $request->filters['endCreatedDate'];

            $contacts = $contacts->when($status !== null, function ($query) use ($status) {
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
                $contacts->whereHas('source', function ($query) use ($request) {
                    $query->where('name', $request->filters['source_name']);
                });
            }

            if ($request->input('contact_type') != null) {
                $contacts->where('contact_type', $request->input('contact_type'));
            }

            if ($request->filters['sub_source_name'] != null) {
                $contacts->whereHas('sub_source', function ($query) use ($request) {
                    $query->where('name', $request->filters['sub_source_name']);
                });
            }

            if ($request->filters['created_by_name'] != null) {
                $contacts->whereHas('created_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['created_by_name']);
                });
            }

            if ($request->filters['updated_by_name'] != null) {
                $contacts->whereHas('updated_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['updated_by_name']);
                });
            }

            if ($request->filters['contact_name'] != null) {
                $contacts->where('name', 'like', '%' . $request->filters['contact_name'] . '%')
                     ->orWhere('email', 'like', '%' . $request->filters['contact_name'] . '%')
                     ->orWhere('phone', 'like', '%' . $request->filters['contact_name'] . '%');
            }            
            
            if ($request->filters['refno'] != null) {
                $contacts->where('refno', 'like', '%' . $request->filters['refno'] . '%');
            }

            // if ($request->filters['whatsapp'] != null) {
            //     $contacts->where('whatsapp', 'like', '%' . $request->filters['whatsapp'] . '%');
            // }
        }
        $contacts = $contacts->latest('updated_at')->get();

        // Export the data using the Excel facade
        $filename = 'contacts_export_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        $export = new ContactsExport($contacts); // Use your Export class

        // Generate the Excel file
        $file = Excel::download($export, $filename)->getFile();

        // Get the file content
        $fileContent = file_get_contents($file);

        // Encode file content to base64
        $base64File = base64_encode($fileContent);

        // Return JSON response with file and filename
        return response()->json(['file' => $base64File, 'filename' => $filename]);
    }

    public function edit(contacts $contact)
    {
        // Eager load documents
        $loadedContact = $contact->load('documents', 'notes.created_by_user', 'created_by_user', 'updated_by_user');
        $loadedContact['profile_image'] = $loadedContact->profileImage();

        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($loadedContact) {
                $query->where('subject_id', $loadedContact->id)
                    ->where('subject_type', get_class($loadedContact));
            })
            ->orderBy('created_at', 'desc')
            ->where('properties', '!=', null)
            ->where('properties', '!=', '[]')
            ->get();

        // Manually add file URL to each document
        $loadedContact->documents->each(function ($document) {
            $document->file_url = asset('public/storage/' . $document->path);
        });

        $loadedContact['activities'] = $activities;

        return response()->json(['contact' => $loadedContact]);
    }

    private function getNextRefNo(){
        //$latestOwner = owners::latest()->first();
        $latestContact = contacts::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestContact) {
            // Extract the numeric part of the existing refno
            $latestRefNo = $latestContact->refno;
            
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            
            // Increment the numeric part
            $nextNumericPart = $numericPart + 1;
            // Generate the new refno
            $newRefNo = $this->shortName . '-C-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing records, start from 001
            $newRefNo = $this->shortName . '-C-001';
        }
        return $newRefNo;
    }

    public function storeAjax(Request $request){
        try {
            // Validate the request data as per your requirements
            $request->validate([
                'name' => 'required|string|max:255|unique:contacts',
                'phone' => 'required|string|max:255|unique:contacts',
            ]);

            $refno = $this->getNextRefNo();

            $dob = null;
            if($request->input('dob') != null){
                $dob = Carbon::parse($request->input('dob'))->format('Y-m-d');
            }
            // Create the data
            $contact = contacts::create([
                'refno' => $refno,
                'title' => $request->input('title'),
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                //'whatsapp' => $request->input('whatsapp'),
                'email' => $request->input('email'),
                'dob' => $dob,
                'address' => $request->input('address'),
                'created_by' => auth()->user()->id,
            ]);

            activity()
                ->performedOn($contact)
                ->causedBy(auth()->user())
                ->withProperties('Contact created from listing page.')
                ->log('created');

            // Return a JSON response with the contact ID and success message
            return response()->json([
                'contactId' => $contact->id,
                'success' => 'Contact added successfully.',
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
            'name' => 'required|string|max:255|unique:contacts',
            'phone' => 'required|string|max:255|unique:contacts',
            'email' => 'required|string|max:255|unique:contacts',
        ]);

        $photo = null;
        if ($request->hasFile('avatar')) {
            $photo = $request->file('avatar')->store('uploads/contacts/images', 'public');
        }
        $dob = null;
        if($request->input('dob') != null){
            $dob = Carbon::parse($request->input('dob'))->format('Y-m-d');
        }

        $refnoType = $this->shortName.'-O-';
        
        $refno = $this->getNextRefNo();
        // Create the data
        $contact = contacts::create([
            'refno' => $refno,
            'title' => $request->input('title'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'contact_type' => $request->input('contact_type'),
            //'whatsapp' => $request->input('whatsapp'),
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
    
                $filename = $file->store('uploads/contacts/'.$refno.'/documents', 'public');
                $fileType = $file->getClientOriginalExtension();
                $originalName = $file->getClientOriginalName();
                $size = $file->getSize();
    
                media_gallery::create([
                    'object' => 'document',
                    'alt' => $request->input('document_name')[$index],
                    'object_id' => $contact->id,
                    'object_type' => contacts::class,
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
                        'object_id' => $contact->id,
                        'object_type' => contacts::class,
                        'note' => $note,
                        'status' => true,
                        'created_by' => auth()->user()->id,
                        'updated_by' => auth()->user()->id,
                    ]);
                }
            }
        }

        activity()
            ->performedOn($contact)
            ->causedBy(auth()->user())
            ->withProperties('Contact created.')
            ->log('created');

        return response()->json(['message' => 'Contact created successfully']);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:contacts,name,'.$id,
            'phone' => 'required|string|max:255|unique:contacts,phone,'.$id,
            'email' => 'required|string|max:255|unique:contacts,email,'.$id,
        ]);

        // Find the contact
        $contact = contacts::findOrFail($id);

        // Retrieve the existing documents
        $existingDocuments = $contact->documents;

        $photo = $contact->photo;;
        if ($request->hasFile('avatar')) {
            $photo = $request->file('avatar')->store('uploads/contacts/images', 'public');
        }

        $originalValues = $contact->getOriginal();

        $dob = $contact->dob;
        if($request->input('dob') != null){
            $dob = Carbon::parse($request->input('dob'))->format('Y-m-d');
        }

        // Update the contact
        $contact->update([
            'title' => $request->input('title'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            //'whatsapp' => $request->input('whatsapp'),
            'email' => $request->input('email'),
            'dob' => $dob,
            'company' => $request->input('company'),
            'designation' => $request->input('designation'),
            'religion' => $request->input('religion'),
            'website' => $request->input('website'),
            'contact_type' => $request->input('contact_type'),
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
    
                $filename = $file->store('uploads/contacts/documents', 'public');
                $fileType = $file->getClientOriginalExtension();
                $originalName = $file->getClientOriginalName();
                $size = $file->getSize();

                $document_note = new media_gallery;
                $document_note->object = 'document';
                $document_note->alt = $request->input('document_name')[$index];
                $document_note->object_id = $contact->id;
                $document_note->object_type = contacts::class;
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

        // Delete all existing notes for this contact
        $contact->notes()->forceDelete();
        // Save the received notes
        foreach ($noteValues as $noteValue) {
            $creat_note = new Notes;
            $creat_note->object = 'Notes';
            $creat_note->object_id = $contact->id;
            $creat_note->object_type = contacts::class;
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

            media_gallery::whereNotIn('id', $documentIds)->where('object_type', contacts::class)->where('object_id', $contact->id)->delete();
        }

        // Get the updated values after updating
        $updatedValues = $contact->getAttributes();
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
                ->performedOn($contact)
                ->causedBy(auth()->user())
                ->withProperties(['details' => $logMessage])
                ->log('updated');
        }
        
        return response()->json(['message' => 'Contact updated successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk delete.']);
        }

        try {
            // $subRecordCheck = contacts::whereIn('id', $itemIds)->whereHas('leads')->pluck('name');

            // if ($subRecordCheck->isNotEmpty()) {
            //     return response()->json(['error' => "Cannot delete. The following contacts have associated leads: " . $subRecordCheck->implode(', ')]);
            // }

            foreach($itemIds as $itemId){
                $data = contacts::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Contact is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            contacts::whereIn('id', $itemIds)->delete();

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
            contacts::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = contacts::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Contact is restored through bulk action.')
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
                $data = contacts::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Contact is activated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 1 for users by IDs
            contacts::whereIn('id', $itemIds)->update(['status' => 1]);

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
                $data = contacts::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Contact is deactivated through bulk action.')
                    ->log('updated');
            }

            // Use the User model to update the status to 0 for users by IDs
            $update = contacts::whereIn('id', $itemIds)->update(['status' => 0]);
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
