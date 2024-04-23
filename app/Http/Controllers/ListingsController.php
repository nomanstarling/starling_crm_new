<?php

namespace App\Http\Controllers;

use App\Models\Listings;
use App\Models\listings_amenities;
use App\Models\amenities;

use App\Models\listing_portals;
use App\Models\owners;
use App\Models\developers;
use App\Models\countries;
use App\Models\cities;
use App\Models\sub_communities;
use App\Models\communities;
use App\Models\towers;
use App\Models\Statuses;
use App\Models\Notes;
use App\Models\media_gallery;
use App\Models\property_category;
use App\Models\User;
use App\Models\property_type;
use App\Models\listing_occupancies;
use App\Models\project_status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ListingsExport;
use Spatie\Valuestore\Valuestore;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Mail;
use App\Mail\ListingBulkEmail;
use Illuminate\Support\Facades\File;
use \claviska\SimpleImage;
use Illuminate\Support\Facades\Validator;


class ListingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->settings = Valuestore::make(config('settings.path'));
        $this->shortName = $this->settings->get('short_name');
    }
    
    public function index(Request $request)
    {
        $this->authorize('listing_view');
        if($request->query('archived')) {
            $this->authorize('listing_view_archived');
        }
        $firstDate = Listings::min('created_at');
        $statuses = Statuses::orderBy('name')->where('type', 'Listings')->get();
        $users = User::orderBy('name')->get();
        $property_types = property_type::orderBy('name')->get();
        $project_statuses = project_status::orderBy('name')->get();
        $occupancies = listing_occupancies::orderBy('name')->get();
        $developers = developers::orderBy('name')->get();
        $categories = property_category::orderBy('name')->get();
        $cities = cities::orderBy('name')->get();
        $portals = listing_portals::orderBy('name')->get();
        return view('admin.listings.index', compact('statuses', 'portals', 'cities', 'firstDate', 'developers', 'categories', 'users', 'property_types', 'occupancies', 'project_statuses'));
    }
    
    public function getList(Request $request)
    {
        $searchTerm = $request->input('q');
        $searchTermId = $request->input('id');
        $query = listings::with('community', 'sub_community', 'tower')->orderBy('refno');

        if ($searchTerm) {
            $query->where('refno', 'like', '%' . $searchTerm . '%')
                ->orWhere('external_refno', 'like', '%' . $searchTerm . '%');
        }
        if($searchTermId){
            $query->where('id', $searchTermId);
        }

        $listings = $query->limit(100)->get();
        return response()->json(['results' => $listings]);
    }

    public function getListings(Request $request)
    {
        $archived = $request->input('archivedd');
        
        $p_for = $request->input('for');
        $p_for = $p_for == 'rent' ? 'rent' : ($p_for == 'sale' ? 'sale' : null);

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $startCreatedDate = $request->input('startCreatedDate');
        $endCreatedDate = $request->input('endCreatedDate');

        $startPublishedDate = $request->input('startPublishedDate');
        $endPublishedDate = $request->input('endPublishedDate');

        $listings = Listings::when($p_for !== null || $archived == 'yes', function ($query) use ($p_for, $archived) {
            if ($p_for !== null) {
                $query->where('property_for', $p_for);
            }
        
            if ($archived == 'yes') {
                $query->onlyTrashed();
            }
        });

        if ($startDate && $endDate) {
            $listings->where(function ($query) use ($startDate, $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate('updated_at', $startDate);
                } else {
                    $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            });
        }

        if ($startCreatedDate && $endCreatedDate) {
            $listings->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
                if ($startCreatedDate === $endCreatedDate) {
                    $query->whereDate('created_at', $startCreatedDate);
                } else {
                    $query->whereBetween('created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
                }
            });
        }

        if ($startPublishedDate && $endPublishedDate) {
            $listings->where(function ($query) use ($startPublishedDate, $endPublishedDate) {

                if ($startPublishedDate === $endPublishedDate) {
                    $query->whereDate('published_at', $startPublishedDate);
                } else {
                    $query->whereBetween('published_at', [$startPublishedDate . ' 00:00:00', $endPublishedDate . ' 23:59:59']);
                }
            });
        }

        if ($request->input('owner_name') != null) {
            $listings->whereHas('owner', function ($query) use ($request) {
                $ownerName = $request->input('owner_name');
                $query->where('name', 'LIKE', "%$ownerName%")
                ->orWhere('phone', 'LIKE', "%$ownerName%")
                ->orWhere('email', 'LIKE', "%$ownerName%")
                ->orWhere('refno', 'LIKE', "%$ownerName%");
            });
        }

        if ($request->input('status') != null) {
            $listings->whereHas('status', function ($query) use ($request) {
                $query->where('name', $request->input('status'));
            });
        }

        if ($request->input('refno') != null) {
            $listings->where('refno', 'like', '%' . $request->input('refno') . '%')
                ->orWhere('external_refno', 'like', '%' . $request->input('refno') . '%');
        }

        if ($request->input('property_for') != null) {
            $listings->where('property_for', $request->input('property_for'));
        }

        if ($request->input('property_type') != null) {
            $listings->whereHas('prop_type', function ($query) use ($request) {
                $query->where('name', $request->input('property_type'));
            });
        }

        if ($request->input('unit_no') != null) {
            // limited to only current user
            if (!auth()->user()->hasRole('Super Admin')) {
                $listings->where('agent_id', auth()->user()->id);
            }
            $listings->where('unit_no', 'like', '%' . $request->input('unit_no') . '%');
        }

        if ($request->input('community') != null) {
            $listings->whereHas('community', function ($query) use ($request) {
                $query->where('name', trim($request->input('community')));
            });
        }

        if ($request->input('sub_community') != null) {
            $listings->whereHas('sub_community', function ($query) use ($request) {
                $query->where('name', $request->input('sub_community'));
            });
        }

        if ($request->input('tower') != null) {
            $listings->whereHas('tower', function ($query) use ($request) {
                $query->where('name', $request->input('tower'));
            });
        }

        if ($request->input('portal') != null) {
            $listings->whereHas('portals', function ($query) use ($request) {
                $query->where('name', $request->input('portal'));
            });
        }            

        if ($request->input('beds') != null) {
            $listings->where('beds', 'like', '%' . $request->input('beds') . '%');
        }

        if ($request->input('baths') != null) {
            $listings->where('baths', 'like', '%' . $request->input('baths') . '%');
        }

        if ($request->input('price') != null) {
            $priceFilter = str_replace([' ', ','], '', $request->input('price'));
            $listings->where('price', 'like', '%' . $priceFilter . '%');
        }

        if ($request->input('bua') != null) {
            $listings->where('bua', 'like', '%' . $request->input('bua') . '%');
        }

        if ($request->input('rera_permit') != null) {
            $listings->where('rera_permit', 'like', '%' . $request->input('rera_permit') . '%');
        }

        if ($request->input('furnished') != null) {
            $listings->where('furnished', $request->input('furnished'));
        }

        if ($request->input('category') != null) {
            $listings->whereHas('category', function ($query) use ($request) {
                $query->where('name', $request->input('category'));
            });
        }

        if ($request->input('marketing_agent') != null) {
            $listings->whereHas('marketing_agent', function ($query) use ($request) {
                $query->where('name', $request->input('marketing_agent'));
            });
        }

        if ($request->input('listing_agent') != null) {
            $listings->whereHas('listing_agent', function ($query) use ($request) {
                $query->where('name', $request->input('listing_agent'));
            });
        }

        if ($request->input('created_by') != null) {
            $listings->whereHas('created_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('created_by'));
            });
        }

        if ($request->input('updated_by') != null) {
            $listings->whereHas('updated_by_user', function ($query) use ($request) {
                $query->where('name', $request->input('updated_by'));
            });
        }

        if ($request->input('project_status') != null) {
            $listings->whereHas('project_status', function ($query) use ($request) {
                $query->where('name', $request->input('project_status'));
            });
        }

        if ($request->input('plot_area') != null) {
            $listings->where('plot_area', 'like', '%' . $request->input('plot_area') . '%');
        }

        if ($request->input('occupancy') != null) {
            $listings->whereHas('occupancy', function ($query) use ($request) {
                $query->where('name', $request->input('occupancy'));
            });
        }

        if ($request->input('cheques') != null) {
            $listings->where('cheques', 'like', '%' . $request->input('cheques') . '%');
        }

        if ($request->input('developer') != null) {
            $listings->whereHas('developer', function ($query) use ($request) {
                $query->where('name', $request->input('developer'));
            });
        }


        $totalRecords = $listings->count(); // Fetch the total count

        $perPage = $request->input('length', 10); // Number of records per page

        // $listings = $towers->latest('updated_at')
        // ->with('country', 'city', 'community', 'sub_community') // Eager load country and city relationships
        // ->skip(($request->input('start', 0) / $perPage) * $perPage)
        // ->take($perPage)
        // ->get();
        
        
        
        $listings = $listings->latest('updated_at')
            ->with('notes', 'documents', 'owner', 'portals', 'prop_type', 'community', 'sub_community', 'tower', 'marketing_agent', 'listing_agent', 'project_status', 'occupancy', 'category', 'developer', 'updated_by_user', 'created_by_user', 'status')
            ->skip(($request->input('start', 0) / $perPage) * $perPage)
            ->take($perPage)->get()
            ->map(function ($listing) {
                // Include additional information for related models
                return array_merge($listing->toArray(), [
                    'owner_image' => $listing->owner ? $listing->owner->profileImage() : '', // Assuming profileImage() returns the image URL
                    'marketing_agent_image' => $listing->marketing_agent ? $listing->marketing_agent->profileImage() : '',
                    'listing_agent_image' => $listing->listing_agent ? $listing->listing_agent->profileImage() : '',
                    'portals_info' => $listing->portals->map(function ($portal) {
                        return [
                            'name' => $portal->name,
                            'portal_logo' => $portal->logoImage(),
                        ];
                    })->toArray(),
                    // Add more as needed
                ]);
            });

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $listings,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);
        // return response()->json(['listings' => $listings]);
    }

    public function import(){
        $this->authorize('listing_import');

        return view('admin.listings.import');
    }

    public function importPost(Request $request){
        $response = [];
        $skippedData = [];
        $processedData = [];
        $errors = [];
        $createdCount = 0;
        $updatedCount = 0;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $validator = Validator::make(
                ['file' => $file],
                ['file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );

            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid file format'], 400);
            }

            $data = \Excel::toArray([], $file);
            $rows = $data[0]; // Assuming the first sheet contains data
            $count = 1;

            // Assuming the first row contains column headers
            $columns = $rows[0];

            // Remove the first row (column headers) from the $rows array
            array_shift($rows);

            $required_columns =
			[
				'Reference',
				'State',
				'Property Type',
				'Property For',
				'Category',
				'Location',
				'Agent'
			];

            if (empty($errors)) {
                foreach ($rows as $key => $row) {

                    if ($key != 0) {
                        $count++;
    
                        $skipRow = false;
                        $reasons = [];
    
                        $ref_number = trim($row[0]);
                        $ext_ref_number = trim($row[1]);
                        $status = trim($row[2]);
                        if($status == 'Approved'){
                            $status = 4;
                        }
                        else{
                            $status = 2;
                        }
                        $property_type = trim($row[3]);
                        if($property_type == 'Residential'){
                            $property_type = 1;
                        }
                        else{
                            $property_type = 2;
                        }
                        $property_for = trim($row[4]);
                        if($property_for == 'Rental' || $property_for == 'Rent'){
                            $property_for = 'rent';
                        }
                        else{
                            $property_for = 'sale';
                        }
                        $unit_no = trim($row[5]);
                        $category = trim($row[6]);
                        if($category == 'Hotel & Hotel Apartment'){
                            $category = 'Hotel Apartment'; 
                        }
                        if($category == 'Residential Full Floor'){
                            $category = 'Full Floor'; 
                        }
                        if($category == 'Land Residential'){
                            $category = 'Land'; 
                        }
                        if($category == 'Office'){
                            $category = 'Office Space'; 
                        }
                        $get_category = property_type::where('name', $category)->first();
                        $category = $get_category ? $get_category->id : null;

                        $city = trim($row[7]);
                        $community = trim($row[8]);
                        $subCommunity = trim($row[9]);
                        $building = trim($row[10]);

                        $location = trim($row[7]);
    
                        // $locationParts = array_map('trim', explode(',', $location));
                        // $building = null;
                        // $subCommunity = null;
                        // $community = null;
                        // $city = null;
    
                        // $countLocation = count($locationParts);
                        // if ($countLocation == 1) {
                        //     $building = null;
                        //     $subCommunity = null;
                        //     $community = null;
                        //     $city = (string)$locationParts[0]; // The city name
                        // }
                        
                        // if ($countLocation == 2) {
                        //     $building = null;
                        //     $subCommunity = null;
                        //     $community = (string)$locationParts[0];
                        //     $city = (string)$locationParts[1]; // The city name
                        // }
                        
                        // if ($countLocation == 3) {
                        //     $building = null;
                        //     $subCommunity = (string)$locationParts[0];
                        //     $community = (string)$locationParts[1];
                        //     $city = (string)$locationParts[2]; // The city name
                        // }
                        
                        // if ($countLocation == 4) {
                        //     $building = (string)$locationParts[0];
                        //     $subCommunity = (string)$locationParts[1];
                        //     $community = (string)$locationParts[2];
                        //     $city = (string)$locationParts[3]; // The city name
                        // }
                        
                        $get_city = cities::where('name', trim($city))->first();
                        $city = $get_city ? $get_city->id : null;
    
                        $get_community = communities::where('city_id', $city)->where('name', trim($community))->first();
                        if($get_city && $get_community){
                            $community = $get_community->id;
                        }
                        else{
                            if($community != null || $community != ''){
                                $create_community = new communities;
                                $create_community->city_id = $city;
                                $create_community->country_id = 234;
                                $create_community->name = trim($community);
                                $create_community->save();
                                $community = $create_community->id;
                            }
                        }
    
                        $get_sub_community = sub_communities::where('city_id', $city)->where('community_id', $community)->where('name', trim($subCommunity))->first();
                        if($get_city && $get_community && $get_sub_community){
                            $subCommunity = $get_sub_community->id;
                        }
                        else{
                            if($subCommunity != null || $subCommunity != ''){
                                $create_sub_community = new sub_communities;
                                $create_sub_community->city_id = $city;
                                $create_sub_community->country_id = 234;
                                $create_sub_community->community_id = $community;
                                $create_sub_community->name = trim($subCommunity);
                                $create_sub_community->save();
                                $subCommunity = $create_sub_community->id;
                            }
                        }
    
                        $get_building = towers::where('city_id', $city)->where('community_id', $community)->where('sub_community_id', $subCommunity)->where('name', trim($building))->first();
                        if($get_city && $get_community && $get_sub_community && $get_building){
                            $building = $get_building->id;
                        }
                        else{
                            if($building != null || $building != ''){
                                $create_building = new towers;
                                $create_building->city_id = $city;
                                $create_building->country_id = 234;
                                $create_building->community_id = $community;
                                $create_building->sub_community_id = $subCommunity;
                                $create_building->name = trim($building);
                                $create_building->save();
                                $building = $create_building->id;
                            }
                        }
    
                        $beds = trim($row[11]);
                        $baths = trim($row[12]);
                        $bua = trim($row[13]);
                        $bua = preg_replace('/[^0-9.]/', '', $bua);
                        $bua = floor($bua);
                        $price = trim($row[14]);
                        $price = preg_replace('/[^0-9.]/', '', $price);
                        $price = floor($price);
    
                        $created_by = trim($row[15]);
                        $created_by = preg_replace('/\s+/', ' ', $created_by); // Replace consecutive spaces with a single space
                        $get_created_by = User::where('name', $created_by)->first();
                        $created_by = $get_created_by ? $get_created_by->id : null;
    
                        $agent = trim($row[16]);
                        $agent = preg_replace('/\s+/', ' ', $agent); // Replace consecutive spaces with a single space
                        $get_agent = User::where('name', $agent)->first();
                        $agent = $get_agent ? $get_agent->id : null;
                        $owner = trim($row[17]);
                        $owner = preg_replace('/\s+/', ' ', $owner); // Replace consecutive spaces with a single space
                        $owner_mobile = trim($row[18]);
                        $owner_email = trim($row[19]);
                        $get_owner = owners::where('name', $owner);
                        if($owner_mobile && $owner_mobile != null ){
                            $get_owner = $get_owner->orWhere('phone', $owner_mobile);
                        }
                        if($owner_email && $owner_email != null ){
                            $get_owner = $get_owner->orWhere('email', $owner_email);
                        }
                        $get_owner = $get_owner->first();
                        
                        if($get_owner){
                            $owner = $get_owner->id;
                        }
                        else if(trim($owner) != null){
                            $owner_create = owners::create([
                                'refno' => $this->getNextRefNoOwner(),
                                'name' => $owner,
                                'phone' => $owner_mobile,
                                'email' => $owner_email,
                                'created_by' => auth()->user()->id,
                            ]);
                            $owner = $owner_create->id;
                        }
                        else{
                            $owner = null;
                        }
                        
                        $listed_date = trim($row[20]);
                        if($listed_date != null || $listed_date != ''){
                            $listed_date = Carbon::createFromFormat('M d, Y', $listed_date);
                        }
                        else{
                            $listed_date = null;
                        }
                        $expiry_date = trim($row[22]);
                        
                        if($expiry_date != null || $expiry_date != ''){
                            //$expiry_date = Carbon::createFromFormat('M j, Y', $expiry_date);
                            $expiry_date = Carbon::createFromFormat('M d, Y', $expiry_date);
                        }
                        else{
                            $expiry_date = null;
                        }
                        $permit_no = trim($row[23]);
                        $unit_type = trim($row[24]);
                        $floor = trim($row[26]);
                        
                        $plot_area = trim($row[27]);
                        $plot_area = preg_replace('/[^0-9.]/', '', $plot_area);
                        $plot_area = floor($plot_area);
    
                        $view = trim($row[28]);
                        $furnished = trim($row[29]);
                        $furnished = trim($furnished) == 'Fully Furnished' ? 'Furnished' : trim($furnished);
                        $parking = trim($row[30]);
                        $developer = trim($row[31]);
                        $frequency = trim($row[33]);
                        $title = trim($row[38]);
                        
                        $desc = trim($row[40]);
                        $desc = nl2br($desc);
    
                        $portals = trim($row[41]);
                        $features = trim($row[42]);
                        $amenities = trim($row[43]);

                        if($property_for == null && $category == null && $community == null && $subCommunity == null){
                            continue; // Skip to the next iteration of the loop
                        }

                        //echo 'portal start: <br>';
    
                        $next_available = trim($row[48]);
                        if($next_available != null || $next_available != ''){
                            $next_available = Carbon::createFromFormat('M d, Y', $next_available);
                        }
                        else{
                            $next_available = null;
                        }
    
                        $occupancy = trim($row[49]);
                        $get_occupancy = listing_occupancies::where('name', $occupancy)->first();
                        $occupancy = $get_occupancy ? $get_occupancy->id : null;
    
                        $poa = trim($row[56]);
                        $poa = $poa == 'Yes' ? true : false;
                        //echo $created_by.' | community: '.$community.' | '.$owner.'<br>';
    
                        $propFor = $property_for == 'sale' ? 'S' : 'R';
                        
                        $get_listing = Listings::where('property_for', $property_for)->where('property_type', $category)->where('community_id', $community)->where('sub_community_id', $subCommunity);
                        if($building != null){
                            $get_listing = $get_listing->where('tower_id', $building);
                        }
                        if($unit_no != null){
                            $get_listing = $get_listing->where('unit_no', $unit_no);
                        }
                        $get_listing = $get_listing->first();
                        $created_at = $listed_date != null ? $listed_date : now();
                        
                        if($get_listing){
                            $listing = Listings::findOrFail($get_listing->id);
    
                            $oldPropertyFor = $listing->property_for;
                            $oldRefno = $listing->refno;
                            $new_refno = $oldRefno;
                            $oldDirectory = public_path('storage/uploads/listings/' . $oldRefno);
    
                            if ($oldPropertyFor !== $property_for) {
                                $parts = explode('-', $oldRefno);
                                $parts[1] = $propFor;
                                $new_refno = implode('-', $parts);
    
                                //rename the directory
                                
                                if (is_dir($oldDirectory)) {
                                    $newDirectory = public_path('storage/uploads/listings/' . $new_refno);
                                    if (rename($oldDirectory, $newDirectory)) {
    
                                    }       
                                } 
                            }
    
                            $listing->update([
                                'refno' => $new_refno,
                                'external_refno' => $ext_ref_number,
                                'property_for' => $property_for,
                                'category_id' => $property_type,
                                'property_type' => $category,
                                'city_id' => $city,
                                'community_id' => $community,
                                'sub_community_id' => $subCommunity,
                                'tower_id' => $building,
                                'title' => $title,
                                'desc' => $desc,
                                'agent_id' => $agent,
                                'marketing_agent_id' => $agent,
                                'price' => $price,
                                'frequency' => $property_for == 'rent' && trim($frequency) == null ? 'Yearly' : $frequency,
                                'unit_no' => $unit_no,
                                'bua' => $bua,
                                'plot_area' => $plot_area,
                                'rera_permit' => $permit_no,
                                'parking' => $parking,
                                'beds' => $beds,
                                'baths' => $baths,
                                'furnished' => $furnished,
                                'owner_id' => $owner,
                                'occupancy_id' => $occupancy,
                                'next_availability_date' => $next_available,
                                'view' => $view,
                                'status_id' => $status,
                                'poa' => $poa,
                                'created_by' => $created_by,
                                'created_at' => $created_at
                            ]);
                            $updatedCount++;
                        }
                        else{
                            $refno = $this->getNextRefNo($propFor);
                            
                            $listing = Listings::create([
                                'refno' => $refno,
                                'old_refno' => $ref_number,
                                'external_refno' => $ext_ref_number,
                                'property_for' => $property_for,
                                'category_id' => $property_type,
                                'property_type' => $category,
                                'city_id' => $city,
                                'community_id' => $community,
                                'sub_community_id' => $subCommunity,
                                'tower_id' => $building,
                                'title' => $title,
                                'desc' => $desc,
                                'agent_id' => $agent,
                                'marketing_agent_id' => $agent,
                                'price' => $price,
                                'frequency' => $frequency,
                                'unit_no' => $unit_no,
                                'bua' => $bua,
                                'plot_area' => $plot_area,
                                'rera_permit' => $permit_no,
                                'parking' => $parking,
                                'beds' => $beds,
                                'baths' => $baths,
                                'furnished' => $furnished,
                                'owner_id' => $owner,
                                'occupancy_id' => $occupancy,
                                'next_availability_date' => $next_available,
                                'view' => $view,
                                'status_id' => $status,
                                'poa' => $poa,
                                'created_by' => $created_by,
                                'published_at' => null,
                                'created_at' => $created_at
                            ]);
                            $createdCount++;
                        }
    
                        $amenities_array = [];
                        $features_array = [];
                        $portals_array = [];
                        $featuress = [];
    
                        if ($portals) {
                            $portals = explode(',', $portals);
                            $portalIds = [];
    
                            foreach ($portals as $portalName) {
                                //echo $portalName.'<br>';
                                $portal = listing_portals::where('name', trim($portalName))->first();
                                //echo $portal->name."<br>";
                                if ($portal) {
                                    $portalIds[] = $portal->id;
                                }
                            }

                            //return $portalIds;
                            if ($portalIds) {
                                $listing->portals()->sync($portalIds);
                            }
                        }
    
                        if ($amenities) {
                            $amenities = explode(',', $amenities);
                            $amenities_array = array_merge($amenities_array, $amenities);
                        }
                        if ($features) {
                            $features = explode(',', $features);
                            $features_array = array_merge($features_array, $features);
                        }
    
                        if($amenities_array || $features_array){
                            $featuress = array_merge($amenities_array, $features_array);
                            $featureIds = [];
    
                            foreach ($featuress as $featureValue) {
                                // Check if the feature already exists in the database
                                $existingFeature = amenities::where('name', trim($featureValue))->first();
                            
                                // If the feature doesn't exist, create a new one
                                if (!$existingFeature) {
                                    $newFeature = new amenities();
                                    $newFeature->name = trim($featureValue);
                                    $newFeature->code = $this->generateFeatureCode(trim($featureValue));
                                    $newFeature->type = 'Private';
                                    $newFeature->status = true;
                                    $newFeature->save();
                                    $featureIds[] = $newFeature->id;
                                }
                                else {
                                    // Collect the ID of the existing feature
                                    $featureIds[] = $existingFeature->id;
                                }
                            }
    
                            if ($featureIds) {
                                $listing->amenities()->sync($featureIds);
                            }
                        }
    
                    }
                }
            }

            $response['message'] = "Import completed.";
            $response['processed'] = $processedData;
            $response['skipped'] = $skippedData;
            $response['created_count'] = $createdCount;
            $response['updated_count'] = $updatedCount;
        } else {
            $response['error'] = 'No file uploaded';
        }

        return back()->with('success', 'Listings imported successfully.')->with('output', $response);
    }

    private function getNextRefNoOwner(){
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

    function generateFeatureCode($name)
    {
        // Split the name into words
        $words = explode(' ', $name);

        // Initialize an array to store the first letter of each word
        $initials = [];

        // Loop through each word and get the first letter
        foreach ($words as $word) {
            $initials[] = strtoupper(substr($word, 0, 1));
        }

        // Join the initials to create the code
        $code = implode('', $initials);

        return $code;
    }

    public function searchRefno(Request $request)
    {
        $refno = $request->input('refno');
        $orderByColumn = 'updated_at';

        // Use your model to search for the record based on refno
        $matchingRecord = Listings::where('refno', $refno)->first();

        if ($matchingRecord) {
            $position = Listings::where($orderByColumn, '>', $matchingRecord->$orderByColumn)
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

    public function getPortalCounts(Request $request)
    {
        $property_for = $request->input('p_for');
        $archived = $request->input('archived');
        $portalCountsQuery = listing_portals::query();
        
        
        // Get portal counts with or without property_for filter
        $portalCounts = $portalCountsQuery->withCount(['listings' => function ($query) use ($property_for, $archived, $request) {
            // If property_for is provided, further filter listings by property_for
            if ($property_for !== null) {
                $query->where('property_for', $property_for);
            }
            if ($archived !== null && $archived == 'yes') {
                $query->onlyTrashed();
            }

            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            $startCreatedDate = $request->input('startCreatedDate');
            $endCreatedDate = $request->input('endCreatedDate');

            $startPublishedDate = $request->input('startPublishedDate');
            $endPublishedDate = $request->input('endPublishedDate');

            if ($startDate && $endDate) {
                $query->where(function ($query) use ($startDate, $endDate) {
                    if ($startDate === $endDate) {
                        $query->whereDate('listings.updated_at', $startDate);
                    } else {
                        $query->whereBetween('listings.updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    }
                });
            }
    
            if ($startCreatedDate && $endCreatedDate) {
                $query->where(function ($query) use ($startCreatedDate, $endCreatedDate) {
                    if ($startCreatedDate === $endCreatedDate) {
                        $query->whereDate('listings.created_at', $startCreatedDate);
                    } else {
                        $query->whereBetween('listings.created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
                    }
                });
            }

            if ($startPublishedDate && $endPublishedDate) {
                $query->where(function ($query) use ($startPublishedDate, $endPublishedDate) {
    
                    if ($startPublishedDate === $endPublishedDate) {
                        $query->whereDate('listings.published_at', $startPublishedDate);
                    } else {
                        $query->whereBetween('listings.published_at', [$startPublishedDate . ' 00:00:00', $endPublishedDate . ' 23:59:59']);
                    }
                });
            }
    
            // if ($publishedDate) {
            //     $query->where(function ($query) use ($publishedDate) {
            //         $query->whereDate('published_at', $publishedDate);
            //     });
            // }
    
            if ($request->input('owner_name') != null) {
                $query->whereHas('owner', function ($query) use ($request) {
                    $ownerName = $request->input('owner_name');
                    $query->where('name', 'LIKE', "%$ownerName%")
                    ->orWhere('phone', 'LIKE', "%$ownerName%")
                    ->orWhere('email', 'LIKE', "%$ownerName%")
                    ->orWhere('refno', 'LIKE', "%$ownerName%");
                });
            }
    
            if ($request->input('status') != null) {
                $query->whereHas('status', function ($query) use ($request) {
                    $query->where('name', $request->input('status'));
                });
            }
    
            if ($request->input('refno') != null) {
                $query->where('refno', 'like', '%' . $request->input('refno') . '%')
                    ->orWhere('external_refno', 'like', '%' . $request->input('refno') . '%');
            }
    
            if ($request->input('property_for') != null) {
                $query->where('property_for', $request->input('property_for'));
            }
    
            if ($request->input('property_type') != null) {
                $query->whereHas('prop_type', function ($query) use ($request) {
                    $query->where('name', $request->input('property_type'));
                });
            }
    
            if ($request->input('unit_no') != null) {
                $query->where('unit_no', 'like', '%' . $request->input('unit_no') . '%');
            }
    
            if ($request->input('community') != null) {
                $query->whereHas('community', function ($query) use ($request) {
                    $query->where('name', $request->input('community'));
                });
            }
    
            if ($request->input('sub_community') != null) {
                $query->whereHas('sub_community', function ($query) use ($request) {
                    $query->where('name', $request->input('sub_community'));
                });
            }
    
            if ($request->input('tower') != null) {
                $query->whereHas('tower', function ($query) use ($request) {
                    $query->where('name', $request->input('tower'));
                });
            }
    
            if ($request->input('portal') != null) {
                $query->whereHas('portals', function ($query) use ($request) {
                    $query->where('name', $request->input('portal'));
                });
            }            
    
            if ($request->input('beds') != null) {
                $query->where('beds', 'like', '%' . $request->input('beds') . '%');
            }
    
            if ($request->input('baths') != null) {
                $query->where('baths', 'like', '%' . $request->input('baths') . '%');
            }
    
            if ($request->input('price') != null) {
                $priceFilter = str_replace([' ', ','], '', $request->input('price'));
                $query->where('price', 'like', '%' . $priceFilter . '%');
            }
    
            if ($request->input('bua') != null) {
                $query->where('bua', 'like', '%' . $request->input('bua') . '%');
            }
    
            if ($request->input('rera_permit') != null) {
                $query->where('rera_permit', 'like', '%' . $request->input('rera_permit') . '%');
            }
    
            if ($request->input('furnished') != null) {
                $query->where('furnished', $request->input('furnished'));
            }
    
            if ($request->input('category') != null) {
                $query->whereHas('category', function ($query) use ($request) {
                    $query->where('name', $request->input('category'));
                });
            }
    
            if ($request->input('marketing_agent') != null) {
                $query->whereHas('marketing_agent', function ($query) use ($request) {
                    $query->where('name', $request->input('marketing_agent'));
                });
            }
    
            if ($request->input('listing_agent') != null) {
                $query->whereHas('listing_agent', function ($query) use ($request) {
                    $query->where('name', $request->input('listing_agent'));
                });
            }
    
            if ($request->input('created_by') != null) {
                $query->whereHas('created_by_user', function ($query) use ($request) {
                    $query->where('name', $request->input('created_by'));
                });
            }
    
            if ($request->input('updated_by') != null) {
                $query->whereHas('updated_by_user', function ($query) use ($request) {
                    $query->where('name', $request->input('updated_by'));
                });
            }
    
            if ($request->input('project_status') != null) {
                $query->whereHas('project_status', function ($query) use ($request) {
                    $query->where('name', $request->input('project_status'));
                });
            }
    
            if ($request->input('plot_area') != null) {
                $query->where('plot_area', 'like', '%' . $request->input('plot_area') . '%');
            }
    
            if ($request->input('occupancy') != null) {
                $query->whereHas('occupancy', function ($query) use ($request) {
                    $query->where('name', $request->input('occupancy'));
                });
            }
    
            if ($request->input('cheques') != null) {
                $query->where('cheques', 'like', '%' . $request->input('cheques') . '%');
            }
    
            if ($request->input('developer') != null) {
                $query->whereHas('developer', function ($query) use ($request) {
                    $query->where('name', $request->input('developer'));
                });
            }

        }])->get();
        
        $formattedCounts = $portalCounts->pluck('listings_count', 'name');

    
        

        // $portalCounts = listing_portals::withCount('listings')->get();
        // $formattedCounts = $portalCounts->pluck('listings_count', 'name');

        return response()->json($formattedCounts);
    }

    public function export(Request $request)
    {
        $itemIds = $request->input('item_ids');
        $listings = Listings::with('notes', 'documents', 'owner', 'portals', 'city', 'prop_type', 'community', 'sub_community', 'tower', 'marketing_agent', 'listing_agent', 'project_status', 'occupancy', 'category', 'developer', 'updated_by_user', 'created_by_user', 'status');
        // Check if any user IDs are provided
        if (!empty($itemIds)) {
            $listings = $listings->whereIn('id', $itemIds);
        }
        else{
            $p_for = $request->input('for');
            $p_for = $p_for == 'sale' ? 'sale' : ($p_for == 'rent' ? 'rent' : null);

            $startDate = $request->filters['startDate'];
            $endDate = $request->filters['endDate'];
            $startCreatedDate = $request->filters['startCreatedDate'];
            $endCreatedDate = $request->filters['endCreatedDate'];

            $startPublishedDate = $request->filters['startPublishedDate'];
            $endPublishedDate = $request->filters['endPublishedDate'];

            $listings = $listings->when($p_for !== null, function ($query) use ($p_for) {
                return $query->where('property_for', $p_for);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                //return $query->whereBetween('updated_at', [$startDate, $endDate]);
                return $query->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->when($startCreatedDate && $endCreatedDate, function ($query) use ($startCreatedDate, $endCreatedDate) {
                //return $query->whereBetween('created_at', [$startCreatedDate, $endCreatedDate]);
                return $query->whereBetween('created_at', [$startCreatedDate . ' 00:00:00', $endCreatedDate . ' 23:59:59']);
            })
            ->when($startPublishedDate && $endPublishedDate, function ($query) use ($startPublishedDate, $endPublishedDate) {
                //return $query->whereBetween('created_at', [$startCreatedDate, $endCreatedDate]);
                //return $query->whereDate('published_at', $publishedDate);
                return $query->whereBetween('published_at', [$startPublishedDate . ' 00:00:00', $endPublishedDate . ' 23:59:59']);
            });

            if ($request->filters['owner_name'] != null) {
                $listings->whereHas('owner', function ($query) use ($request) {
                    $ownerName = $request->filters['owner_name'];
                    $query->where('name', 'LIKE', "%$ownerName%")
                    ->orWhere('phone', 'LIKE', "%$ownerName%")
                    ->orWhere('email', 'LIKE', "%$ownerName%")
                    ->orWhere('refno', 'LIKE', "%$ownerName%");
                });
            }

            if ($request->filters['status'] != null) {
                $listings->whereHas('status', function ($query) use ($request) {
                    $query->where('name', $request->filters['status']);
                });
            }

            if ($request->filters['refno'] != null) {
                $listings->where('refno', 'like', '%' . $request->filters['refno'] . '%')
                    ->orWhere('external_refno', 'like', '%' . $request->filters['refno'] . '%');
            }

            if ($request->filters['property_for'] != null) {
                $listings->where('property_for', $request->filters['property_for']);
            }

            if ($request->filters['property_type'] != null) {
                $listings->whereHas('prop_type', function ($query) use ($request) {
                    $query->where('name', $request->filters['property_type']);
                });
            }

            if ($request->filters['unit_no'] != null) {
                $listings->where('unit_no', 'like', '%' . $request->filters['unit_no'] . '%');
            }

            if ($request->filters['community'] != null) {
                $listings->whereHas('community', function ($query) use ($request) {
                    $query->where('name', $request->filters['community']);
                });
            }

            if ($request->filters['sub_community'] != null) {
                $listings->whereHas('sub_community', function ($query) use ($request) {
                    $query->where('name', $request->filters['sub_community']);
                });
            }

            if ($request->filters['tower'] != null) {
                $listings->whereHas('tower', function ($query) use ($request) {
                    $query->where('name', $request->filters['tower']);
                });
            }

            // if ($request->filters['portal'] != null) {
            //     $listings->whereHas('portal', function ($query) use ($request) {
            //         $query->where('name', $request->filters['portal']);
            //     });
            // }

            if ($request->filters['portal'] != null) {
                $listings->whereHas('portals', function ($query) use ($request) {
                    $query->where('name', $request->filters['portal']);
                });
            }            

            if ($request->filters['beds'] != null) {
                $listings->where('beds', 'like', '%' . $request->filters['beds'] . '%');
            }

            if ($request->filters['baths'] != null) {
                $listings->where('baths', 'like', '%' . $request->filters['baths'] . '%');
            }

            if ($request->filters['price'] != null) {
                $priceFilter = str_replace([' ', ','], '', $request->filters['price']);
                $listings->where('price', 'like', '%' . $priceFilter . '%');
            }

            if ($request->filters['bua'] != null) {
                $listings->where('bua', 'like', '%' . $request->filters['bua'] . '%');
            }

            if ($request->filters['rera_permit'] != null) {
                $listings->where('rera_permit', 'like', '%' . $request->filters['rera_permit'] . '%');
            }

            if ($request->filters['furnished'] != null) {
                $listings->where('furnished', $request->filters['furnished']);
            }

            if ($request->filters['category'] != null) {
                $listings->whereHas('category', function ($query) use ($request) {
                    $query->where('name', $request->filters['category']);
                });
            }

            if ($request->filters['marketing_agent'] != null) {
                $listings->whereHas('marketing_agent', function ($query) use ($request) {
                    $query->where('name', $request->filters['marketing_agent']);
                });
            }

            if ($request->filters['listing_agent'] != null) {
                $listings->whereHas('listing_agent', function ($query) use ($request) {
                    $query->where('name', $request->filters['listing_agent']);
                });
            }

            if ($request->filters['created_by'] != null) {
                $listings->whereHas('created_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['created_by']);
                });
            }

            if ($request->filters['updated_by'] != null) {
                $listings->whereHas('updated_by_user', function ($query) use ($request) {
                    $query->where('name', $request->filters['updated_by']);
                });
            }

            if ($request->filters['project_status'] != null) {
                $listings->whereHas('project_status', function ($query) use ($request) {
                    $query->where('name', $request->filters['project_status']);
                });
            }

            if ($request->filters['plot_area'] != null) {
                $listings->where('plot_area', 'like', '%' . $request->filters['plot_area'] . '%');
            }

            if ($request->filters['occupancy'] != null) {
                $listings->whereHas('occupancy', function ($query) use ($request) {
                    $query->where('name', $request->filters['occupancy']);
                });
            }

            if ($request->filters['cheques'] != null) {
                $listings->where('cheques', 'like', '%' . $request->filters['cheques'] . '%');
            }

            if ($request->filters['developer'] != null) {
                $listings->whereHas('developer', function ($query) use ($request) {
                    $query->where('name', $request->filters['developer']);
                });
            }

            if (!auth()->user()->hasRole('Super Admin')) {
                $listings->where('agent_id', auth()->user()->id);
            }
        }
        $listings = $listings->latest('updated_at')->get();

        // Export the data using the Excel facade
        $filename = 'listings_export_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        $export = new ListingsExport($listings); // Use your Export class

        // Generate the Excel file
        $file = Excel::download($export, $filename)->getFile();

        // Get the file content
        $fileContent = file_get_contents($file);

        // Encode file content to base64
        $base64File = base64_encode($fileContent);

        // Return JSON response with file and filename
        return response()->json(['file' => $base64File, 'filename' => $filename]);
    }

    public function edit(Listings $listing)
    {
        // Eager load documents
        $loadedListing = $listing->load('documents', 'images', 'amenities', 'portals', 'notes.created_by_user', 'created_by_user', 'updated_by_user');
        //$loadedListing['profile_image'] = $loadedListing->profileImage();

        // Fetch activities associated with the user
        $activities = Activity::with('causer')->where(function ($query) use ($loadedListing) {
                $query->where('subject_id', $loadedListing->id)
                    ->where('subject_type', get_class($loadedListing));
            })
            ->orderBy('created_at', 'desc')
            ->where('properties', '!=', null)
            ->where('properties', '!=', '[]')
            ->get();

        // Manually add file URL to each document
        $loadedListing->documents->each(function ($document) {
            $document->file_url = asset('public/storage/' . $document->path);
        });

        $loadedListing->images->each(function ($image) {
            $newpath = str_ireplace('/images/', '/images/550x375/', $image->path);
            $image->file_url = asset('public/storage/' . $newpath);
        });

        $loadedListing['activities'] = $activities;
        //$amenityNames = $loadedListing->amenities->pluck('amenity.name');
        $amenityNames = $loadedListing->amenities->pluck('name');

        return response()->json(['listing' => $loadedListing, 'amenityNames' => $amenityNames]);
    }

    private function getNextRefNo($type){
        $latestListing = Listings::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestListing) {
            $latestRefNo = $latestListing->refno;
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            $nextNumericPart = $numericPart + 1;
            $newRefNo = $this->shortName . '-'.$type.'-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            $newRefNo = $this->shortName . '-'.$type.'-001';
        }
        return $newRefNo;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $property_status = $request->input('status');
            $p_status = Statuses::find($property_status);
            
            // return $amenitiesData
            // $rules = [
            //     'external_refno' => 'required|unique:listings',
            //     'category_id' => 'required',
            //     'property_type' => 'required',
            //     'property_for' => 'required',
            //     'city_id' => 'required',
            //     'community_id' => 'required',
            //     'sub_community_id' => 'required',
            //     'title' => 'required',
            //     'desc' => 'required',
            //     'agent_id' => 'required',
            //     'price' => 'required',
            //     'frequency' => 'required',
            //     'unit_no' => 'required',
            //     'bua' => 'required',
            //     'plot_no' => 'required',
            //     'plot_area' => 'required',
            //     'rera_permit' => 'required',
            //     'parking' => 'required',
            //     'beds' => 'required',
            //     'baths' => 'required',
            //     'furnished' => 'required',
            //     'project_status_id' => 'required',
            //     'owner_id' => 'required',
            //     'occupancy_id' => 'required',
            //     'next_availability_date' => 'required',
            //     'cheques' => 'required',
            //     'portals' => 'required',
            // ];

            // if ($p_status->name == 'Prospect') {
            //     $rules = [
            //         'title' => 'required',
            //         'desc' => 'required',
            //     ];
            // } elseif ($p_status == 'Available - Published') {
                
            //     if($request->input('property_for') == 'rent'){
            //         $rules = [
            //             'external_refno' => 'required|unique:listings',
            //             'category_id' => 'required',
            //             'property_type' => 'required',
            //             'property_for' => 'required',
            //             'city_id' => 'required',
            //             'community_id' => 'required',
            //             'sub_community_id' => 'required',
            //             'title' => 'required',
            //             'desc' => 'required',
            //             'agent_id' => 'required',
            //             'price' => 'required',
            //             'frequency' => 'required',
            //             'unit_no' => 'required',
            //             'bua' => 'required',
            //             'plot_no' => 'required',
            //             'plot_area' => 'required',
            //             'rera_permit' => 'required',
            //             'parking' => 'required',
            //             'beds' => 'required',
            //             'baths' => 'required',
            //             'furnished' => 'required',
            //             'project_status_id' => 'required',
            //             'owner_id' => 'required',
            //             'occupancy_id' => 'required',
            //             'next_availability_date' => 'required',
            //             'cheques' => 'required',
            //             'portals' => 'required',
            //         ];
            //     }
            //     else{
            //         $rules = [
            //             'external_refno' => 'required|unique:listings',
            //             'category_id' => 'required',
            //             'property_type' => 'required',
            //             'property_for' => 'required',
            //             'city_id' => 'required',
            //             'community_id' => 'required',
            //             'sub_community_id' => 'required',
            //             'title' => 'required',
            //             'desc' => 'required',
            //             'agent_id' => 'required',
            //             'price' => 'required',
            //             'frequency' => 'required',
            //             'unit_no' => 'required',
            //             'bua' => 'required',
            //             'plot_no' => 'required',
            //             'plot_area' => 'required',
            //             'rera_permit' => 'required',
            //             'parking' => 'required',
            //             'beds' => 'required',
            //             'baths' => 'required',
            //             'furnished' => 'required',
            //             'project_status_id' => 'required',
            //             'owner_id' => 'required',
            //             'occupancy_id' => 'required',
            //             'next_availability_date' => 'required',
            //             'portals' => 'required',
            //         ];
            //     }
            // }
            // elseif ($p_status == 'Available - Off-Market') {
            //     $rules = [
            //         'title' => 'required',
            //         'desc' => 'required',
            //     ];
            // }
            // elseif ($p_status == 'Coming to Market') {
            //     $rules = [
            //         'title' => 'required',
            //         'desc' => 'required',
            //     ];
            // }

            $baseRules = [
                'external_refno' => 'required|unique:listings',
                'category_id' => 'required',
                'property_type' => 'required',
                'property_for' => 'required',
                'city_id' => 'required',
                'community_id' => 'required',
                'sub_community_id' => 'required',
                'title' => 'required',
                'desc' => 'required',
                'agent_id' => 'required',
                'price' => 'required',
                'frequency' => 'required',
                'unit_no' => 'required',
                'bua' => 'required',
                'plot_no' => 'required',
                'plot_area' => 'required',
                'rera_permit' => 'required',
                'parking' => 'required',
                'beds' => 'required',
                'baths' => 'required',
                'furnished' => 'required',
                'project_status_id' => 'required',
                'owner_id' => 'required',
                'occupancy_id' => 'required',
                'next_availability_date' => 'required',
                'portals' => 'required',
                'cheques' => 'required'
            ];
            
            if ($p_status->name == 'Prospect' || $p_status->name == 'Available - Off-Market' || $p_status->name == 'Coming to Market') {
                $rules = [
                    'title' => 'required',
                    'status' => 'required',
                    'city_id' => 'required',
                    'community_id' => 'required',
                    'sub_community_id' => 'required',
                    'price' => 'required',
                    'unit_no' => 'required',
                    'bua' => 'required',
                    'beds' => 'required',
                    'owner_id' => 'required',
                ];
            } elseif ($p_status->name == 'Available - Published') {
                if ($request->input('property_for') == 'rent') {
                    $rules = $baseRules;
                } else {
                    unset($baseRules['cheques']);
                    unset($baseRules['next_availability_date']);
                    unset($baseRules['frequency']);
                    $rules = $baseRules;
                }
            }
            
            // Merge base rules with additional rules if needed
            if (isset($rules)) {
                $rules = array_merge($baseRules, $rules);
            } else {
                $rules = $baseRules;
            }

            $request->validate($rules);

            // $photo = null;
            // if ($request->hasFile('avatar')) {
            //     $photo = $request->file('avatar')->store('uploads/owners/images', 'public');
            // }
            $next_availability_date = null;
            if($request->input('next_availability_date') != null){
                $next_availability_date = Carbon::parse($request->input('next_availability_date'))->format('Y-m-d');
            }
            $propFor = $request->input('property_for') == 'sale' ? 'S' : 'R';

            $refnoType = $this->shortName.'-'.$propFor.'-';

            if ($request->has('lead_gen')) {
                $lead_gen = true;
            } else {
                $lead_gen = false;
            }

            if ($request->has('poa')) {
                $poa = true;
            } else {
                $poa = false;
            }

            $property_price = $request->input('price');
            // Remove separators and non-numeric characters
            $property_price = preg_replace('/[^\d]/', '', $property_price);
            $refno = $this->getNextRefNo($propFor);

            //return $refno;
            // Create the data
            $listing = Listings::create([
                'refno' => $refno,
                'external_refno' => $request->input('external_refno'),
                'property_for' => $request->input('property_for'),
                'category_id' => $request->input('category_id'),
                'property_type' => $request->input('property_type'),
                'city_id' => $request->input('city_id'),
                'community_id' => $request->input('community_id'),
                'sub_community_id' => $request->input('sub_community_id'),
                'tower_id' => $request->input('tower_id'),
                'title' => $request->input('title'),
                'desc' => $request->input('desc'),
                'agent_id' => $request->input('agent_id'),
                'marketing_agent_id' => $request->input('marketing_agent_id'),
                'price' => $property_price,
                'frequency' => $request->input('frequency'),
                'unit_no' => $request->input('unit_no'),
                'bua' => $request->input('bua'),
                'plot_no' => $request->input('plot_no'),
                'plot_area' => $request->input('plot_area'),
                'rera_permit' => $request->input('rera_permit'),
                'parking' => $request->input('parking'),
                'beds' => $request->input('beds'),
                'baths' => $request->input('baths'),
                'furnished' => $request->input('furnished'),
                'project_status_id' => $request->input('project_status_id'),
                'owner_id' => $request->input('owner_id'),
                'occupancy_id' => $request->input('occupancy_id'),
                'next_availability_date' => $next_availability_date,
                'cheques' => $request->input('cheques'),
                'view' => $request->input('view'),
                'video_link' => $request->input('video_link'),
                'live_tour_link' => $request->input('live_tour_link'),
                'developer_id' => $request->input('developer_id'),

                'status_id' => $request->input('status'),
                'lead_gen' => $lead_gen,

                'poa' => $poa,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'published_at' => $request->input('status') == 4 ? Carbon::now() : null,
            ]);

            if ($request->hasFile('file')) {
                $files = $request->file('file');
        
                foreach ($files as $index => $file) {
        
                    $filename = $file->store('uploads/listings/'.$refno.'/documents', 'public');
                    $fileType = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();
                    $size = $file->getSize();
        
                    $document_note = new media_gallery;
                    $document_note->object = 'document';
                    $document_note->alt = $request->input('document_name')[$index];
                    $document_note->object_id = $listing->id;
                    $document_note->object_type = Listings::class;
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

                $noteDates = array_map(function ($value) {
                    return ($value == 'null') ? null : $value;
                }, $noteDates);
        
                if (is_array($noteValues) && count($noteValues) > 0) {
                    foreach ($noteValues as $index => $note) {
                        $eventDate = ($noteDates[$index] != 'null' || $noteDates[$index] != null || $noteDates[$index] != '') ? Carbon::parse($noteDates[$index]) : null;

                        Notes::create([
                            'object' => 'Notes',
                            'object_id' => $listing->id,
                            'object_type' => Listings::class,
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
            
            $listingId = $listing->id;

            $amenitiesData = json_decode($request->input('amenities'), true);
            if ($amenitiesData) {
                $amenityIds = [];
                foreach ($amenitiesData as $amenityData) {
                    $amenityValue = $amenityData['value'];
                    $amenity = amenities::where('name', $amenityValue)->first();
                    if (!$amenity) {
                        $amenity = amenities::create(['name' => $amenityValue]);
                    }
                    $amenityIds[] = $amenity->id;
                }

                $listing->amenities()->attach($amenityIds);
            }

            // foreach ($amenityIds as $amenityId) {
            //     listings_amenities::create([
            //         'listing_id' => $listingId,
            //         'amenity_id' => $amenityId,
            //     ]);
            // }

            if ($request->has('portals') && is_array($request->input('portals'))) {
                $listing->portals()->attach($request->input('portals'));
            }

            if ($request->hasFile('images')) {
                $images = $request->file('images');
        
                foreach ($images as $index => $file) {
                    
                    $structure = [];
                    $structure[] = 'uploads/listings/' . $refno . '/images/';
                    $structure[] = 'uploads/listings/' . $refno . '/images/550x375/';
                    $structure[] = 'uploads/listings/' . $refno . '/images/768x535/';

                    // Make sure the structure exists
                    foreach ($structure as $dir) {
                        $this->touchDirectory($dir);
                    }
                    
                    $filename = $file->store('uploads/listings/'.$refno.'/images', 'public');
                    $fileType = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();
                    $storedFilename = basename($filename);
                    $size = $file->getSize();
        
                    $image_gallery = new media_gallery;
                    $image_gallery->object = 'image';
                    $image_gallery->object_id = $listing->id;
                    $image_gallery->object_type = Listings::class;
                    $image_gallery->path = $filename;
                    $image_gallery->file_name = $originalName;
                    $image_gallery->file_type = $fileType;
                    $image_gallery->status = true;
                    $image_gallery->featured = false;
                    $image_gallery->created_by = auth()->user()->id;
                    $image_gallery->updated_by = auth()->user()->id;
                    $image_gallery->save();
                    
                    // resize images
                    $storagePath = public_path('storage');
                    $directoryPath = 'uploads/listings/' . $refno . '/images';

                    $originalImagePath = $storagePath . '/' . $file;
                    $image = new SimpleImage($file);

                    list($width, $height) = getimagesize($file);

                    $image->thumbnail(768, 535)->toFile($storagePath.'/'.$directoryPath.'/768x535/'.$storedFilename);
                    $image->thumbnail(550, 375)->toFile($storagePath.'/'.$directoryPath.'/550x375/'.$storedFilename);
                }
            }

            // // Get the IDs of existing media associated with the listing
            // $existingMediaIds = $listing->media()->pluck('id')->toArray();

            // // Attach the existing media to the current listing
            // $listing->media()->attach($existingMediaIds);

            activity()
                ->performedOn($listing)
                ->causedBy(auth()->user())
                ->withProperties('Listing created.')
                ->log('created');
            
            DB::commit();

            return response()->json(['message' => 'Listing created successfully']);
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
            $property_status = $request->input('status');
            $p_status = Statuses::find($property_status);
            $baseRules = [
                'external_refno' => 'required|unique:listings',
                'category_id' => 'required',
                'property_type' => 'required',
                'property_for' => 'required',
                'city_id' => 'required',
                'community_id' => 'required',
                'sub_community_id' => 'required',
                'title' => 'required',
                'desc' => 'required',
                'agent_id' => 'required',
                'price' => 'required',
                'frequency' => 'required',
                'unit_no' => 'required',
                'bua' => 'required',
                'plot_no' => 'required',
                'plot_area' => 'required',
                'rera_permit' => 'required',
                'parking' => 'required',
                'beds' => 'required',
                'baths' => 'required',
                'furnished' => 'required',
                'project_status_id' => 'required',
                'owner_id' => 'required',
                'occupancy_id' => 'required',
                'next_availability_date' => 'required',
                'portals' => 'required',
                'cheques' => 'required'
            ];
            $rules = [];
            
            if ($p_status->name == 'Prospect' || $p_status->name == 'Available - Off-Market' || $p_status->name == 'Coming to Market') {
                $rules = [
                    'title' => 'required',
                    'status' => 'required',
                    'city_id' => 'required',
                    'community_id' => 'required',
                    'sub_community_id' => 'required',
                    'price' => 'required',
                    'unit_no' => 'required',
                    'bua' => 'required',
                    'beds' => 'required',
                    'owner_id' => 'required',
                ];
            } elseif ($p_status->name == 'Available - Published') {
                if ($request->input('property_for') == 'rent') {
                    $rules = $baseRules;
                } else {
                    unset($baseRules['cheques']);
                    unset($baseRules['next_availability_date']);
                    unset($baseRules['frequency']);
                    $rules = $baseRules;
                }
            }
            
            // Merge base rules with additional rules if needed
            // if (isset($rules)) {
            //     $rules = array_merge($baseRules, $rules);
            // } else {
            //     $rules = $baseRules;
            // }

            $request->validate($rules);

            // Find the Listing
            $listing = Listings::findOrFail($id);

            // Retrieve the existing documents
            $existingDocuments = $listing->documents;
            $originalValues = $listing->getOriginal();

            $next_availability_date = $listing->dob;
            if($request->input('next_availability_date') != null){
                $next_availability_date = Carbon::parse($request->input('next_availability_date'))->format('Y-m-d');
            }

            $propFor = $request->input('property_for') == 'sale' ? 'S' : 'R';
            $oldPropertyFor = $listing->property_for;
            $oldRefno = $listing->refno;

            $new_refno = $oldRefno;

            $oldDirectory = public_path('storage/uploads/listings/' . $oldRefno);

            if ($oldPropertyFor !== $request->input('property_for')) {
                $parts = explode('-', $oldRefno);
                $parts[1] = $propFor;
                $new_refno = implode('-', $parts);

                //rename the directory
                
                if (is_dir($oldDirectory)) {
                    $newDirectory = public_path('storage/uploads/listings/' . $new_refno);
                    if (rename($oldDirectory, $newDirectory)) {

                    }       
                } 
            }

            $refnoType = $this->shortName.'-'.$propFor.'-';

            if ($request->has('lead_gen')) {
                $leadgen = true;
            } else {
                $leadgen = false;
            }

            if ($request->has('poa')) {
                $poa = true;
            } else {
                $poa = false;
            }

            $property_price = $request->input('price');
            // Remove separators and non-numeric characters
            $property_price = preg_replace('/[^\d]/', '', $property_price);

            // Update the Owner
            $listing->update([
                'refno' => $new_refno,
                'external_refno' => $request->input('external_refno'),
                'property_for' => $request->input('property_for'),
                'category_id' => $request->input('category_id'),
                'property_type' => $request->input('property_type'),
                'city_id' => $request->input('city_id'),
                'community_id' => $request->input('community_id'),
                'sub_community_id' => $request->input('sub_community_id'),
                'tower_id' => $request->input('tower_id'),
                'title' => $request->input('title'),
                'desc' => $request->input('desc'),
                'agent_id' => $request->input('agent_id'),
                'price' => $property_price,
                'frequency' => $request->input('frequency'),
                'unit_no' => $request->input('unit_no'),
                'bua' => $request->input('bua'),
                'plot_no' => $request->input('plot_no'),
                'plot_area' => $request->input('plot_area'),
                'rera_permit' => $request->input('rera_permit'),
                'parking' => $request->input('parking'),
                'beds' => $request->input('beds'),
                'baths' => $request->input('baths'),
                'furnished' => $request->input('furnished'),
                'project_status_id' => $request->input('project_status_id'),
                'owner_id' => $request->input('owner_id'),
                'occupancy_id' => $request->input('occupancy_id'),
                'marketing_agent_id' => $request->input('marketing_agent_id'),
                'next_availability_date' => $next_availability_date,
                'cheques' => $request->input('cheques'),
                'view' => $request->input('view'),
                'video_link' => $request->input('video_link'),
                'live_tour_link' => $request->input('live_tour_link'),
                'developer_id' => $request->input('developer_id'),
                'status_id' => $request->input('status'),
                'lead_gen' => $leadgen,
                'poa' => $poa,
                'updated_by' => auth()->user()->id,
                'published_at' => $request->input('status') == 4 ? Carbon::now() : $listing->published_at,
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
                    // Check if property_for has changed
                    if ($oldPropertyFor !== $request->input('property_for')) {
                        $doc_path = str_replace($oldRefno, $new_refno, $mediaGallery->path);
                    }

                    // Update the alt field with the corresponding document_name
                    if ($mediaGallery) {
                        $mediaGallery->update(['alt' => $documentNames[$index], 'path' => $doc_path]);
                    }
                }

                media_gallery::whereNotIn('id', $documentIds)->where('object', 'document')->where('object_type', Listings::class)->where('object_id', $listing->id)->delete();
            }
            else{
                media_gallery::where('object', 'document')->where('object_type', Listings::class)->where('object_id', $listing->id)->delete();
            }

            // insert new documents
            if ($request->hasFile('file')) {
                
                $files = $request->file('file');
        
                foreach ($files as $index => $file) {
        
                    $filename = $file->store('uploads/listings/'.$new_refno.'/documents', 'public');
                    $fileType = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();
                    $size = $file->getSize();
                    //return $filename;
        
                    $document_note = new media_gallery;
                    $document_note->object = 'document';
                    $document_note->alt = $request->input('document_name')[$index];
                    $document_note->object_id = $listing->id;
                    $document_note->object_type = Listings::class;
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


            // images startes from here

            // Update images in media_gallery
            if ($request->has('image_id')) {
                $image_ids = $request->input('image_id');
                $sort_orders = $request->input('sort_order');
                $is_floorplans = $request->input('is_floorplan');
                $is_watermarks = $request->input('is_watermark');
                
                foreach ($image_ids as $index => $image_id) {
                    // Find the corresponding media_gallery record by image id
                    $mediaGallery = media_gallery::find($image_id);

                    $image_path_r = $mediaGallery->path;
                    // Check if property_for has changed
                    if ($oldPropertyFor !== $request->input('property_for')) {
                        $image_path_r = str_replace($oldRefno, $new_refno, $mediaGallery->path);
                    }

                    // Update the gallery
                    if ($mediaGallery) {
                        $mediaGallery->update(['sort_order' => $sort_orders[$index], 'floor_plan' => $is_floorplans[$index], 'watermark' => $is_watermarks[$index], 'path' => $image_path_r]);
                    }
                }

                media_gallery::whereNotIn('id', $image_ids)->where('object', 'image')->where('object_type', Listings::class)->where('object_id', $listing->id)->delete();
            }
            else{
                media_gallery::where('object', 'image')->where('object_type', Listings::class)->where('object_id', $listing->id)->delete();
            }
            
            if ($request->hasFile('images')) {
                $images = $request->file('images');
        
                foreach ($images as $index => $file) {
        
                    // $filename = $file->store('uploads/listings/'.$new_refno.'/images', 'public');
                    // $fileType = $file->getClientOriginalExtension();
                    // $originalName = $file->getClientOriginalName();
                    // $size = $file->getSize();


                    $structure = [];
                    $structure[] = 'uploads/listings/' . $new_refno . '/images/';
                    $structure[] = 'uploads/listings/' . $new_refno . '/images/550x375/';
                    $structure[] = 'uploads/listings/' . $new_refno . '/images/768x535/';

                    // Make sure the structure exists
                    foreach ($structure as $dir) {
                        $this->touchDirectory($dir);
                    }
                    
                    $filename = $file->store('uploads/listings/'.$new_refno.'/images', 'public');
                    $fileType = $file->getClientOriginalExtension();
                    $originalName = $file->getClientOriginalName();
                    $storedFilename = basename($filename);
                    $size = $file->getSize();
        
                    $image_gallery = new media_gallery;
                    $image_gallery->object = 'image';
                    $image_gallery->object_id = $listing->id;
                    $image_gallery->object_type = Listings::class;
                    $image_gallery->path = $filename;
                    $image_gallery->file_name = $originalName;
                    $image_gallery->file_type = $fileType;
                    $image_gallery->status = true;
                    $image_gallery->featured = false;
                    $image_gallery->sort_order = 100;
                    $image_gallery->floor_plan = false;
                    $image_gallery->watermark = false;
                    $image_gallery->created_by = auth()->user()->id;
                    $image_gallery->updated_by = auth()->user()->id;
                    $image_gallery->save();
                    
                    // resize images
                    $storagePath = public_path('storage');
                    $directoryPath = 'uploads/listings/' . $new_refno . '/images';

                    $originalImagePath = $storagePath . '/' . $file;
                    $image = new SimpleImage($file);

                    list($width, $height) = getimagesize($file);

                    $image->thumbnail(768, 535)->toFile($storagePath.'/'.$directoryPath.'/768x535/'.$storedFilename);
                    $image->thumbnail(550, 375)->toFile($storagePath.'/'.$directoryPath.'/550x375/'.$storedFilename);
                }
            }

            // images ends here

            // Synchronize Notes
            //$noteValues = $request->input('note_values', []);

            $noteValues = $request->input('note_values', []);
            $noteType = $request->input('note_types');
            $noteDates = $request->input('note_dates');
            $noteUpdatedAt = $request->input('note_updated_at');
            $noteCreatedAt = $request->input('note_created_at');

            // Convert 'null' strings to null values in $noteUpdatedAt array
            $noteDates = array_map(function ($value) {
                return ($value == 'null') ? null : $value;
            }, $noteDates);

            $noteUpdatedAt = array_map(function ($value) {
                return ($value == 'null') ? null : $value;
            }, $noteUpdatedAt);

            $noteCreatedAt = array_map(function ($value) {
                return ($value == 'null') ? null : $value;
            }, $noteCreatedAt);

            // Delete all existing notes for this owner
            $listing->notes()->forceDelete();
            // Save the received notes
            foreach ($noteValues as $index => $noteValue) {

                $eventDate = ($noteDates[$index] != 'null' || $noteDates[$index] != null || $noteDates[$index] != '') ? Carbon::parse($noteDates[$index]) : null;
                $updatedAt = ($noteUpdatedAt[$index] != 'null' || $noteUpdatedAt[$index] != null || $noteUpdatedAt[$index] != '') ? $noteUpdatedAt[$index] : null;
                $createdAt = ($noteCreatedAt[$index] != 'null' || $noteCreatedAt[$index] != null || $noteCreatedAt[$index] != '') ? $noteUpdatedAt[$index] : null;

                $creat_note = new Notes;
                $creat_note->object = 'Notes';
                $creat_note->object_id = $listing->id;
                $creat_note->object_type = Listings::class;
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

                $creat_note->status = true;
                $creat_note->created_by = auth()->user()->id;
                $creat_note->updated_by = auth()->user()->id;
                $creat_note->save();
            }

            // Get the selected portal IDs from the request
            $selectedPortals = $request->input('portals', []);

            // Sync the selected portal IDs with the listing's portals
            $listing->portals()->sync($selectedPortals);

            $amenitiesData = json_decode($request->input('amenities'), true);
            if ($amenitiesData) {
                $amenityIds = [];
                foreach ($amenitiesData as $amenityData) {
                    $amenityValue = $amenityData['value'];
                    $amenity = amenities::where('name', $amenityValue)->first();
                    if (!$amenity) {
                        $amenity = amenities::create(['name' => $amenityValue]);
                    }
                    $amenityIds[] = $amenity->id;
                }

                $listing->amenities()->sync($amenityIds);
            }
            else{
                $listing->amenities()->detach();
            }

            // Get the updated values after updating
            $updatedValues = $listing->getAttributes();
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
                    if ($field == 'category_id') {
                        $oldName = $oldValue ? (property_category::find($oldValue) ? property_category::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (property_category::find($newValue) ? property_category::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Category: $oldName to $newName";
                    }
                    elseif ($field == 'status_id') {
                        $oldName = $oldValue ? (statuses::find($oldValue) ? statuses::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (statuses::find($newValue) ? statuses::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Status: $oldName to $newName";
                    }
                    elseif ($field == 'property_type') {
                        $oldName = $oldValue ? (property_type::find($oldValue) ? property_type::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (property_type::find($newValue) ? property_type::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Property Type: $oldName to $newName";
                    }
                    elseif ($field == 'city_id') {
                        $oldName = $oldValue ? (cities::find($oldValue) ? cities::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (cities::find($newValue) ? cities::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "City: $oldName to $newName";
                    }
                    elseif ($field == 'community_id') {
                        $oldName = $oldValue ? (communities::find($oldValue) ? communities::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (communities::find($newValue) ? communities::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Community: $oldName to $newName";
                    }
                    elseif ($field == 'sub_community_id') {
                        $oldName = $oldValue ? (sub_communities::find($oldValue) ? sub_communities::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (sub_communities::find($newValue) ? sub_communities::find($newValue)->name : 'empty'): 'empty';
        
                        $logDetails[] = "Sub Community: $oldName to $newName";
                    }
                    elseif ($field == 'tower_id') {
                        $oldName = $oldValue ? (towers::find($oldValue) ? towers::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (towers::find($newValue) ? towers::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Tower: $oldName to $newName";
                    }
                    elseif ($field == 'agent_id') {
                        $oldName = $oldValue ? (User::find($oldValue) ? User::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (User::find($newValue) ? User::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Listing Agent: $oldName to $newName";
                    }
                    elseif ($field == 'marketing_agent_id') {
                        $oldName = $oldValue ? (User::find($oldValue) ? User::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (User::find($newValue) ? User::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Marketing Agent: $oldName to $newName";
                    }
                    elseif ($field == 'project_status_id') {
                        $oldName = $oldValue ? (project_status::find($oldValue) ? project_status::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (project_status::find($newValue) ? project_status::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Project Status: $oldName to $newName";
                    }
                    elseif ($field == 'owner_id') {
                        $oldName = $oldValue ? (owners::find($oldValue) ? owners::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (owners::find($newValue) ? owners::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Owner: $oldName to $newName";
                    }
                    elseif ($field == 'occupancy_id') {
                        $oldName = $oldValue ? (listing_occupancies::find($oldValue) ? listing_occupancies::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (listing_occupancies::find($newValue) ? listing_occupancies::find($newValue)->name : 'empty') : 'empty';
        
                        $logDetails[] = "Occupancy: $oldName to $newName";
                    }
                    elseif ($field == 'developer_id') {
                        $oldName = $oldValue ? (developers::find($oldValue) ? developers::find($oldValue)->name : 'empty') : 'empty';
                        $newName = $newValue ? (developers::find($newValue) ? developers::find($newValue)->name : 'empty') : 'empty';
                        $logDetails[] = "Developer: $oldName to $newName";
                    }
                    elseif ($field == 'next_availability_date') {
                        $oldName = Carbon::parse($oldValue)->format('Y-m-d');
                        //$oldName = $oldValue ? developers::find($oldValue)->name : 'empty';
                        //$newName = $newValue ? developers::find($newValue)->name : 'empty';
                        $oldName = $newValue ? Carbon::parse($newValue)->format('Y-m-d') : 'empty';
                        if($oldName != $newValue){
                            $logDetails[] = "next_available: $oldName to $newName";   
                        }
                    }
                    elseif ($field == 'published_at') {
                        $oldName = $oldValue ? Carbon::parse($oldValue)->format('Y-m-d') : 'empty';
                        $newName = $newValue ? Carbon::parse($newValue)->format('Y-m-d') : 'empty';
                        if($oldName !== $newName){
                            $logDetails[] = "published_at: $oldName to $newName";   
                        }
                    }
                    else {
                        $logDetails[] = "$field: $oldValue to $newValue";
                    }
                }
            }

            if (!empty($logDetails)) {
                $logMessage = implode(', ', $logDetails);

                activity()
                    ->performedOn($listing)
                    ->causedBy(auth()->user())
                    ->withProperties(['details' => $logMessage])
                    ->log('updated');
            }

            DB::commit();
            
            return response()->json(['message' => 'Listing updated successfully.']);
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

    public function uploadImages(Request $request)
    {
        $uploadedImages = [];

        // Validate the request, e.g., file types, sizes, etc.
        $request->validate([
            'images' => 'required|image|mimes:jpeg,png,jpg,gif, .webp|max:2048', // Adjust validation rules as needed
        ]);

        foreach ($request->file('images') as $image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/images'), $imageName);

            // Save the image details to the database if needed
            $imageModel = Image::create([
                'file_name' => $imageName,
                // Add other fields as needed
            ]);

            $uploadedImages[] = $imageModel;
        }

        return response()->json(['images' => $uploadedImages], 200);
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

                    media_gallery::create([
                        'object' => $document,
                        'object_id' => $owner->id,
                        'object_type' => owners::class,
                        'path' => $filename,
                        'file_name' => $originalName,
                        'file_type' => $fileType,
                        'status' => true,
                        'featured' => false,
                        'created_by' => auth()->user()->id,
                    ]);
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
        $reason = $request->input('reason');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk delete.']);
        }
        DB::beginTransaction();
        try {

            foreach($itemIds as $itemId){
                $data = Listings::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Listing is deleted through bulk action.')
                    ->log('updated');
            }

            // Use the User model to delete users by IDs
            Listings::whereIn('id', $itemIds)->update(['status_reason' => $reason]);
            Listings::whereIn('id', $itemIds)->delete();

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

            foreach($itemIds as $itemId){
                
                $listing = Listings::findOrFail($itemId);
                $oldAgent = $listing->listing_agent ? $listing->listing_agent->name : 'empty';
                $newAgent = User::findOrFail($agent)->name;

                $listing->update(['agent_id' => $agent, 'marketing_agent_id' => $agent]);

                // Create a note
                $note = 'Listing agent is changed from "'.$oldAgent.'" to "'.$newAgent.'"';

                if ($reason) {
                    $note .= ' and the reason is "('.$reason.')"';
                }

                $note .= ' through bulk action.';

                Notes::create([
                    'object_id' => $listing->id,
                    'object_type' => Listings::class,
                    'note' => $note,
                    'status' => true,
                    'created_by' => auth()->user()->id,
                    'updated_by' => auth()->user()->id,
                ]);

                // Log activity
                activity()
                    ->performedOn($listing)
                    ->causedBy(auth()->user())
                    ->withProperties(['note' => $note])
                    ->log('updated');

            }

            // Use the model to change status by IDs
            //Listings::whereIn('id', $itemIds)->update(['status_id' => $status_id, 'status_reason' => $reason]);
            DB::commit();
            return response()->json(['success' => 'Bulk assign successful.']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions that may occur during the deletion process
            return response()->json(['error' => 'Error during bulk assign: ' . $e->getMessage()], 500);
        }
    }

    public function bulkStatusChange(Request $request)
    {
        $itemIds = $request->input('item_ids');
        $status_id = $request->input('status_id');
        $reason = $request->input('reason');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['error' => 'No IDs provided for bulk action.']);
        }

        DB::beginTransaction();

        try {

            $new_status = Statuses::find($status_id)->name;

            foreach($itemIds as $itemId){
                $data = Listings::findOrFail($itemId);
                $old_status = $data->status ? $data->status->name : 'empty';

                $note = 'Listing status is changed from '.$old_status.' to '.$new_status;

                if ($reason) {
                    $note .= ' and the reason is ('.$reason.')';
                }

                $note .= ' through bulk action.';
                
                Notes::create([
                    'object_id' => $data->id,
                    'object_type' => Listings::class,
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
            Listings::whereIn('id', $itemIds)->update(['status_id' => $status_id, 'status_reason' => $reason]);
            DB::commit();
            return response()->json(['message' => 'Bulk status change successful.']);
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
            Listings::whereIn('id', $itemIds)->restore();

            foreach($itemIds as $itemId){
                $data = Listings::findOrFail($itemId);
                activity()
                    ->performedOn($data)
                    ->causedBy(auth()->user())
                    ->withProperties('Listing is restored through bulk action.')
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

    public function bulkDuplicate(Request $request){
        $itemIds = $request->input('item_ids');

        // Check if any user IDs are provided
        if (empty($itemIds)) {
            return response()->json(['message' => 'No IDs provided for bulk duplication.']);
        }

        DB::beginTransaction();
        try {
            
            foreach ($itemIds as $itemId) {
                $originalListing = Listings::with([
                    'notes', 'documents', 'images', 'amenities', 'portals'
                ])->findOrFail($itemId);
    
                // Duplicate the listing
                $newListing = $originalListing->replicate();
                $newListing->refno = $this->getNextRefNo($originalListing->property_for == 'sale' ? 'S' : 'R');
                $newListing->status_id = 2;
                $newListing->created_at = now(); // Set the current timestamp as created_at
                $newListing->updated_at = now();
                $newListing->push();
    
                // Duplicate related records
                //$this->duplicateRelatedRecords($originalListing, $newListing, 'activities');
                $this->duplicateRelatedRecords($originalListing, $newListing, 'notes');
                $this->duplicateRelatedRecords($originalListing, $newListing, 'documents');
                $this->duplicateRelatedRecords($originalListing, $newListing, 'images');
                $this->duplicateRelatedRecords($originalListing, $newListing, 'amenities');
                $this->duplicateRelatedRecords($originalListing, $newListing, 'portals');
                // Add more related records duplication here

                activity()
                    ->performedOn($newListing)
                    ->causedBy(auth()->user())
                    ->withProperties('Listing is duplicated through bulk action.')
                    ->log('updated');
                
            }
            DB::commit();
            return response()->json(['message' => 'Bulk duplication successful.']);

        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any exceptions that may occur during the update process
            return response()->json(['message' => 'Error during bulk duplication: ' . $e->getMessage()], 500);
        }
    }

    private function duplicateRelatedRecords($original, $new, $relation)
    {
        foreach ($original->{$relation} as $relatedRecord) {
            if ($relation == 'notes' || $relation == 'documents' || $relation == 'images') {
                $newRelatedRecord = $relatedRecord->replicate();
                $newRelatedRecord->object_id = $new->id;
                $newRelatedRecord->created_by = auth()->user()->id;
                $newRelatedRecord->updated_by = auth()->user()->id;
                $newRelatedRecord->push();
            } elseif ($relation == 'portals' || $relation == 'amenities') {
                // For many-to-many relationships, attach the existing related records to the new listing
                $new->{$relation}()->attach($relatedRecord->id);
            } else {
                $newRelatedRecord = $relatedRecord->replicate();
                $newRelatedRecord->listing_id = $new->id;
                $newRelatedRecord->push();
            }
        }
    }

    public function bulkSendEmail(Request $request){
        try {
            // Retrieve data from the request
            //$emails = $request->input('formValues.email');
            $emails = $request->input('formValues.email');

            $subject = $request->input('formValues.subject');
            $message = strval(trim($request->input('formValues.message')));
            $itemIds = $request->input('item_ids');

            $listings = Listings::whereIn('id', $itemIds)->get();
            $message = null;

            foreach ($emails as $email) {
                // Trim each email to remove leading/trailing spaces
                $email = trim($email);
                //$recipent = $email;
                $recipient = null; // Ensure $recipient is a string

                Mail::to($email)->send(new ListingBulkEmail($subject, $message, $listings, $recipient));
            }
    
            return response()->json(['success' => 'Emails sent successfully.']);
        } catch (\Exception $e) {
            Log::error('Error sending emails: ' . $e->getMessage());

            //dd($e->getMessage());
            
            return response()->json(['error' => 'Error sending emails: ' . $e->getMessage()], 500);
        }
    }


}
