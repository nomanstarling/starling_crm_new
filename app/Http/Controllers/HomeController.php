<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\owners;
use App\Models\Leads;
use App\Models\Notes;
use App\Models\Listings;
use App\Models\Statuses;
use App\Models\Teams;
use Illuminate\Support\Facades\Hash;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon; // Add Carbon at the top
use Spatie\Searchable\Search;
use Google\Client;
use Google\Service\Calendar;
use Google_Client;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function email()
    {
        // Replace 'to@example.com' with the recipient's email address
        $recipientEmail = 'mna.fullstack@gmail.com';
        $senderEmail = 'noman.m@starlingproperties.ae';


        // Send email using the view directly
        Mail::send('email_template', [], function ($message) use ($recipientEmail, $senderEmail) {
            $message->to($recipientEmail)
                ->from($senderEmail)
                ->subject('Subject of the email'); // Replace with your subject
        });

        // Optionally, you can check if the email was sent successfully
        if (Mail::failures()) {
            return response()->json(['message' => 'Email not sent.'], 500);
        }

        return response()->json(['message' => 'Email sent successfully.']);
    }

    public function email_temp()
    {
       return view('email_template');
    }

    public function dashboard(){
        return view('admin.dashboard');
    }

    public function pdf(){
        $pdf = Browsershot::url('https://portal.starlingproperties.ae/brochure/download/SP-S-01997')->pdf();
        return $pdf; 
    }

    public function createUser(){
        $user = new User;
        $user->type = 'business';
        $user->business_id = 1;
        $user->user_name = 'noman.m';
        $user->email = 'noman.m@starlingproperties.ae';
        $user->phone = '971554178772';
        $user->password = Hash::make('123nomi123');
        $user->save();
        return $user;

    }

    // public function dashboardStats(Request $request){
    //     $agent_id = $request->input('agent_id');
    //     if($agent_id == null){
    //         $agent_id = auth()->user()->id;
    //     }
    //     $user = User::find($agent_id);
    //     if (!$user) {
    //         return response()->json(['error' => 'User not found'], 404);
    //     }

    //     $startDate = $request->input('startDate') ? $request->input('startDate') : null;
    //     $endDate = $request->input('startDate') ? $request->input('startDate') : null;

    //     if($startDate != null){
    //         $startDate = Carbon::parse($startDate);
    //         $formattedStartDate = $startDate->toDateString();
    //     }

    //     if($endDate != null){
    //         $endDate = Carbon::parse($endDate);
    //         $formattedEndDate = $endDate->toDateString();
    //     }

    //     $callsQuery = $user->calls();

    //     if ($startDate) {
    //         $callsQuery->where('start_date', '>=', $startDate);
    //     }

    //     if ($endDate) {
    //         $callsQuery->where('start_date', '<=', $endDate);
    //     }

    //     $callsCount = $callsQuery->count();

    //     //calls end

    //     //off market start
    //     $offMarketQuery = $user->offMarket();

    //     if ($startDate) {
    //         $offMarketQuery->where('start_date', '>=', $startDate);
    //     }

    //     if ($endDate) {
    //         $offMarketQuery->where('start_date', '<=', $endDate);
    //     }

    //     $offMarketCount = $offMarketQuery->count();

    //     //off market end

    //     //published start
    //     $publishedQuery = $user->published();

    //     if ($startDate) {
    //         $publishedQuery->where('start_date', '>=', $startDate);
    //     }

    //     if ($endDate) {
    //         $publishedQuery->where('start_date', '<=', $endDate);
    //     }

    //     $publishedCount = $publishedQuery->count();

    //     //off market end

    //     $user['profile_image_url'] = $user->profileImage();
    //     $user['calls_goal'] = $user->callsGoal();
    //     $user['off_market_goal'] = $user->offMarketGoal();
    //     $user['published_goal'] = $user->publishedGoal();
    //     $stats = [
    //         'user' => $user,
    //         'calls' => $callsCount,
    //         'offMarket' => $offMarketCount,
    //         'published' => $publishedCount
    //     ];

    //     return response()->json(['stats' => $stats]);
    // }

    public function dashboardStats(Request $request){
        $user = auth()->user();
        
        $agent_id = $request->input('agent_id');
    
        $type = 'single';
        $userIds = [];
    
        // Check user's role
        if ($user->hasRole('Super Admin')) {
            // Fetch records for all users
            $users = User::all();
            $userIds = $users->pluck('id');
            $userCount = $users->count();
            $type = 'multiple';
        } elseif ($user->hasRole('Team Leader')) {
            // Fetch records for specific user ids (2, 4, 8)
            $userIds = [2, 4, 8];
            $userCount = count($userIds);
            $type = 'multiple';
        } else {
            // For other roles, fetch records for the logged-in user
            $userIds = [$user->id];
            $userCount = 1;
            $type = 'single';
        }
    
        if ($agent_id != null) {
            // If agent_id is present in the request, use it as the user id
            $userIds = [$request->input('agent_id')];
            $userCount = 1;
            $type = 'single';
        }

        $user = User::whereIn('id', $userIds)->first();
        // Date filters
        $startDate = $request->input('startDate') ? Carbon::parse($request->input('startDate')) : null;
        $endDate = $request->input('endDate') ? Carbon::parse($request->input('endDate')) : null;

        $users = User::whereIn('id', $userIds)->get();
    
        // Calculate the number of days between start and end dates
        $numberOfDays = $startDate && $endDate ? max(1, $endDate->diffInDays($startDate)) : 1;

        $callsCount = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->outGoingCalls()
                ->when($startDate, function ($query) use ($startDate) {
                    $query->where('start_date', '>=', $startDate);
                })
                ->when($endDate, function ($query) use ($endDate) {
                    // Adjust the end time to include the entire day
                    $query->where('start_date', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
                })
                ->count();
        });
    
        $offMarketCount = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->offMarket()
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                // Adjust the end time to include the entire day
                $query->where('created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
            })
            ->count();
        });

    
        $publishedCount = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->published()
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                // Adjust the end time to include the entire day
                $query->where('created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
            })
            ->count();
        });
        
        $callsGoal = $users->sum(function ($user) use ($numberOfDays) {
            return $user->callsGoal() * $numberOfDays;
        });
    
        $offMarketGoal = $users->sum(function ($user) use ($numberOfDays) {
            return $user->offMarketGoal() * $numberOfDays;
        });
    
        $publishedGoal = $users->sum(function ($user) use ($numberOfDays) {
            return $user->publishedGoal() * $numberOfDays;
        });

        $leadsNotContacted = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->leadsNotContacted()
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                // Adjust the end time to include the entire day
                $query->where('created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
            })
            ->count();
        });

        $leadsInProgress = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->leadsInProgress()
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                // Adjust the end time to include the entire day
                $query->where('created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
            })
            ->count();
        });

        $leadsAttemptContact = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->leadsAttemptContact()
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                // Adjust the end time to include the entire day
                $query->where('created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
            })
            ->count();
        });

        $meetingsCount = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->meetings()
                ->when($startDate, function ($query) use ($startDate) {
                    $query->where('notes.created_at', '>=', $startDate);
                })
                ->when($endDate, function ($query) use ($endDate) {
                    // Adjust the end time to include the entire day
                    $query->where('notes.created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
                })
                ->count();
        });        

        $viewingsCount = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->viewings()
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('notes.created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                // Adjust the end time to include the entire day
                $query->where('notes.created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
            })
            ->count();
        });

        $remindersCount = $users->sum(function ($user) use ($startDate, $endDate) {
            return $user->reminders()
            ->when($startDate, function ($query) use ($startDate) {
                $query->where('notes.created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                // Adjust the end time to include the entire day
                $query->where('notes.created_at', '<', date('Y-m-d', strtotime($endDate . ' + 1 day')));
            })
            ->count();
        });
    
        $stats = [
            'user_count' => $userCount,
            'calls' => $callsCount,
            'offMarket' => $offMarketCount,
            'published' => $publishedCount,
            'user' => $user,
            'type' => $type,
            'calls_goal' => $callsGoal,
            'off_market_goal' => $offMarketGoal,
            'published_goal' => $publishedGoal,
            'leads_not_contacted' => $leadsNotContacted,
            'leads_inprogress' => $leadsInProgress,
            'leads_attempt_contact' => $leadsAttemptContact,
            'meetings_count' => $meetingsCount,
            'viewings_count' => $viewingsCount,
            'reminders_count' => $remindersCount,
        ];
    
        return response()->json(['stats' => $stats]);
    }

    // public function getCalendarData(Request $request)
    // {
    //     $loggedInUser = auth()->user();
    //     $currentUserId = $loggedInUser->id;
    //     $valid_leads = Statuses::where('type', 'Leads')->where('lead_type', 'Active')->pluck('id');

    //     $query = Leads::with(['notes' => function ($query) {
    //             $query->select(
    //                 'id',
    //                 'object_id',
    //                 'object',
    //                 'type',
    //                 'event_date',
    //                 'note',
    //                 'created_at',
    //                 'status'
    //             )->orderBy('event_date', 'DESC')->orderBy('created_at', 'ASC')->whereIn('type', ['note', 'reminder', 'meeting', 'viewing'])->limit(50);
    //         }])
    //         ->whereIn('status_id', $valid_leads);

    //     if (!$loggedInUser->hasRole('Super Admin')) {
    //         $query->where('agent_id', $currentUserId);
    //     }

    //     $calendarData = $query->get(['refno', 'id as leadid', 'agent_id'])
    //         ->map(function ($lead) {
    //             $lead->agent_name = $lead->lead_agent->name;
    //             //$lead->addedby_name = $lead->notes->created_by_user->name;
    //             unset($lead->lead_agent, $lead->notes->created_by_user);
    //             return $lead;
    //         });
    //     return $calendarData;

    //     return response()->json(['calendar_data' => $calendarData]);
    // }

    public function getLeadCounts(Request $request)
    {
        $loggedInUser = auth()->user();
        $currentUserId = $loggedInUser->id;

        // Retrieve status IDs for dead, active, and closed leads
        $deadStatusIds = Statuses::where('name', 'Archive')->pluck('id')->toArray();
        $activeStatusIds = Statuses::where('type', 'Leads')->where('lead_type', 'Active')->pluck('id')->toArray();
        $closedStatusIds = Statuses::where('name', 'Deal Closed')->pluck('id')->toArray();

        // Query counts for dead, active, and closed leads
        $allLeadsQuery = Leads::select('id');

        $deadLeadsQuery = Leads::whereIn('status_id', $deadStatusIds);
        $activeLeadsQuery = Leads::whereIn('status_id', $activeStatusIds);
        $closedLeadsQuery = Leads::whereIn('status_id', $closedStatusIds);
        $unassignedLeadsQuery = Leads::where('assign_status', 'Unassigned');

        $user_ids = [];
        // If user is not Super Admin, filter leads by current user ID
        if (!$loggedInUser->hasRole('Super Admin')) {

            if($loggedInUser->is_teamleader == true){
                $team = Teams::with('users')->where('team_leader', $currentUserId)->first();
                
                if ($team && $team->users->isNotEmpty()) {
                    $user_ids = $team->users->pluck('id')->toArray();
                }
                if (!empty($user_ids) && !in_array(auth()->user()->id, $user_ids)) {
                    $user_ids[] = auth()->user()->id;
                }
            }
            else{
                $user_ids = [$currentUserId];
            }

            $deadLeadsQuery->whereIn('agent_id', $user_ids);
            $activeLeadsQuery->whereIn('agent_id', $user_ids);
            $closedLeadsQuery->whereIn('agent_id', $user_ids);
            $unassignedLeadsQuery->whereIn('agent_id', $user_ids);
            $allLeadsQuery->whereIn('agent_id', $user_ids);
        }

        // Get counts for each lead status
        $deadLeadsCount = $deadLeadsQuery->count();
        $activeLeadsCount = $activeLeadsQuery->count();
        $closedLeadsCount = $closedLeadsQuery->count();
        $unassignedLeadsCount = $unassignedLeadsQuery->count();
        $all_count = $allLeadsQuery->count();

        // Prepare counts array
        $counts = [
            'all_leads' => $all_count,
            'dead_leads' => $deadLeadsCount,
            'active_leads' => $activeLeadsCount,
            'closed_leads' => $closedLeadsCount,
            'unassigned_leads' => $unassignedLeadsCount
        ];

        return response()->json(['counts' => $counts]);
    }


    public function getCalendarData(Request $request)
    {
        $loggedInUser = auth()->user();
        $currentUserId = $loggedInUser->id;
        $deadLeads = Statuses::where('type', 'Leads')->where('lead_type', 'Dead')->pluck('id');

        $notesQuery = Notes::with(['lead' => function ($query) use ($deadLeads, $currentUserId, $loggedInUser) {
            $query->select('id', 'refno', 'agent_id');
            //->whereIn('status_id', $validLeads);
            
            // Include the 'lead_agent' relation
            $query->with('lead_agent'); // Adjust the columns as needed
        }])
        ->whereIn('type', ['reminder', 'meeting', 'viewing'])
        ->where('object_type', Leads::class)
        ->whereHas('lead', function ($query) use ($deadLeads, $loggedInUser, $currentUserId) {
            // Filter notes that have leads with valid status IDs
            $query->whereNotIn('status_id', $deadLeads);

            if (!$loggedInUser->hasRole('Super Admin')) {
                if($loggedInUser->is_teamleader == true){
                    $team = Teams::with('users')->where('team_leader', $currentUserId)->first();
                    $user_ids = [];
                    if ($team && $team->users->isNotEmpty()) {
                        $user_ids = $team->users->pluck('id')->toArray();
                    }
                    if (!empty($user_ids) && !in_array(auth()->user()->id, $user_ids)) {
                        $user_ids[] = auth()->user()->id;
                    }

                    $query->whereIn('agent_id', $user_ids);
                }
                else{
                    $query->where('agent_id', $currentUserId);
                }
            }
        })
        ->orderBy('event_date', 'DESC')
        ->orderBy('created_at', 'ASC')
        //->limit(50)
        ->get();
    
        return response()->json(['calendar_data' => $notesQuery]);
    }
    
    // public function searchModule(Request $request){
    //     $query = $request->input('query');

    //     // Perform the search on your User model (adjust attributes as needed)
    //     $users = User::where('name', 'like', "%$query%")
    //         ->orWhere('email', 'like', "%$query%")
    //         ->orWhere('phone', 'like', "%$query%")
    //         ->orWhere('user_name', 'like', "%$query%")
    //         ->get();

    //     return response()->json($users);
    // }

    public function searchModule(Request $request)
    {
        $query = $request->input('query');

        $searchResults = (new Search())
            ->registerModel(User::class, 'name', 'email', 'phone', 'user_name')
            ->registerModel(owners::class, 'name', 'email', 'phone', 'refno')
            ->registerModel(Listings::class, 'refno', 'external_refno', 'property_for', 'beds', 'property_for')
            ->search($query);

        $results = [];

        foreach ($searchResults->groupByType() as $type => $modelResults) {
            $results[$type] = $modelResults->map(function ($result) use ($query) {
                
                $isPartialMatch = false;

                if ($result->type === 'users') {
                    $isPartialMatch = str_contains(strtolower(strval($result->searchable->name)), strtolower($query));
                }
                elseif ($result->type === 'owners') {
                    $isPartialMatch = str_contains(strtolower(strval($result->searchable->refno)), strtolower($query));
                } 
                elseif ($result->type === 'listings') {
                    $isPartialMatch = str_contains(strtolower(strval($result->searchable->refno)), strtolower($query));
                }


                if ($isPartialMatch) {
                    $link = null;

                    if ($result->type === 'users') {
                        $link = route('users.index', ['id' => $result->searchable->id]);
                    } elseif ($result->type === 'owners') {
                        // Assuming there is a refno attribute in the Owner model
                        $link = route('owners.index', ['refno' => $result->searchable->refno]);
                    }
                    elseif ($result->type === 'listings') {
                        // Assuming there is a refno attribute in the Owner model
                        $link = route('listings.index', ['refno' => $result->searchable->refno]);
                    }

                    return [
                        'id' => $result->searchable->id,
                        'name' => $result->searchable->name,
                        'profile_photo' => $result->type != 'listings' ? $result->searchable->profileImage() : null,
                        'email' => $result->searchable->email,
                        'phone' => $result->searchable->phone,
                        'type' => $result->type,
                        'link' => $link,
                        'beds' => $result->searchable->beds,
                        'property_for' => $result->searchable->property_for,
                        'property_type' => $result->type == 'listings' ? ($result->searchable->prop_type ? $result->searchable->prop_type->name : null) : null,
                        'refno' => $result->searchable->refno,
                        'external_refno' => $result->searchable->external_refno,
                    ];
                }

                return null;
            })->filter();
        }

        return response()->json($results);
    }

    public function listingEmail(Request $request){
        $listings = Listings::get();
        $recipent = 'Noman M.';
        $message = '';

        return view('admin.emails.listings', compact('listings', 'recipent', 'message'));
    }

    public function getGoogleToken(Request $request){
        
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user has a Google access token
        if (!$user->google_access_token) {
            return response()->json(['error' => 'Google access token not found'], 400);
        }

        // Initialize Google API Client
        $client = new Client();
        $client->setAccessToken($user->google_access_token);

        // Create Google Calendar service
        $calendarService = new Calendar($client);

        dd($calendarService);
    }

    public function profile(){
        return view('admin.profile.index');
    }

    public function profilePost(Request $request){
        $password = $request->password;
        if($password != null){
            $update = User::find(auth()->user()->id);
            $update->password = Hash::make($password);
            $update->phone = $request->phone;
            $update->save();

            if($update){
                return back()->with('success', 'Password changed.');
            }
            else{
                return back()->with('error', 'Query Error.');
            }
        }
        else{
            return back()->with('success', 'Profile Updated.');
        }
    }

    public function getPdf(){
        //$image = Browsershot::url('https://starlingproperties.ae')->save('public/folder/namefile.jpg');
        // return $image;

        Browsershot::url('https://www.itsolutionstuff.com')
            ->setNodeBinary('/usr/local/bin/node')
            ->setNpmBinary('/usr/local/bin/npm')
            ->setChromePath('/opt/homebrew/bin/chromium')
            ->pdf();
  
        dd("Done");

    }

    public function getPassword(){
        $password = 'UscNXU8w9Q5RthX';
        $password = Hash::make($password);
        return $password;
    }

    
}
