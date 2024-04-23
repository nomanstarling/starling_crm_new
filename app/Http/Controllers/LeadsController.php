<?php

namespace App\Http\Controllers;

use App\Models\Leads;
use App\Models\LeadDetails;

use App\Models\Sources;
use App\Models\SubSources;
use App\Models\Contacts;
use App\Models\Listings;
use App\Models\User;
use App\Models\countries;
use App\Models\cities;
use App\Models\Campaigns;
use App\Models\Statuses;
use App\Models\SubStatuses;
use App\Models\Notes;
use App\Models\media_gallery;
use App\Models\Teams;
use App\Models\sub_communities;
use App\Models\communities;
use App\Models\towers;
use App\Mail\LeadsAssignEmail;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

use Spatie\Valuestore\Valuestore;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use App\Services\UtilService;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Concerns\ToArray;

class LeadsController extends Controller
{
    protected $whatsAppService;
    protected $utilService;

    public function __construct(WhatsAppService $whatsAppService, UtilService $utilService)
    {
        $this->middleware('auth');
        $this->whatsAppService = $whatsAppService;
        $this->settings = Valuestore::make(config('settings.path'));
        $this->shortName = $this->settings->get('short_name');
        $this->utilService = $utilService;
    }

    public function index(Request $request)
    {
        if(!$request->query('type')) {
            $this->authorize('leads_view_all');
        }

        if($request->query('type') && $request->query('type') == 'unassigned') {
            $this->authorize('leads_view_unassigned');
        }

        if($request->query('type') && $request->query('type') == 'active') {
            $this->authorize('leads_view_active');
        }

        if($request->query('type') && $request->query('type') == 'closed') {
            $this->authorize('leads_view_closed');
        }

        if($request->query('type') && $request->query('type') == 'dead') {
            $this->authorize('leads_view_dead');
        }

        $team_users = [];
        $firstDate = Leads::min('created_at');
        $sources = Sources::orderBy('name')->get();
        $users = User::orderBy('name');

        if(auth()->user()->is_teamleader == true){
            $team = Teams::with('users')->where('team_leader', auth()->user()->id)->first();
            if ($team && $team->users->isNotEmpty()) {
                $team_users = $team->users->pluck('id')->toArray();
            }
            if (!empty($team_users) && !in_array(auth()->user()->id, $team_users)) {
                $team_users[] = auth()->user()->id;
            }
            if (!empty($team_users)) {
                $users->whereIn('id', $team_users);
            }
        }

        $users = $users->get();
        $countries = countries::orderBy('name')->get();
        $cities = cities::orderBy('name')->get();
        $campaigns = Campaigns::orderBy('name')->get();
        $statuses = Statuses::orderBy('name')->where('type', 'Leads')->get();
        return view('admin.leads.index', compact('firstDate', 'sources', 'campaigns', 'users', 'countries', 'cities', 'statuses', 'team_users'));
    }

    public function manual(){
        if(auth()->user()->user_name == 'noman.m'){
            return view('admin.leads.manual');
        }
        else{
            abort(403); // Or any other logic to handle unauthorized access
        }
    }

    public function manualPost(Request $request){
        $contents = $request->content;
        if($contents != null){
            $lines = explode("\n", $contents);
            // Initialize formattedText
            $formattedText = "";

            // Iterate through each line and format it
            foreach ($lines as $line) {
                // Trim leading and trailing whitespaces
                $line = trim($line);

                // Skip empty lines
                if ($line === "") {
                    continue;
                }

                // Add the line with a newline character
                $formattedText .= $line . "\n";
            }

            // Remove the trailing newline character
            $data = rtrim($formattedText, "\n");
            // Convert the data array to JSON
            $jsonData = json_encode(['message' => ['message' => ['text' => $data]]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            //print_r($jsonData); exit;

            // Build the internal URL
            $internalUrl = route('api.webhookEmail');

            // JSON data to be sent
            //$jsonData = json_encode(['message' => ['message' => ['text' => $data]]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            // Initialize cURL session
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $internalUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // Set the Content-Type header

            // Execute cURL session and capture the response
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            }

            // Close cURL session
            curl_close($ch);
            $responseData = json_decode($response);

            if(isset($responseData->status) && $responseData->status === "success") {
                return back()->with('success', $responseData->message);
            } else {
                return back()->with('error', 'Lead Import Error: '.$responseData->message);
            }

            // if($response){
            //     return back()->with('success', 'Lead imported successfully.');
            // }
            // else{
            //     return back()->with('error', 'Lead Import Error');
            // }
        }
        else{
            return back()->with('error', 'content should not be empty');
        }
    }

    public function getLeads(Request $request)
    {
        $validLeads = $this->utilService->valid_leads();

        $archived = $request->input('archivedd');
        $type = $request->input('type');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $startCreatedDate = $request->input('startCreatedDate');
        $endCreatedDate = $request->input('endCreatedDate');

        $startEnqDate = $request->input('startEnqDate');
        $endEnqDate = $request->input('endEnqDate');

        $startAssignedDate = $request->input('startAssignedDate');
        $endAssignedDate = $request->input('endAssignedDate');

        $startAcceptedDate = $request->input('startAcceptedDate');
        $endAcceptedDate = $request->input('endAcceptedDate');

        $leads = Leads::when($archived == 'yes', function ($query) use ($archived) {
            if ($archived == 'yes') {
                $query->onlyTrashed();
            }
        });

        if ($type && $type != null) {
            $leads->where(function ($query) use ($type, $validLeads) {
                if ($type == 'unassigned') {
                    $query->where('assign_status', 'Unassigned');
                } 
                else if ($type == 'active') {
                    $query->whereIn('status_id', $validLeads);
                }
                else if ($type == 'closed') {
                    $get_status = Statuses::where('name', 'Deal Closed')->first();
                    if($get_status != null){
                        $query->where('status_id', $get_status->id);
                    }
                }
                else if ($type == 'dead') {
                    $get_status = Statuses::where('name', 'Archive')->first();
                    if($get_status != null){
                        $query->where('status_id', $get_status->id);
                    }
                }
            });
        }

        if ($startDate && $endDate) {
            $leads->where(function ($query) use ($startDate, $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate('updated_at', $startDate);
                } else {
                    $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            });
        }

        if ($startCreatedDate && $endCreatedDate) {
            $leads->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
                if ($startCreatedDate === $endCreatedDate) {
                    $query->whereDate('created_at', $startCreatedDate);
                } else {
                    $query->whereBetween('created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
                }
            });
        }

        if ($startEnqDate && $endEnqDate) {
            $leads->where(function ($query) use ($startEnqDate, $endEnqDate) {
                if ($startEnqDate === $endEnqDate) {
                    $query->whereDate('created_at', $startEnqDate);
                } else {
                    $query->whereBetween('created_at', [$startEnqDate . ' 00:00:00', $endEnqDate . ' 23:59:59']);
                }
            });
        }

        if ($startAssignedDate && $endAssignedDate) {
            $leads->where(function ($query) use ($startAssignedDate, $endAssignedDate) {
                if ($startAssignedDate === $endAssignedDate) {
                    $query->whereDate('assigned_date', $startAssignedDate);
                } else {
                    $query->whereBetween('assigned_date', [$startAssignedDate . ' 00:00:00', $endAssignedDate . ' 23:59:59']);
                }
            });
        }

        if ($startAcceptedDate && $endAcceptedDate) {
            $leads->where(function ($query) use ($startAcceptedDate, $endAcceptedDate) {
                if ($startAcceptedDate === $endAcceptedDate) {
                    $query->whereDate('accepted_date', $startAcceptedDate);
                } else {
                    $query->whereBetween('accepted_date', [$startAcceptedDate . ' 00:00:00', $endAcceptedDate . ' 23:59:59']);
                }
            });
        }

        if ($request->input('refno') != null) {
            $leads->where('refno', 'like', '%' . $request->input('refno') . '%');
        }
        
        if ($request->input('status') != null) {
            $leads->whereHas('status', function ($query) use ($request) {
                $query->where('name', $request->input('status'));
            });
        }

        if ($request->input('sub_status') != null) {
            $leads->whereHas('sub_status', function ($query) use ($request) {
                $query->where('name', $request->input('sub_status'));
            });
        }

        if ($request->input('stage') != null) {
            $leads->where('lead_stage', $request->input('stage'));
        }

        if ($request->input('client_details') != null) {
            $leads->whereHas('contact', function ($query) use ($request) {
                $client_details = $request->input('client_details');
                $query->where('name', 'LIKE', "%$client_details%")
                ->orWhere('phone', 'LIKE', "%$client_details%")
                ->orWhere('email', 'LIKE', "%$client_details%")
                ->orWhere('refno', 'LIKE', "%$client_details%");
            });
        }

        if ($request->input('property') != null) {
            $leads->whereHas('property', function ($query) use ($request) {
                $property = $request->input('property');
                $query->where('refno', 'LIKE', "%$property%");
            });
        }

        if ($request->input('campaign') != null) {
            $leads->whereHas('campaign', function ($query) use ($request) {
                $query->where('name', $request->input('campaign'));
            });
        }

        if ($request->input('lead_agent') != null) {
            $leads->whereHas('lead_agent', function ($query) use ($request) {
                $query->where('name', $request->input('lead_agent'));
            });
        }

        if ($request->input('source') != null) {
            $leads->whereHas('source', function ($query) use ($request) {
                $query->where('name', $request->input('source'));
            });
        }

        if ($request->input('sub_source') != null) {
            $leads->whereHas('sub_source', function ($query) use ($request) {
                $query->where('name', $request->input('sub_source'));
            });
        }

        if ($request->input('created_by') != null) {
            $leads->whereHas('created_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('created_by'));
            });
        }

        if ($request->input('updated_by') != null) {
            $leads->whereHas('updated_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('updated_by'));
            });
        }

        if (!auth()->user()->hasRole('Super Admin')) {
            if(auth()->user()->is_teamleader == true){
                $team = Teams::with('users')->where('team_leader', auth()->user()->id)->first();
                $user_ids = [];
                
                if ($team && $team->users->isNotEmpty()) {
                    $user_ids = $team->users->pluck('id')->toArray();
                }

                if (!empty($user_ids) && !in_array(auth()->user()->id, $user_ids)) {
                    $user_ids[] = auth()->user()->id;
                }

                if (!empty($user_ids)) {
                    $leads->whereIn('agent_id', $user_ids);
                }
            }
            else{
                $leads->where('agent_id', auth()->user()->id);
            }
        }

        $totalRecords = $leads->count();
        $perPage = $request->input('length', 10);
        
        $leads = $leads->latest('updated_at')
            ->with('notes', 'documents', 'campaign', 'contact', 'property', 'lead_details', 'source', 'sub_source', 'lead_agent', 'updated_by_user', 'created_by_user', 'status', 'sub_status')
            ->skip(($request->input('start', 0) / $perPage) * $perPage)
            ->take($perPage)->get()
            ->map(function ($lead) {
                // Include additional information for related models
                return array_merge($lead->toArray(), [
                    //'owner_image' => $listing->owner ? $listing->owner->profileImage() : '', // Assuming profileImage() returns the image URL
                    'lead_agent_image' => $lead->lead_agent ? $lead->lead_agent->profileImage() : '',
                    //'listing_agent_image' => $listing->listing_agent ? $listing->listing_agent->profileImage() : '',
                    // 'portals_info' => $listing->portals->map(function ($portal) {
                    //     return [
                    //         'name' => $portal->name,
                    //         'portal_logo' => $portal->logoImage(),
                    //     ];
                    // })->toArray(),
                    // Add more as needed
                ]);
            });

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $leads,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);
        // return response()->json(['listings' => $listings]);
    }

    private function getNextRefNo(){
        //$latestOwner = owners::latest()->first();
        $latestLead = Leads::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestLead) {
            // Extract the numeric part of the existing refno
            $latestRefNo = $latestLead->refno;
            
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            
            // Increment the numeric part
            $nextNumericPart = $numericPart + 1;
            // Generate the new refno
            $newRefNo = $this->shortName . '-L-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing records, start from 001
            $newRefNo = $this->shortName . '-L-001';
        }
        return $newRefNo;
    }

    public function searchRefno(Request $request)
    {
        $refno = $request->input('refno');
        $orderByColumn = 'updated_at';

        // Use your model to search for the record based on refno
        $matchingRecord = Leads::where('refno', $refno)->first();

        if ($matchingRecord) {
            $position = Leads::where($orderByColumn, '>', $matchingRecord->$orderByColumn)
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

    public function edit(Leads $lead)
    {
        // Eager load documents
        $loadedLead = $lead->load('documents', 'lead_details', 'notes.created_by_user', 'created_by_user', 'updated_by_user');
        //$loadedListing['profile_image'] = $loadedListing->profileImage();

        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($loadedLead) {
                $query->where('subject_id', $loadedLead->id)
                    ->where('subject_type', get_class($loadedLead));
            })
            ->orderBy('created_at', 'desc')
            ->where('properties', '!=', null)
            ->where('properties', '!=', '[]')
            ->get();

        // Manually add file URL to each document
        $loadedLead->documents->each(function ($document) {
            $document->file_url = asset('public/storage/' . $document->path);
        });

        $loadedLead['activities'] = $activities;

        return response()->json(['lead' => $loadedLead]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'contact_id' => 'required',
                'status_id' => 'required',
                'agent_id' => 'required',
            ];

            $request->validate($rules);

            $refno = $this->getNextRefNo();

            $assigned_date = null;
            $enquiry_date = null;
            if($request->input('agent_id') != null){
                $assigned_date = Carbon::now()->format('Y-m-d H:i:s');
                $enquiry_date = Carbon::now()->format('Y-m-d H:i:s');
            }

            // Create the data
            $lead = Leads::create([
                'refno' => $refno,
                'contact_id' => $request->input('contact_id'),
                'listing_id' => $request->input('listing_id'),
                'status_id' => $request->input('status_id'),
                'sub_status_id' => $request->input('sub_status_id'),
                'lead_stage' => $request->input('lead_stage'),
                'agent_id' => $request->input('agent_id'),
                'source_id' => $request->input('source_id'),
                'sub_source_id' => $request->input('sub_source_id'),
                'campaign_id' => $request->input('campaign_id'),
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'assigned_date' => $assigned_date,
                'enquiry_date' => $enquiry_date,
            ]);

            if ($request->hasFile('file')) {
                $files = $request->file('file');
        
                foreach ($files as $index => $file) {
        
                    $filename = $file->store('uploads/leads/'.$refno.'/documents', 'public');
                    $fileType = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();
                    $size = $file->getSize();
        
                    $document_note = new media_gallery;
                    $document_note->object = 'document';
                    $document_note->alt = $request->input('document_name')[$index];
                    $document_note->object_id = $lead->id;
                    $document_note->object_type = Leads::class;
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

            if ($request->filled('note_values')) {
                //$noteValues = $request->input('note_values');

                $noteValues = $request->input('note_values');
                $noteType = $request->input('note_types');
                $noteDates = $request->input('note_dates');

                // $noteDates = array_map(function ($value) {
                //     return ($value == 'null') ? null : $value;
                // }, $noteDates);

                if ($noteDates !== null) {
                    $noteDates = array_map(function ($value) {
                        return ($value == 'null') ? null : $value;
                    }, $noteDates);
                }
        
                if (is_array($noteValues) && count($noteValues) > 0) {
                    foreach ($noteValues as $index => $note) {
                        $eventDate = ($noteDates[$index] != 'null' || $noteDates[$index] != null || $noteDates[$index] != '') ? Carbon::parse($noteDates[$index]) : null;

                        Notes::create([
                            'object' => 'Notes',
                            'object_id' => $lead->id,
                            'object_type' => Leads::class,
                            'note' => $note,
                            'type' => $noteType[$index] != 'null' ? $noteType[$index] : null,
                            'event_date' => $noteType[$index] != null && $noteType[$index] != 'note' ? $eventDate : null,
                            'status' => true,
                            'created_by' => auth()->user()->id,
                            'updated_by' => auth()->user()->id,
                        ]);
                    }
                }
            }

            $move_in_date = null;
            if($request->input('move_in_date') != null){
                $move_in_date = Carbon::parse($request->input('move_in_date'))->format('Y-m-d');
            }

            $lead_details = LeadDetails::create([
                'lead_id' => $lead->id,
                'community' => $request->input('community_id'),
                'subcommunity' => $request->input('sub_community_id'),
                'property' => $request->input('tower_id'),
                'budget' => $request->input('budget'),
                'bedroom' => $request->input('beds'),
                'move_in' => $move_in_date,

                'cheque' => $request->input('cheque'),
                'furnish' => $request->input('furnish'),
                'upgraded' => $request->input('upgraded'),
                'landscape' => $request->input('landscape'),
                'bathroom' => $request->input('bathroom'),
                'kitchen' => $request->input('kitchen'),
                'schools' => $request->input('schools'),
                'pets' => $request->input('pets'),
                'current_home' => $request->input('current_home'),
                'parking' => $request->input('parking'),
                'work_place' => $request->input('work_place'),
                'view' => $request->input('view'),
                'floor' => $request->input('floor'),
                'bua' => $request->input('bua'),
                'plot_size' => $request->input('plot_size'),
                'new_to_dubai' => $request->input('new_to_dubai'),
            ]);
            
            activity()
                ->performedOn($lead)
                ->causedBy(auth()->user())
                ->withProperties('Lead created.')
                ->log('created');
            
            DB::commit();

            return response()->json(['message' => 'Lead created successfully']);
        }
        catch (\Throwable $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // Handle the exception as needed
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    function touchDirectory($path)
	{
		$path =  'public/storage/' . $path;

		// check if it exists
		if (!is_dir($path)) {
			// create directory
			mkdir($path, 0777, true);
		}
	}

    public function update(Request $request, $id)
    {
        // Start a database transaction
        DB::beginTransaction();
        try {

            $rules = [
                'contact_id' => 'required',
                'status_id' => 'required',
                'agent_id' => 'required',
            ];

            $request->validate($rules);
            $refno = $this->getNextRefNo();

            // Find the Lead
            $lead = Leads::findOrFail($id);

            $assigned_date = $lead->assigned_date;
            $accepted_date = $lead->accepted_date;
            if($request->input('agent_id') != null && $lead->agent_id != $request->input('agent_id')){
                $assigned_date = Carbon::now()->format('Y-m-d H:i:s');
                $accepted_date = null;
            }

            // Retrieve the existing documents
            $existingDocuments = $lead->documents;
            $originalValues = $lead->getOriginal();

            //update lead
            $lead->update([
                'contact_id' => $request->input('contact_id'),
                'listing_id' => $request->input('listing_id'),
                'status_id' => $request->input('status_id'),
                'sub_status_id' => $request->input('sub_status_id'),
                'lead_stage' => $request->input('lead_stage'),
                'agent_id' => $request->input('agent_id'),
                'source_id' => $request->input('source_id'),
                'sub_source_id' => $request->input('sub_source_id'),
                'campaign_id' => $request->input('campaign_id'),
                'updated_by' => auth()->user()->id,
                'assigned_date' => $assigned_date,
                'accepted_date' => $accepted_date,
            ]);

            //documents starts from here
            // Update document names in media_gallery
            if ($request->has('document_id') && $request->has('document_names')) {
                $documentIds = $request->input('document_id');
                $documentNames = $request->input('document_names');

                foreach ($documentIds as $index => $documentId) {
                    // Find the corresponding media_gallery record by document_id
                    $mediaGallery = media_gallery::find($documentId);
                    $doc_path = $mediaGallery->path;

                    // Update the alt field with the corresponding document_name
                    if ($mediaGallery) {
                        $mediaGallery->update(['alt' => $documentNames[$index], 'path' => $doc_path]);
                    }
                }

                media_gallery::whereNotIn('id', $documentIds)->where('object', 'document')->where('object_type', Leads::class)->where('object_id', $lead->id)->delete();
            }
            else{
                media_gallery::where('object', 'document')->where('object_type', Leads::class)->where('object_id', $lead->id)->delete();
            }

            // insert new documents
            if ($request->hasFile('file')) {
                
                $files = $request->file('file');
        
                foreach ($files as $index => $file) {
        
                    $filename = $file->store('uploads/leads/'.$lead->refno.'/documents', 'public');
                    $fileType = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();
                    $size = $file->getSize();
                    //return $filename;
        
                    $document_note = new media_gallery;
                    $document_note->object = 'document';
                    $document_note->alt = $request->input('document_name')[$index];
                    $document_note->object_id = $lead->id;
                    $document_note->object_type = Leads::class;
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

            //documents ends here

            // Synchronize Notes
            $noteValues = $request->input('note_values', []);
            $noteType = $request->input('note_types');
            $noteDates = $request->input('note_dates');
            $noteUpdatedAt = $request->input('note_updated_at');
            $noteCreatedAt = $request->input('note_created_at');

            // Convert 'null' strings to null values in $noteUpdatedAt array
            if ($noteDates !== null) {
                $noteDates = array_map(function ($value) {
                    return ($value == 'null') ? null : $value;
                }, $noteDates);
            }
            
            if ($noteUpdatedAt !== null) {
                $noteUpdatedAt = array_map(function ($value) {
                    return ($value == 'null') ? null : $value;
                }, $noteUpdatedAt);
            }
            
            if ($noteCreatedAt !== null) {
                $noteCreatedAt = array_map(function ($value) {
                    return ($value == 'null') ? null : $value;
                }, $noteCreatedAt);
            }

            // Delete all existing notes for this owner
            $lead->notes()->forceDelete();
            // Save the received notes
            foreach ($noteValues as $index => $noteValue) {
                
                //$eventDate = ($noteDates[$index] !== null) ? Carbon::parse($noteDates[$index]) : null;
                //$updatedAt = ($noteUpdatedAt[$index] !== null) ? Carbon::parse($noteUpdatedAt[$index]) : null;
                //$createdAt = ($noteCreatedAt[$index] !== null) ? Carbon::parse($noteCreatedAt[$index]) : null;

                $eventDate = ($noteDates[$index] != 'null' || $noteDates[$index] != null || $noteDates[$index] != '') ? Carbon::parse($noteDates[$index]) : null;
                $updatedAt = ($noteUpdatedAt[$index] != 'null' || $noteUpdatedAt[$index] != null || $noteUpdatedAt[$index] != '') ? $noteUpdatedAt[$index] : null;
                $createdAt = ($noteCreatedAt[$index] != 'null' || $noteCreatedAt[$index] != null || $noteCreatedAt[$index] != '') ? $noteUpdatedAt[$index] : null;

                $creat_note = new Notes;
                $creat_note->object = 'Notes';
                $creat_note->object_id = $lead->id;
                $creat_note->object_type = Leads::class;
                $creat_note->note = $noteValue;
                
                $creat_note->type = $noteType[$index] != 'null' ? $noteType[$index] : null;
                $creat_note->event_date = $noteType[$index] != null && $noteType[$index] != 'note' ? $eventDate : null;

                if ($updatedAt != null) {
                    $creat_note->updated_at = $updatedAt;
                }
                else{
                    $creat_note->updated_at = Carbon::now();
                }

                if ($createdAt != null) {
                    $creat_note->created_at = $createdAt;
                }
                else{
                    $creat_note->created_at = Carbon::now();
                }

                $creat_note->note = $noteValue;
                $creat_note->status = true;
                $creat_note->created_by = auth()->user()->id;
                $creat_note->updated_by = auth()->user()->id;
                $creat_note->save();
            }


            // lead details

            $move_in_date = null;
            if($request->input('move_in_date') != null){
                $move_in_date = Carbon::parse($request->input('move_in_date'))->format('Y-m-d');
            }

            $lead->lead_details()->forceDelete();

            $lead_details = LeadDetails::create([
                'lead_id' => $lead->id,
                'community' => $request->input('community_id'),
                'subcommunity' => $request->input('sub_community_id'),
                'property' => $request->input('tower_id'),
                'budget' => $request->input('budget'),
                'bedroom' => $request->input('beds'),
                'move_in' => $move_in_date, 

                'cheque' => $request->input('cheque'),
                'furnish' => $request->input('furnish'),
                'upgraded' => $request->input('upgraded'),
                'landscape' => $request->input('landscape'),
                'bathroom' => $request->input('bathroom'),
                'kitchen' => $request->input('kitchen'),
                'schools' => $request->input('schools'),
                'pets' => $request->input('pets'),
                'current_home' => $request->input('current_home'),
                'parking' => $request->input('parking'),
                'work_place' => $request->input('work_place'),
                'view' => $request->input('view'),
                'floor' => $request->input('floor'),
                'bua' => $request->input('bua'),
                'plot_size' => $request->input('plot_size'),
                'new_to_dubai' => $request->input('new_to_dubai'),
            ]);

            // Get the updated values after updating
            $updatedValues = $lead->getAttributes();
            unset($updatedValues['updated_at']);
            unset($updatedValues['created_by']);
            unset($updatedValues['updated_by']);
            unset($updatedValues['assigned_date']);
            unset($updatedValues['accepted_date']);
            
            // Log the changes
            $logDetails = [];

            //update contact details 
            if($request->input('title') != $lead->contact->title || $request->input('name') != $lead->contact->name || $request->input('phone') != $lead->contact->phone || $request->input('email') != $lead->contact->email || $request->input('contact_type') != $lead->contact->contact_type){

                $update_contact = Contacts::find($lead->contact->id);

                $originalValuesContact = $update_contact->getOriginal();

                $update_contact->title = $request->input('title');
                $update_contact->name = $request->input('name');
                $update_contact->phone = $request->input('phone');
                $update_contact->email = $request->input('email');
                $update_contact->contact_type = $request->input('contact_type');
                $update_contact->save();

                $updatedValuesContact = $update_contact->getAttributes();
                unset($updatedValuesContact['updated_at']);
                unset($updatedValuesContact['created_by']);
                unset($updatedValuesContact['updated_by']);

                // Log the changes
                $logDetailsContact = [];

                foreach ($updatedValuesContact as $field => $newValue) {
                    $oldValue = $originalValuesContact[$field];
            
                    // Check if the field has changed
                    if ($oldValue != $newValue) {
                        // If the changed field is 'city_id', handle null values
                        $logDetailsContact[] = "$field: $oldValue to $newValue";
                        $logDetails[] = "$field: $oldValue to $newValue";
                    }
                }

                if (!empty($logDetailsContact)) {
                    $logMessageContact = implode(', ', $logDetailsContact);
        
                    activity()
                        ->performedOn($lead->contact)
                        ->causedBy(auth()->user())
                        ->withProperties(['details' => $logMessageContact])
                        ->log('updated');
                }

                $lead->updated_at = Carbon::now();
                $lead->save();
            }

            foreach ($updatedValues as $field => $newValue) {
                $oldValue = $originalValues[$field];
        
                // Check if the field has changed
                if ($oldValue != $newValue) {
                    // If the changed field is 'city_id', handle null values
                    if ($field == 'status_id') {
                        $oldName = $oldValue ? (Statuses::find($oldValue) ? Statuses::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (Statuses::find($newValue) ? Statuses::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Status: $oldName to $newName";
                    }
                    elseif ($field == 'sub_status_id') {
                        $oldName = $oldValue ? (SubStatuses::find($oldValue) ? SubStatuses::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (SubStatuses::find($newValue) ? SubStatuses::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Sub Status: $oldName to $newName";
                    }
                    elseif ($field == 'agent_id') {
                        $oldName = $oldValue ? (User::find($oldValue) ? User::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (User::find($newValue) ? User::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Lead Agent: $oldName to $newName";
                    }
                    elseif ($field == 'source_id') {
                        $oldName = $oldValue ? (Sources::find($oldValue) ? Sources::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (Sources::find($newValue) ? Sources::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Source: $oldName to $newName";
                    }
                    elseif ($field == 'sub_source_id') {
                        $oldName = $oldValue ? (SubSources::find($oldValue) ? SubSources::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (SubSources::find($newValue) ? SubSources::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Sub Source: $oldName to $newName";
                    }
                    elseif ($field == 'campaign_id') {
                        $oldName = $oldValue ? (Campaigns::find($oldValue) ? Campaigns::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (Campaigns::find($newValue) ? Campaigns::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Campaign: $oldName to $newName";
                    }
                    elseif ($field == 'listing_id') {
                        $oldName = $oldValue ? (listings::find($oldValue) ? listings::find($oldValue)->refno : 'empty') : 'empty';
                        $newName = $newValue ? (listings::find($newValue) ? listings::find($newValue)->refno : 'empty') : 'empty';
        
                        $logDetails[] = "Property : $oldName to $newName";
                    }
                    elseif ($field == 'contact_id') {
                        $oldName = $oldValue ? (Contacts::find($oldValue) ? Contacts::find($oldValue)->refno : 'empty') : 'empty';
                        $newName = $newValue ? (Contacts::find($newValue) ? Contacts::find($newValue)->refno : 'empty') : 'empty';
        
                        $logDetails[] = "Contact: $oldName to $newName";
                    }
                    else {
                        $logDetails[] = "$field: $oldValue to $newValue";
                    }
                }
            }

            if (!empty($logDetails)) {
                $logMessage = implode(', ', $logDetails);

                activity()
                    ->performedOn($lead)
                    ->causedBy(auth()->user())
                    ->withProperties(['details' => $logMessage])
                    ->log('updated');
            }

            DB::commit();
            
            return response()->json(['message' => 'Lead updated successfully.']);
        } catch (\Throwable $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            // If it's a validation exception, return the validation errors
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json(['errors' => $e->validator->errors()], 422);
            }

            // Handle the exception as needed
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function acceptAjax(Request $request)
    {
        $lead_id = $request->input('lead_id');

        // Check if any user IDs are provided
        if (empty($lead_id)) {
            return response()->json(['error' => 'No IDs provided for lead accept.']);
        }
        DB::beginTransaction();
        try {

            $lead = Leads::findOrFail($lead_id);

            activity()
                    ->performedOn($lead)
                    ->causedBy(auth()->user())
                    ->withProperties('Lead is accepted.')
                    ->log('updated');


            $accepted_date = Carbon::now()->format('Y-m-d H:i:s');
            // Use the User model to delete users by IDs
            Leads::where('id', $lead->id)->update(['accepted_date' => $accepted_date]);

            DB::commit();

            return response()->json(['message' => 'Lead accepted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['error' => 'Error during lead accept: ' . $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $itemIds = $request->input('item_ids');
        $reason = $request->input('reason');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk delete.']);
        }
        DB::beginTransaction();
        try {

            foreach($itemIds as $itemId){
                $data = Leads::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Lead is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            Leads::whereIn('id', $itemIds)->update(['status_reason' => $reason]);
            Leads::whereIn('id', $itemIds)->delete();

            DB::commit();

            return response()->json(['message' => 'Bulk delete successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['error' => 'Error during bulk delete: ' . $e->getMessage()], 500);
        }
    }

    public function bulkAssign(Request $request){
        $itemIds = $request->input('item_ids');
        $agent = $request->input('formValues')['agent'];
        $reason = $request->input('formValues')['reason'];

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk action.']);
        }

        DB::beginTransaction();
        
        try {

            $get_agent = User::findOrFail($agent);
            foreach($itemIds as $itemId){
                
                $lead = Leads::findOrFail($itemId);
                $oldAgent = $lead->lead_agent ? $lead->lead_agent->name : 'empty';
                
                $newAgent = $get_agent->name;
                $lead->update(['agent_id' => $agent, 'accept_status' => null, 'assigned_date' => date('Y-m-d H:i:s'), 'accepted_date' => null]);

                // Create a note
                $note = 'Lead agent is changed from "'.$oldAgent.'" to "'.$newAgent.'"';

                if ($reason) {
                    $note .= ' and the reason is "('.$reason.')"';
                }

                $note .= ' through bulk action.';

                Notes::create([
                    'object_id' => $lead->id,
                    'object_type' => Leads::class,
                    'note' => $note,
                    'status' => true,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);

                // Log activity
                activity()
                    ->performedOn($lead)
                    ->causedBy(auth()->user())
                    ->withProperties(['note' => $note])
                    ->log('updated');

                //$this->whatsAppService->notify($newAgent->id);
            }

            if($get_agent != null){
                $user = User::findOrFail(auth()->user()->id);
                $subject = 'Leads Assigned To You';
                //$message = 'Hi '. $get_agent->name .', These leads have been assigned to you by '. $user->name;
                $message = null;
                
                $leads = Leads::whereIn('id', $itemIds)->get();
                Mail::to($get_agent->email)->send(new LeadsAssignEmail($subject, $message, $leads, $get_agent->name, $user));
            }

            // Use the model to change status by IDs
            //Listings::whereIn('id', $itemIds)->update(['status_id' => $status_id, 'status_reason' => $reason]);
            DB::commit();
            return response()->json(['success' => 'Bulk assign successful.']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['error' => 'Error during bulk assign: ' . $e], 500);
        }
    }

    public function bulkStatusChange(Request $request)
    {
        $itemIds = $request->input('item_ids');

        $status_id = $request->input('formValues')['status_id'];
        $sub_status_id = $request->input('formValues')['sub_status_id'];
        $reason = $request->input('formValues')['reason'];

        //$status_id = $request->input('status_id');
        // $sub_status_id = $request->input('sub_status_id');
        // $reason = $request->input('reason');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk action.']);
        }

        DB::beginTransaction();

        try {

            $new_status = Statuses::find($status_id)->name;

            $new_sub_status = null;
            if($sub_status_id && $sub_status_id != null){
                $new_sub_status = SubStatuses::find($sub_status_id)->name;
            }

            foreach($itemIds as $itemId){
                $data = Leads::findOrFail($itemId);

                $old_status = $data->status ? $data->status->name : 'empty';
                $note = 'Lead status is changed from '.$old_status.' to '.$new_status;

                if ($sub_status_id && $sub_status_id != null) {
                    $old_sub_status = $data->sub_status ? $data->sub_status->name : 'empty';
                    $note .= ' and sub status from '.$old_sub_status.' to '.$new_sub_status;
                }

                if ($reason) {
                    $note .= ' and the reason is ('.$reason.')';
                }
                

                $note .= ' through bulk action.';
                
                Notes::create([
                    'object_id' => $data->id,
                    'object_type' => Leads::class,
                    'note' => $note,
                    'status' => true,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);
                
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties($note)
                    ->log('updated');
            }

            // Use the model to change status by IDs
            // Leads::whereIn('id', $itemIds)->update(['status_id' => $status_id, 'status_reason' => $reason]);
            // Use the model to change status by IDs
            $updateData = [
                'status_id' => $status_id,
                'status_reason' => $reason,
            ];
            $updateData['sub_status_id'] = $sub_status_id;

            // Conditionally add sub_status_id to updateData
            // if ($sub_status_id !== null) {
            //     $updateData['sub_status_id'] = $sub_status_id;
            // }

            Leads::whereIn('id', $itemIds)->update($updateData);

            DB::commit();
            return response()->json(['success' => 'Bulk status change successful.']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['error' => 'Error during bulk status change: ' . $e->getMessage()], 500);
        }
    }

    public function bulkRestore(Request $request)
    {
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk restore listings.']);
        }

        DB::beginTransaction();
        try {

            // Use the User model to delete users by IDs
            Leads::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = Leads::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Lead is restored through bulk action.')
                    ->log('updated');
            }
            DB::commit();
            return response()->json(['message' => 'Bulk restored successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['message' => 'Error during bulk restoring: ' . $e->getMessage()], 500);
        }
    }


    public function export(Request $request)
    {
        $itemIds = $request->input('item_ids');
        $leads = Leads::with('notes', 'documents', 'campaign', 'contact', 'property', 'lead_details', 'source', 'sub_source', 'lead_agent', 'updated_by_user', 'created_by_user', 'status', 'sub_status');
        // Check if any user IDs are provided
        if (!empty($itemIds)) {
            $leads = $leads->whereIn('id', $itemIds);
        }
        else{
            $validLeads = $this->utilService->valid_leads();

            $archived = $request->input('archivedd');
            $type = $request->input('type');

            $startDate = $request->filters['startDate'];
            $endDate = $request->filters['endDate'];

            $startCreatedDate = $request->filters['startCreatedDate'];
            $endCreatedDate = $request->filters['endCreatedDate'];

            $startEnqDate = $request->filters['startEnqDate'];
            $endEnqDate = $request->filters['endEnqDate'];

            $startAssignedDate = $request->filters['startAssignedDate'];
            $endAssignedDate = $request->filters['endAssignedDate'];

            $startAcceptedDate = $request->filters['startAcceptedDate'];
            $endAcceptedDate = $request->filters['endAcceptedDate'];

            $leads = $leads->when($archived == 'yes', function ($query) use ($archived) {
                if ($archived == 'yes') {
                    $query->onlyTrashed();
                }
            });

            if ($type && $type != null) {
                $leads->where(function ($query) use ($type, $validLeads) {
                    if ($type == 'unassigned') {
                        $query->where('assign_status', 'Unassigned');
                    } 
                    else if ($type == 'active') {
                        $query->whereIn('status_id', $validLeads);
                    }
                    else if ($type == 'closed') {
                        $get_status = Statuses::where('name', 'Deal Closed')->first();
                        if($get_status != null){
                            $query->where('status_id', $get_status->id);
                        }
                    }
                    else if ($type == 'dead') {
                        $get_status = Statuses::where('name', 'Archive')->first();
                        if($get_status != null){
                            $query->where('status_id', $get_status->id);
                        }
                    }
                });
            }
    
            if ($startDate && $endDate) {
                $leads->where(function ($query) use ($startDate, $endDate) {
                    if ($startDate === $endDate) {
                        $query->whereDate('updated_at', $startDate);
                    } else {
                        $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    }
                });
            }
    
            if ($startCreatedDate && $endCreatedDate) {
                $leads->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
                    if ($startCreatedDate === $endCreatedDate) {
                        $query->whereDate('created_at', $startCreatedDate);
                    } else {
                        $query->whereBetween('created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
                    }
                });
            }
    
            if ($startEnqDate && $endEnqDate) {
                $leads->where(function ($query) use ($startEnqDate, $endEnqDate) {
                    if ($startEnqDate === $endEnqDate) {
                        $query->whereDate('created_at', $startEnqDate);
                    } else {
                        $query->whereBetween('created_at', [$startEnqDate . ' 00:00:00', $endEnqDate . ' 23:59:59']);
                    }
                });
            }
    
            if ($startAssignedDate && $endAssignedDate) {
                $leads->where(function ($query) use ($startAssignedDate, $endAssignedDate) {
                    if ($startAssignedDate === $endAssignedDate) {
                        $query->whereDate('assigned_date', $startAssignedDate);
                    } else {
                        $query->whereBetween('assigned_date', [$startAssignedDate . ' 00:00:00', $endAssignedDate . ' 23:59:59']);
                    }
                });
            }
    
            if ($startAcceptedDate && $endAcceptedDate) {
                $leads->where(function ($query) use ($startAcceptedDate, $endAcceptedDate) {
                    if ($startAcceptedDate === $endAcceptedDate) {
                        $query->whereDate('accepted_date', $startAcceptedDate);
                    } else {
                        $query->whereBetween('accepted_date', [$startAcceptedDate . ' 00:00:00', $endAcceptedDate . ' 23:59:59']);
                    }
                });
            }
    
            if ($request->filters['refno'] != null) {
                $leads->where('refno', 'like', '%' . $request->filters['refno'] . '%');
            }
            
            if ($request->filters['status'] != null) {
                $leads->whereHas('status', function ($query) use ($request) {
                    $query->where('name', $request->filters['status']);
                });
            }
    
            if ($request->filters['sub_status'] != null) {
                $leads->whereHas('sub_status', function ($query) use ($request) {
                    $query->where('name', $request->filters['sub_status']);
                });
            }
    
            if ($request->filters['stage'] != null) {
                $leads->where('lead_stage', $request->filters['stage']);
            }
    
            if ($request->filters['client_details'] != null) {
                $leads->whereHas('contact', function ($query) use ($request) {
                    $client_details = $request->filters['client_details'];
                    $query->where('name', 'LIKE', "%$client_details%")
                    ->orWhere('phone', 'LIKE', "%$client_details%")
                    ->orWhere('email', 'LIKE', "%$client_details%")
                    ->orWhere('refno', 'LIKE', "%$client_details%");
                });
            }
    
            if ($request->filters['property'] != null) {
                $leads->whereHas('property', function ($query) use ($request) {
                    $property = $request->filters['property'];
                    $query->where('refno', 'LIKE', "%$property%");
                });
            }
    
            if ($request->filters['campaign'] != null) {
                $leads->whereHas('campaign', function ($query) use ($request) {
                    $query->where('name', $request->filters['campaign']);
                });
            }
    
            if ($request->filters['lead_agent'] != null) {
                $leads->whereHas('lead_agent', function ($query) use ($request) {
                    $query->where('name', $request->filters['lead_agent']);
                });
            }
    
            if ($request->filters['source'] != null) {
                $leads->whereHas('source', function ($query) use ($request) {
                    $query->where('name', $request->filters['source']);
                });
            }
    
            if ($request->filters['sub_source'] != null) {
                $leads->whereHas('sub_source', function ($query) use ($request) {
                    $query->where('name', $request->filters['sub_source']);
                });
            }
    
            if ($request->filters['created_by'] != null) {
                $leads->whereHas('created_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['created_by']);
                });
            }
    
            if ($request->filters['updated_by'] != null) {
                $leads->whereHas('updated_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['updated_by']);
                });
            }
        }
        $leads = $leads->latest('updated_at')->get();

        // Export the data using the Excel facade
        $filename = 'leads_export_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        $export = new LeadsExport($leads); // Use your Export class

        // Generate the Excel file
        $file = Excel::download($export, $filename)->getFile();

        // Get the file content
        $fileContent = file_get_contents($file);

        // Encode file content to base64
        $base64File = base64_encode($fileContent);

        // Return JSON response with file and filename
        return response()->json(['file' => $base64File, 'filename' => $filename]);
    }

    public function import(){
        $this->authorize('leads_import');
        return view('admin.leads.import');
    }

    public function importPost(Request $request){

        // Validate the uploaded file
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $output = [];
		$skipped_count = 0;
		$processed = 0;

        $rowCount = 0;
        $errorMessages = [];
        $successMessages = [];
        $response = [];

        if ($request->hasFile('file')) {

            // Get the uploaded file
            $file = $request->file('file');
            // Read the Excel file
            $data = Excel::toArray([], $file);
            // Get the first sheet
            $data = $data[0];
            // Check if required columns are present
            $required_columns = [
                'Source',
                'Status',
                'Client Name',
                'Client Mobile'
            ];

            $columns = array_filter($data[0]);
            unset($data[0]);

            if (array_diff($required_columns, $columns)) {
                $missing_columns = implode(', ', array_diff($required_columns, $columns));
                return back()->with('error', 'The uploaded file is missing the following columns: ' . $missing_columns . '. Please check the sample file and try again.');
            }
            
            foreach ($data as $row) {
                foreach ($required_columns as $field) {
                    if (empty($row[array_search($field, $columns)])) {
                        $output[] = 'Row ' . array_search($row, $data) . ' is missing a value for "' . $field . '". This row will be skipped.';
                        $skipped_count++;

                        // remove the row from the data array
                        unset($data[array_search($row, $data)]);
                    }
                }
            }
            
            if (count($data) > 0) {
                // Fetch necessary data from database
                $agents = User::pluck('email', 'id')->toArray();
                $sourceTypes = Sources::pluck('name', 'id')->toArray();

                // Skip the first row (header row)
                array_shift($data);

                foreach ($data as $rowIndex => $row) { 
                    // Fetch data from the row using column names
                    $name = $row[array_search('Client Name', $columns)];
                    $mobile = $row[array_search('Client Mobile', $columns)];
                    //$email = $row[array_search('Client Email', $columns)];

                    $emailIndex = array_search('Client Email', $columns);
                    $email = null;

                    if ($emailIndex !== false) {
                        $email = $row[$emailIndex];
                    }

                    $agent = $row[array_search('Agent', $columns)];

                    $get_agent = User::where('email', $agent)->first();
                    $agentId = $get_agent ? $get_agent->id : null;
                    
                    $campaignName = $row[array_search('Campaign', $columns)] ?? null;
                    $source = $row[array_search('Source', $columns)];
                    
                    $source_id = array_search(trim($source), $sourceTypes) ?? null;

                    $status = $row[array_search('Status', $columns)];
                    $getStatus = Statuses::where('name', 'Not Yet Contacted')->first();
                    $status_id = $getStatus ? $getStatus->id : null;
                    // Match campaign and update assignment_pointer if needed
                    $campaign = Campaigns::with('users')->where('name', trim($campaignName))->first();
                    if ($campaign) {
                        $agentsArray = json_decode($campaign->users, true);

                        if ($agentsArray) {
                            if (isset($agentsArray[$campaign->assignment_pointer])) {
                                $agentId = $agentsArray[$campaign->assignment_pointer] ? $agentsArray[$campaign->assignment_pointer]['id'] : null;

                                $campaign->update([
                                    'assignment_pointer' => $campaign->assignment_pointer + 1,
                                ]);
                            } elseif (isset($agentsArray[0])) {
                                $agentId = $agentsArray[0] ? $agentsArray[0]['id'] : null;

                                $campaign->update([
                                    'assignment_pointer' => 1,
                                ]);
                            } else {
                                $agentId = null; // Or set to default agent ID
                                $campaign->update([
                                    'assignment_pointer' => 0,
                                ]);
                            }
                        }
                    }

                    // Upsert the contact
                    // Create or update the contact
                    $contact = contacts::where('phone', $mobile)->first();
                    if ($contact) {
                        $updates = ['name' => $name];
    
                        // Conditionally add email to the update array if it's not null
                        if ($email != null || $email != '') {
                            $updates['email'] = $email;
                        }

                        $contact->update($updates);
                    } else {
                        $contact = contacts::create(['name' => $name, 'phone' => $mobile, 'email' => $email, 'refno' => $this->utilService->get_next_refkey_contact()]);
                    }

                    // Upsert the lead
                    $lead = new Leads;
                    $lead->refno = $this->utilService->get_next_refkey_lead();
                    $lead->source_id = $source_id;
                    $lead->agent_id = $agentId;
                    $lead->contact_id = $contact->id;
                    $lead->status_id = $status_id;
                    $lead->lead_stage = 'Cold';
                    $lead->import_source = 'excel';
                    //$lead->sub_status_id = 6; // Default sub status
                    $lead->campaign_id = $campaign ? $campaign->id : null;
                    $lead->assign_status = $agentId ? 'Assigned' : 'Unassigned';
                    $lead->accept_status = $agentId ? 'Accepted' : null;
                    $lead->enquiry_date = $row['Enquiry Date'] ?? Carbon::now();

                    $lead->updated_at = Carbon::now();
                    $lead->created_at = Carbon::now();
                    $lead->save();

                    activity()
                    ->performedOn($lead)
                    ->causedBy(auth()->user())
                    ->withProperties('Lead imported from Excel file.')
                    ->log('created');
                    
                    $rowCount++;

                    // Add note if available
                    if (!empty($row['Notes'])) {

                        $creat_note = new Notes;
                        $creat_note->object = 'Notes';
                        $creat_note->object_id = $lead->id;
                        $creat_note->object_type = Leads::class;
                        $creat_note->note = $row['Notes'];
                        
                        $creat_note->type = 'note';
                        $creat_note->event_date = null;

                        $creat_note->updated_at = Carbon::now();
                        $creat_note->created_at = Carbon::now();

                        $creat_note->status = true;
                        $creat_note->created_by = auth()->user()->id;
                        $creat_note->updated_by = auth()->user()->id;
                        $creat_note->save();
                    }

                    $output[] = "Row " . array_search($row, $data) . " - Lead imported successfully with refno: " . $lead->refno;
                }
            }
        }
        else{
            return back()->with('error', 'Select the file please.');
        }

        //return $output;

        return back()->with('success', 'Leads imported successfully.')->with('output', $output)->with('skipped', $skipped_count);
    }
}
