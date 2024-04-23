<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
//use Goutte\Client;
//use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\crm_calls;
use App\Models\Leads;
use App\Models\Campaigns;
use App\Models\LeadDetails;
use App\Models\Notes;
use App\Models\media_gallery;
use App\Models\Statuses;
use App\Models\owners;
use App\Models\contacts;
use App\Models\Listings;
use App\Models\property_type;
use App\Models\listing_portals;
use App\Models\communities;
use App\Models\sub_communities;
use App\Models\Sources;
use App\Models\SubSources;
use App\Models\amenities;
use App\Models\towers;
use App\Models\project_status;
use App\Models\listing_occupancies;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Spatie\Feed\Feed;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Illuminate\Support\Facades\Log;
use Bmatovu\LaravelXml\Facades\Xml;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use \claviska\SimpleImage;
use \Marxolity\OpenAi\Facades\OpenAi;
use GuzzleHttp\Client;
use App\Services\OpenAIService;

use Spatie\Activitylog\Models\Activity;

use Spatie\Valuestore\Valuestore;
use Throwable;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;

class ApiController extends Controller
{
    protected $extensions = [];
    protected $openai;
    protected $whatsAppService;

    public function __construct(OpenAIService $openai, WhatsAppService $whatsAppService)
    {
        //parent::__construct();
        $this->whatsAppService = $whatsAppService;

        // Cache all ids that have an extension
        $extensions = User::select('id', 'extention')
            ->where('extention', '!=', '')
            ->get();

        foreach ($extensions as $extension) {
            $this->extensions[$extension['extention']] = $extension['id'];
        }

        $this->token = 'VlrvxjEpRkWfWxbJxoMxB98RbDBjEI7fSroLu5QdSGIiMGWXbV7gUclETp6E1GKc';
        $this->open_ai_key = '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';
        $this->bypass_auth = false;
        $this->source_id = null;
        $this->sub_source_id = null;

        $this->openai = $openai;

        $this->settings = Valuestore::make(config('settings.path'));
        $this->shortName = $this->settings->get('short_name');

        //$usersModel = new User(); // Assuming UsersModel is your Eloquent model for users
        // You can now use $this->extensions and $usersModel as needed
    }

    public function testNotify(){
        $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MzE2Mywic3BhY2VJZCI6MTI3NjYxLCJvcmdJZCI6MTMwMjc0LCJ0eXBlIjoiYXBpIiwiaWF0IjoxNjkwOTE3MDIzfQ.cOUDP18yzoSkZGUBRHigfXZAxTWAq9jG4B4NkRXcugs';
        $respond_io_channel_id = '152478';

        // WhatsApp number or identifier to send the message to
        $phoneNumber = '971554178772';

        // Message content
        $message = [
            'text' => 'There has been an update in your account...',
            'type' => 'text',
            'messageTag' => 'ACCOUNT_UPDATE'
        ];

        // Prepare the request body
        $payload = [
            'message' => $message,
            'channelId' => $respond_io_channel_id // Assuming channelId is always 0 in your case
        ];

        try {
            // Send the POST request to the Respond.io API using Laravel's HTTP client
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post("https://api.respond.io/v2/contact/phone:$phoneNumber/message", $payload);

            // Handle the response
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'response' => $response->json()
            ]);

        } catch (\Exception $e) {
            // Handle any exceptions or errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function strip_known_terms($body)
    {
        $terms = [
            'manager at +971 4 5560300',
            '971 4 5560300',
        ];
        $body = str_replace($terms,'', $body);

        return $body;
    }

    private function clean_text($body) {
        $body = strip_tags($body);
        $body = preg_replace('/[ \t]+/', ' ', $body);
        $body = str_replace("\r\n", "\n", $body); 
        $body = preg_replace('/\n+/', "\n", $body); 
        $body = implode("\n", array_map('trim', explode("\n", $body)));
        $body = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $body);
    
        return $body;
    }

    private function returnStatus($status, $message)
    {
        $response = [
            'status' => $status,
            'message' => $message
        ];

        echo json_encode($response);
        exit;
    }

    // public function dinstar($id = null){
    //     $client = new Client();

    //     try {
    //         // Capture port parameter (optional)
    //         $port = isset($argv[1]) ? $argv[1] : 81;

    //         // Navigate to login page
    //         $crawler = $client->request('GET', 'https://starling.doomdns.com:' . $port . '/enLogin.htm');

    //         // Fill in login credentials
    //         $form = $crawler->selectButton('Login')->form();
    //         $form['loginname'] = 'admin';
    //         $form['loginpass'] = 'Starling@123!';
    //         $crawler = $client->submit($form);

    //         // Navigate to CDR page
    //         $crawler = $client->request('GET', 'https://starling.doomdns.com:' . $port . '/enCallCDR.htm');

    //         // Extract data from table rows
    //         $data = [];
    //         $crawler->filter('.table_data tr')->each(function ($row) use (&$data) {
    //             $rowData = [];
    //             $row->filter('td')->each(function ($column) use (&$rowData) {
    //                 $rowData[] = trim($column->text());
    //             });
    //             $data[] = $rowData;
    //         });

    //         // Log extracted data
    //         print_r($data);

    //     } catch (Exception $e) {
    //         echo 'Error: ' . $e->getMessage();
    //     }
    // }
    public function dinstar($id) {
        // $scriptPath = public_path('libraries/dinstar/');
        // $nodePath = "/usr/local/bin/node";
    
        // $command = $nodePath . ' ' . $scriptPath . 'index.mjs ' . $id . ' 2>&1';
        // $output = shell_exec($command);

        // $json = str_replace("'", '"', $output);
        // $json = json_decode($json, true);
        // dd($output);
        // array_shift($json);

        // try {
        //     $client = new Client();

        //     // Navigate to login page
        //     $crawler = $client->request('GET', 'https://starling.doomdns.com:81/enLogin.htm');
            
        //     // Fill in credentials and submit the login form
        //     $form = $crawler->select('#form1');
        //     $form['#loginname'] = 'admin';
        //     $form['#loginpass'] = 'Starling@123!';
        //     $crawler = $client->submit($form->form());

        //     // Navigate to CDR page
        //     $client->request('GET', 'https://starling.doomdns.com:81/enCallCDR.htm');

        //     // Wait for table data and capture elements
        //     $crawler->waitFor('.table_data');
        //     $rows = $crawler->filter('.table_data tr');

        //     // Handle scenario with only one row
        //     if ($rows->count() === 1) {
        //         $year = date('Y');
        //         $month = date('n');
        //         $day = date('j');

        //         // Fill in the date filter and submit
        //         $crawler->select('#StartYear', $year);
        //         $crawler->select('#StartMonth', $month);
        //         $crawler->select('#StartDay', $day);
        //         $crawler->filter('input[name="filter"]')->click();

        //         // Wait for table data and recapture elements
        //         $crawler->waitFor('.table_data');
        //         $rows = $crawler->filter('.table_data tr');
        //     }

        //     // Extract data from table rows
        //     $data = [];
        //     $rows->each(function (Crawler $row) use (&$data) {
        //         $columns = $row->filter('td');
        //         $rowContent = [];
        //         $columns->each(function (Crawler $column) use (&$rowContent) {
        //             $rowContent[] = $column->text();
        //         });
        //         $data[] = $rowContent;
        //     });

        //     // Log extracted data
        //     dd($data);

        // } catch (\Exception $error) {
        //     // Handle errors
        //     dd('Error: ' . $error->getMessage());
        // }


        // Replace 'https://starling.doomdns.com:81' with the base URL
        $baseUrl = 'https://starling.doomdns.com:81';
        //$client = HttpClient::create(['verify_peer' => false]);
        $client = HttpClient::create(['verify_peer' => false]);
        // Create an HTTP client
        //$client = HttpClient::create();

        // Create a browser
        $browser = new HttpBrowser($client);

        // Visit the login page
        $crawler = $browser->request('GET', $baseUrl . '/enLogin.htm');

        // Find the login form and fill in credentials
        $form = $crawler->filter('#form1');
        $form['loginname'] = 'admin';
        $form['loginpass'] = 'Starling@123!';

        // Submit the login form
        $crawler = $browser->submit($form->form());

        // Visit the CDR page
        $crawler = $browser->request('GET', $baseUrl . '/enCallCDR.htm');

        // Wait for the table data to be present
        $crawler->waitFor('.table_data');

        // Extract rows from the table
        $rows = $crawler->filter('.table_data tr');

        // Output the rows
        foreach ($rows as $row) {
            echo $row->textContent . PHP_EOL;
        }
    }
    

    public function dinstars($id) {
        $scriptPath = public_path('libraries/dinstar/index.mjs');
        $nodePath = "/usr/local/bin/node";
    
        // Provide execution permission to the Node.js script
        $chmodCommand = "chmod +x " . escapeshellarg($scriptPath);
        shell_exec($chmodCommand);
    
        // Execute the Node.js script using Symfony Process
        $process = new Process([$nodePath, $scriptPath, $id]);
    
        try {
            // Run the process
            $process->mustRun();
    
            // Get the output
            $output = $process->getOutput();

            dd($output);
    
            // Check if $output is valid JSON
            $json = json_decode(str_replace("'", '"', $output), true);
    
            if (is_array($json)) {
                // Remove the first element from the array
                array_shift($json);
    
                foreach ($json as $row) {
                    // Process each row as needed
                }
    
                return $json;
            } else {
                // Handle the case where $output is not valid JSON
                throw new \Exception('Invalid JSON format');
            }
        } catch (ProcessFailedException $exception) {
            // Output error details for debugging
            dd($exception->getMessage(), $process->getErrorOutput());
        } catch (\Exception $exception) {
            // Handle the exception for invalid JSON
            dd($exception->getMessage());
        }
    }
    

    // public function saveUsers()
    // {
    //     $hashedPassword = Hash::make('123nomi123');

    //     return $hashedPassword;
    //     $url = 'https://portal.starlingproperties.ae/dinstar/getUsers';

    //     // Make a GET request to the external URL
    //     $response = Http::get($url);

    //     // Check if the request was successful (status code 2xx)
    //     if ($response->successful()) {
    //         // Decode the JSON response
    //         $users = $response->json();

    //         // Now you can handle the $users array as needed
    //         foreach ($users as $user) {

    //             $phone = preg_replace('/[^0-9]/', '', $user['mobile']);
    //             $phoneSecondary = preg_replace('/[^0-9]/', '', $user['mobile1']);
    //             $email = $user['email'];
    //             $user_name = $user['username'];
                
    //             $phoneExists = User::where('phone', $phone)->exists();
    //             $phoneSecondaryExists = User::where('phone_secondary', $phoneSecondary)->exists();
    //             $emailExists = User::where('email', $email)->exists();
    //             $userNameExists = User::where('user_name', $user['username'])->exists();

    //             $randomString = Str::random(5);

    //             $email = $emailExists ? $email.$randomString : $email;
    //             $phone = $phoneExists ? $phone.$randomString : $phone;
    //             $phoneSecondary = $phoneSecondaryExists ? $phoneSecondary.$randomString : $phoneSecondary;

    //             $user_name = $userNameExists ? $user_name.$randomString : $user_name;

    //             User::create([
    //                 'id' => $user['id'],
    //                 'type' => 'individual',
    //                 'name' => $user['name'] != null ? $user['name'] : $user['first_name'].' '.$user['last_name'],
    //                 'user_name' => $user['username'],
    //                 'email' => $email,
    //                 //'phone' => $user['mobile'],
    //                 'phone' => $phone,
    //                 'phone_secondary' => $phoneSecondary,
    //                 'designation' => $user['jobtitle'],
    //                 'gender' => ucfirst($user['gender']),
    //                 'extention' => $user['extension'],
    //                 'rera_no' => $user['rera_no'],
    //                 'brn' => $user['brn'],
    //                 'status' => $user['status'] == 'Y' ? true : false,
    //                 'deleted_at' => $user['delete_status'] == 'Y' ? now() : null,
    //             ]);
    //         }

    //         return response()->json(['message' => 'Users saved successfully']);
    //     } else {
    //         // Handle the case where the request was not successful
    //         return response()->json(['error' => 'Failed to retrieve users from the external URL'], 500);
    //     }
    // }


    public function importCalls(){
        $api_key = 'a44854d5-0e78-4f7a-8d5a-702b831b6f2d';

        $url = 'https://portal.starlingproperties.ae/dinstar/getCalls?api='.$api_key;
        
        // Make a GET request to the external URL
        $response = Http::get($url);

        // Check if the request was successful (status code 2xx)
        if ($response->successful()) {
            // Decode the JSON response
            $calls = $response->json();

            // Now you can handle the $users array as needed
            foreach ($calls as $call) {

                $user_get = User::where('user_name', $call['user_name'])->first();
                $user_id = $user_get ? $user_get->id : null;

                crm_calls::create([
                    'id' => $call['call_id'],
                    'port' => $call['port'],
                    'start_date' => Carbon::parse($call['start_date'])->toDateTimeString(),
                    'answer_date' => ($call['answer_date'] && $call['answer_date'] != '0000-00-00 00:00:00') ? Carbon::parse($call['answer_date'])->toDateTimeString() : null,

                    // 'start_date' => ucfirst($call['start_date']),
                    // 'answer_date' => $call['answer_date'],
                    'direction' => $call['direction'],
                    'source' => $call['source'],
                    'ip' => $call['ip'],
                    'destination' => $call['destination'],
                    'hang_side' => $call['hang_side'],
                    'reason' => $call['reason'],
                    'duration' => $call['duration'],
                    'codec' => $call['codec'],
                    'rtp_send' => $call['rtp_send'],
                    'rtp_recv' => $call['rtp_recv'],
                    'loss_rate' => $call['loss_rate'],
                    'BCCH' => $call['BCCH'],
                    'user_id' => $user_id,
                ]);
            }

            return response()->json(['message' => 'Calls saved successfully']);
        } else {
            // Handle the case where the request was not successful
            return response()->json(['error' => 'Failed to retrieve call from the external URL'], 500);
        }
    }
    public function importDinstar($id){
        $url = 'https://portal.starlingproperties.ae/dinstar/export/'.$id;

        // Make a GET request to the external URL
        $response = Http::get($url);

        // Check if the request was successful (status code 2xx)
        if ($response->successful()) {
            // Decode the JSON response
            $calls = $response->json();

            // Now you can handle the $users array as needed
            foreach ($calls as $row) {

                //$startDate = $row[1];
                $source = $row[4];

                //$count = crm_calls::where('start_date', $startDate)
                   // ->where('source', $source)
                   // ->count();

                // Assuming $row[1] contains a date string
                $startDate = $row[1];

                // Parse the date using Carbon
                $startDate = Carbon::parse($startDate);

                // Format the date as needed (e.g., 'Y-m-d H:i:s')
                $formattedStartDate = $startDate->format('Y-m-d H:i:s');

                // Now use $formattedStartDate in your query
                $count = crm_calls::where('start_date', $formattedStartDate)
                    ->where('source', $row[4])
                    ->count();

                if ($count > 0)
                {
                    continue;
                }

                if (empty($row[4]))
                {
                    continue;
                }

                if (empty($row[6]))
                {
                    continue;
                }

                $data = [
                    'port' => $row[0],
                    'start_date' => Carbon::parse($row[1])->format('Y-m-d H:i:s'),
                    'answer_date' => $row[2] == '0000-00-00 00:00:00' ? null : Carbon::parse($row[2])->format('Y-m-d H:i:s'),
                    'direction' => $row[3] == 'IP->Gsm' ? 'outgoing' : 'incoming',
                    'source' => $row[4],
                    'ip' => $row[5],
                    'destination' => $row[6],
                    'hang_side' => $row[7],
                    'reason' => $row[8],
                    'duration' => $row[9],
                    'codec' => $row[10],
                    'rtp_send' => $row[11],
                    'rtp_recv' => $row[12],
                    'loss_rate' => intval($row[13]),
                    'BCCH' => $row[15],
                    'user_id' => $this->extensions[$row[4]] ?? null
                ];
        
                // Insert into the database using Eloquent
                crm_calls::create($data);
            }

            return response()->json(['message' => 'Calls saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve call from the external URL'], 500);
        }

    }

    public function importCommunities(){
        $url = 'https://portal.starlingproperties.ae/dinstar/getCommunities';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {

                //echo $row['clients_id'].'<br>';
                
                $community = new communities;
                $community->country_id = 234;
                $community->city_id = 7;
                $community->name = $row['title'];
                $community->save();

            }

            return response()->json(['message' => 'Data saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve data from the external URL'], 500);
        }
    }

    public function importSources(){
        $url = 'http://localhost/crmtwo/dinstar/getSources';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {

                //echo $row['clients_id'].'<br>';
                
                $source = new Sources;
                $source->name = $row['title'];
                $source->status = true;
                $source->save();

            }

            return response()->json(['message' => 'Data saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve data from the external URL'], 500);
        }
    }

    public function importFeatures(){
        $url = 'http://localhost/crmtwo/dinstar/getFeatures';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {

                //echo $row['clients_id'].'<br>';
                
                $save = new amenities;
                $save->name = $row['title'];
                $save->type = $row['facility_type'];
                $save->code = $row['code'];
                $save->status = true;
                $save->save();

            }

            return response()->json(['message' => 'Data saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve data from the external URL'], 500);
        }
    }

    public function importSubCommunities(){
        $url = 'https://portal.starlingproperties.ae/dinstar/getSubCommunities';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {

                //echo $row['clients_id'].'<br>';
                $community = communities::where('name', $row['development_name'])->first();
                if($row['title'] != null || $row['title'] != ''){
                    $sub_community = new sub_communities;
                    $sub_community->country_id = 234;
                    $sub_community->city_id = 7;
                    $sub_community->community_id = $community->id;
                    $sub_community->name = $row['title'];
                    $sub_community->save();
                }

            }

            return response()->json(['message' => 'Data saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve data from the external URL'], 500);
        }
    }

    public function importTowers(){
        $url = 'https://portal.starlingproperties.ae/dinstar/getTowers';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {

                //echo $row['clients_id'].'<br>';
                $sub_community = sub_communities::where('name', $row['sub_community_name'])->first();
                $community = communities::where('id', $sub_community->community_id)->first();
                if($row['name'] != null || $row['name'] != ''){
                    $tower = new towers;
                    $tower->country_id = 234;
                    $tower->city_id = 7;
                    $tower->community_id = $community->id;
                    $tower->sub_community_id = $sub_community->id;
                    $tower->name = $row['name'];
                    $tower->save();
                }

            }

            return response()->json(['message' => 'Data saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve data from the external URL'], 500);
        }
    }

    private function getNextRefNoOwners(){
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
            $newRefNo = 'SP' . '-O-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            // If there are no existing records, start from 001
            $newRefNo = 'SP' . '-O-001';
        }
        return $newRefNo;
    }

    public function importContacts(){
        $url = 'https://portal.starlingproperties.ae/dinstar/getContacts';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {

                //echo $row['clients_id'].'<br>';
                $get_source = Sources::where('name', $row['source_name'])->first();
                $get_user = User::where('email', $row['added_by_user'])->first();
                
                $data = new owners;
                $data->name = $row['firstname'].' '.$row['lastname'];
                $data->phone = $row['owner_mobile'];
                $data->refno = $this->getNextRefNoOwners();
                $data->email = $row['owner_email'];
                $data->old_refno = $row['refno'];
                $data->source_id = $get_source ? $get_source->id : null;
                $data->created_by = $get_user ? $get_user->id : null;
                $data->save();

                //$noteValues = $row['notes'];
                //echo $row['owner_email'].'<br>';
                //print_r($noteValues);

                // if (count($noteValues) > 0) {
                //     foreach ($noteValues as $note) {
                //         // Notes::create([
                //         //     'object' => 'Notes',
                //         //     'object_id' => $data->id,
                //         //     'object_type' => owners::class,
                //         //     'note' => $note,
                //         //     'status' => true,
                //         //     'created_by' => $get_user ? $get_user->id : null,
                //         //     'updated_by' => $get_user ? $get_user->id : null,
                //         // ]);
                //         echo $note.'<br>';
                //     }
                // }

                activity()
                ->performedOn($data)
                ->causedBy(auth()->user())
                ->withProperties('Owner imported.')
                ->log('created');

            }

            return response()->json(['message' => 'owners saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve data from the external URL'], 500);
        }
    }

    private function getNextRefNoListing($type){
        $latestListing = Listings::withTrashed()
            ->select('refno')
            ->orderByRaw("CAST(SUBSTRING_INDEX(refno, '-', -1) AS SIGNED) DESC")
            ->first();

        if ($latestListing) {
            $latestRefNo = $latestListing->refno;
            $numericPart = (int)preg_replace('/[^0-9]/', '', $latestRefNo);
            $nextNumericPart = $numericPart + 1;
            $newRefNo = 'SP' . '-'.$type.'-' . str_pad($nextNumericPart, 3, '0', STR_PAD_LEFT);
        } else {
            $newRefNo = 'SP' . '-'.$type.'-001';
        }
        return $newRefNo;
    }

    public function importListings(){
        $url = 'https://portal.starlingproperties.ae/dinstar/getListings';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {

                //echo $row['clients_id'].'<br>';
                $agent = User::where('email', $row['agent_email'])->first();
                $marketing_agent = User::where('email', $row['marketing_agent_email'])->first();
                $created_by = User::where('email', $row['created_by_email'])->first();
                $community = communities::where('name', $row['community_name'])->first();
                if(!$community && trim($row['community_name']) != null){
                    $create_community = new communities;
                    $create_community->country_id = 234;
                    $create_community->city_id = 7;
                    $create_community->name = $row['community_name'];
                    $create_community->save();
                    $community = $create_community;
                }
                $sub_community = sub_communities::where('name', $row['sub_community_name'])->first();
                if(!$sub_community && trim($row['sub_community_name']) != null){
                    $create_sub_community = new sub_communities;
                    $create_sub_community->country_id = 234;
                    $create_sub_community->city_id = 7;
                    $create_sub_community->community_id = $community->id;
                    $create_sub_community->name = $row['sub_community_name'];
                    $create_sub_community->save();
                    $sub_community = $create_sub_community;
                }
                $tower = towers::where('name', $row['building_name'])->first();
                if(!$tower && trim($row['building_name']) != null){
                    $create_tower = new towers;
                    $create_tower->country_id = 234;
                    $create_tower->city_id = 7;
                    $create_tower->community_id = $community->id;
                    $create_tower->sub_community_id = $sub_community->id;
                    $create_tower->name = $row['building_name'];
                    $create_tower->save();
                    $tower = $create_tower;
                }
                $property_type = property_type::where('name', $row['property_type_name'])->first();
                $owner = owners::where('old_refno', $row['owner_refno'])->first();
                $occupancy = listing_occupancies::where('name', $row['occupancy'])->first();
                $project_status = project_status::where('name', $row['completed'])->first();

                $status_val = null;
                $archive_val = false;
                if($row['status'] == 'D'){
                    $status_val = 'Prospect';
                }
                else if($row['status'] == 'C'){
                    $status_val = 'Coming to Market';
                }
                else if($row['status'] == 'Y'){
                    $status_val = 'Available - Published';
                }
                else if($row['status'] == 'L'){
                    $status_val = 'Available - Off-Market';
                }
                else if($row['status'] == 'N'){
                    $status_val = 'Prospect';
                    $archive_val = true;
                }

                $status = Statuses::where('name', $status_val)->first();
                
                $data = new Listings;
                $data->refno = $this->getNextRefNoListing($row['property_for'] == "sale" ? "S" : "R");
                $data->old_refno = $row['refno'];
                $data->external_refno = $row['external_refno'];
                $data->title = $row['title'];;
                $data->desc = $row['desc'];
                $data->property_for = $row['property_for'];

                $data->property_type = $property_type ? $property_type->id : null;
                $data->category_id = $property_type ? $property_type->id : null;
                $data->country_id = 234;
                $data->city_id = 7;
                $data->community_id = $community ? $community->id : null;
                $data->sub_community_id = $sub_community ? $sub_community->id : null;
                $data->tower_id = $tower ? $tower->id : null;
                $data->plot_no = $row['plotno'];
                $data->plot_area = $row['plotarea'];
                $data->unit_type = $row['unittype'];
                $data->unit_no = $row['unitno'];
                $data->floor_no = $row['floorno'];
                $data->bua = $row['bua'];
                $data->lead_gen = $row['leadgen'] == 'Yes' ? true : false;
                $data->poa = $row['poa'] == 'Y' ? true : false;
                $data->project_status_id = $project_status ? $project_status->id : null;

                $data->completion_date = $this->parseDate($row['completion_date']);
                $data->expiry_date = $this->parseDate($row['expiry_date']);
                $data->next_availability_date = $this->parseDate($row['avail_date']);
                $data->available_date = $this->parseDate($row['avail_date']);

                $data->parking = $row['parking'];
                $data->beds = $row['bedrooms'];
                $data->baths = $row['bathrooms'];
                $data->furnished = $row['furnished'] == true ? 'Furnished' : 'Unfurnished';
                $data->latitude = $row['latitude'];
                $data->longitude = $row['longitude'];
                
                $data->status_id = $archive_val == true ? 2 : ($status ? $status->id : null);

                $data->status_reason = $row['status_reason'];
                $data->price = $row['price'];
                $data->frequency = ucfirst($row['frequency']);
                $data->occupancy_id = $occupancy ? $occupancy->id : null;
                $data->cheques = $row['cheques'];

                $data->currency = $row['currency'];
                $data->created_by = $created_by ? $created_by->id : null;
                $data->agent_id = $agent ? $agent->id : null;
                $data->marketing_agent_id = $marketing_agent ? $marketing_agent->id : null;
                $data->owner_id = $owner ? $owner->id : null;
                $data->rera_permit = $row['rera_permit'];
                

                $data->view = $row['view'];
                $data->video_link = $row['video_link'];
                $data->live_tour_link = $row['livetour_link'];
                $data->save();

                if($archive_val == true){
                    $data->delete();
                }

                //$noteValues = $row['notes'];
                //echo $row['owner_email'].'<br>';
                //print_r($noteValues);

                // if (count($noteValues) > 0) {
                //     foreach ($noteValues as $note) {
                //         // Notes::create([
                //         //     'object' => 'Notes',
                //         //     'object_id' => $data->id,
                //         //     'object_type' => owners::class,
                //         //     'note' => $note,
                //         //     'status' => true,
                //         //     'created_by' => $get_user ? $get_user->id : null,
                //         //     'updated_by' => $get_user ? $get_user->id : null,
                //         // ]);
                //         echo $note.'<br>';
                //     }
                // }

                activity()
                ->performedOn($data)
                ->causedBy(auth()->user())
                ->withProperties('Listing imported.')
                ->log('created');

            }

            return response()->json(['message' => 'listings saved successfully']);

            //return response()->json(['success' => 'Failed to retrieve call from the external URL'], 500);

        }else{
            return response()->json(['error' => 'Failed to retrieve data from the external URL'], 500);
        }
    }

    public function syncListings(){
        $url = 'https://portal.starlingproperties.ae/dinstar/getPortalsForListings';

        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            foreach ($data as $row) {
                // Fetch the listing using refno
                $listing = Listings::where('old_refno', $row['refno'])->first();

                if ($listing) {
                    // Fetch the portal using portal_name
                    $portal = listing_portals::where('name', $row['portal_name'])->first();

                    if ($portal) {
                        // Attach the portal to the listing
                        $listing->portals()->attach($portal->id);
                    } else {
                        // Handle the case where portal is not found
                        // You may log an error or take appropriate action
                    }
                } else {
                    // Handle the case where listing is not found
                    // You may log an error or take appropriate action
                }
            }

            return 'done';
        }
    }


    // public function syncImages(){
    //     $listing_get = Listings::where('import_type', null)->where('old_refno', 'SP-R-00402')->first();
    //     if($listing_get){
    //         $url = 'https://portal.starlingproperties.ae/dinstar/getListingImages/'.$listing_get->old_refno;
    //         $response = Http::get($url);
    //         if ($response->successful()) {
    //             $data = $response->json();
    //             foreach ($data as $row) {
    
    //                 $img_link = 'https://portal.starlingproperties.ae/public/'.$row['image'];
    //                 $img = file_get_contents($img_link);
    //                 //echo $img.'<br>';
    //                 $path = 'uploads/listings/'.$listing_get->refno.'/images';

    //                 $filename = $img->store($path, 'public');
    //                 $fileType = $img->getClientOriginalExtension();
    //                 $originalName = $img->getClientOriginalName();
    //                 $size = $img->getSize();
        
    //                 // $image_gallery = new media_gallery;
    //                 // $image_gallery->object = 'image';
    //                 // $image_gallery->object_id = $listing->id;
    //                 // $image_gallery->object_type = Listings::class;
    //                 // $image_gallery->path = $filename;
    //                 // $image_gallery->file_name = $originalName;
    //                 // $image_gallery->file_type = $fileType;
    //                 // $image_gallery->status = true;
    //                 // $image_gallery->featured = false;
    //                 // $image_gallery->created_by = auth()->user()->id;
    //                 // $image_gallery->updated_by = auth()->user()->id;
    //                 // $image_gallery->save();

    //                 // Create a SimpleImage object
    //                 $simpleImage = new SimpleImage();
    //                 $simpleImage->load($img);

    //                 // Resize the image to the first size (e.g., 300x200)
    //                 $simpleImage->resize(550, 375);
    //                 $firstSizePath = $path.'/550x375/'.$filename; // Adjust the path as needed
    //                 $simpleImage->save($firstSizePath);

    //                 // Resize the image to the second size (e.g., 600x400)
    //                 $simpleImage->resize(768, 535);
    //                 $secondSizePath = $path.'/768x535/'.$filename; // Adjust the path as needed
    //                 $simpleImage->save($secondSizePath);
    //                 echo 'done resizing';
                    
    //             }
    
    //             return 'done';
    //         }
    //         else{
    //             echo 'url issue<br>';
    //         }
    //     }
    //     else{
    //         return 'no listing found';
    //     }
        

        
    // }

    public function syncImages()
    {
        $listing_gets = Listings::where('import_type', null)->where('old_refno', '!=', null)->take(5)->get();

        if ($listing_gets) {
            foreach($listing_gets as $listing_get){
                $url = 'https://portal.starlingproperties.ae/dinstar/getListingImages/' . $listing_get->old_refno;
                $response = Http::get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $count = 0;
                    foreach ($data as $row) {
                        $count++;
                        $img_link = 'https://portal.starlingproperties.ae/public/' . $row['image'];
                        $image = file_get_contents($img_link);

                        $filename = pathinfo($row['image'], PATHINFO_BASENAME);
                        /* Save file wherever you want */
                        //file_put_contents('public/uploads/'.$filename, $image);

                        $path = 'uploads/listings/' . $listing_get->refno . '/images/' . $filename;
                        $directoryPath = 'uploads/listings/' . $listing_get->refno . '/images';
                        
                        $structure = [];
                        $structure[] = 'uploads/listings/' . $listing_get->refno . '/images/';
                        $structure[] = 'uploads/listings/' . $listing_get->refno . '/images/550x375/';
                        $structure[] = 'uploads/listings/' . $listing_get->refno . '/images/768x535/';

                        // Make sure the structure exists
                        foreach ($structure as $dir) {
                            $this->touchDirectory($dir);
                        }
                        
                        // Upload to AWS
                        //$this->upload_to_aws($path, $image, $filename);
                        $storagePath = public_path('storage');

                        $originalImagePath = $storagePath . '/' . $path;
                        file_put_contents($storagePath.'/'.$path, $image);

                        $image_gallery = new media_gallery;
                        $image_gallery->object = 'image';
                        $image_gallery->object_id = $listing_get->id;
                        $image_gallery->object_type = Listings::class;
                        $image_gallery->path = $path;
                        $image_gallery->file_name = $filename;
                        $image_gallery->status = true;
                        $image_gallery->featured = false;
                        $image_gallery->created_by = 1;
                        $image_gallery->updated_by = 1;
                        $image_gallery->save();

                        //$this->listings_model->updateListing_imageColumn($check_listing->id, $images);
                        //$savedImage = 'public/storage/' . $path;
                        //$image = new SimpleImage($savedImage);
                        $image = new SimpleImage($originalImagePath);

                        list($width, $height) = getimagesize($originalImagePath);

                        $image->thumbnail(768, 535)->toFile($storagePath.'/'.$directoryPath.'/768x535/'.$filename);
                        $image->thumbnail(550, 375)->toFile($storagePath.'/'.$directoryPath.'/550x375/'.$filename);

                        $messages[] = 'Done resizing : ' . $listing_get->refno . '<br>';

                        //echo 'Done resizing : '.$listing_get->refno.' <br>';
                    }

                    $update_listing = Listings::find($listing_get->id);
                    $update_listing->import_type = 'done';
                    $update_listing->save();

                    $messages[] = $count . ' images imported <br><br>----------------<br><br>';
                    
                    //echo $count.' images imported <br><br>----------------<br><br>';
                } else {
                    //echo 'URL issue<br>';
                    $messages[] = 'URL issue<br>';

                }
            }
            
        } else {
            //return 'No listing found';
            $messages[] = 'No listing found';

        }

        return view('admin.test', ['messages' => $messages]);

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

    private function parseDate($date)
    {
        if ($date === null || $date === '0000-00-00') {
            return null;
        }

        $rules = ['date' => 'date_format:Y-m-d'];
        $validator = Validator::make(['date' => $date], $rules);

        try {
            $validator->validate();
            return Carbon::parse($date);
        } catch (ValidationException $e) {
            return null;
        }
    }

    public function cronPortal($name){
        // $portal = listing_portals::with('listings')->where('slug', $name)->first();
        // if(!$portal){
        //     return 'Portal not found';
        // }

        // $portal_listings = $portal->listings;

        $portal = listing_portals::with(['listings' => function ($query) {
            $query->where('status_id', 4)->where('agent_id', '!=', null);
        }])->where('slug', $name)->first();
        
        $portal_listings = $portal->listings;        

        //return $portal_listings;
        if($portal->slug == 'bayut'){
            return $this->bayutPortal($portal, $portal_listings);
        }
        else if($portal->slug == 'generic'){
            return $this->propertyFinderPortal($portal, $portal_listings);
        }
        else if($portal->slug == 'propertyfinder'){
            return $this->propertyFinderPortal($portal, $portal_listings);
        }
        else if($portal->slug == 'dubizzle'){
            return $this->dubizzlePortal($portal, $portal_listings);
        }
        return 'nothing';

    }

    private function propertyFinderPortal($portal, $listings)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $lastUpdate = count($listings) > 0 ? $listings[0]->updated_at : '';
        $xml .= '<list last_update="'.$lastUpdate.'" listing_count="'.count($listings).'">';
        
        if(count($listings) > 0){
            foreach($listings as $key => $listing){
                $property = Listings::where('id', $listing->id)->first();
                $property_for = $property->property_for == 'rent' ? 'R' : 'S';
                $offering_type = $property->category->name == 'Commercial' ? 'C' : 'R';
                $xml .= '<property last_update="'.$property->updated_at.'">';
                $xml .= '<reference_number><![CDATA['.$property->external_refno.']]></reference_number>';
                $xml .= '<permit_number><![CDATA['.$property->rera_permit.']]></permit_number>';
                $xml .= '<offering_type><![CDATA['.$offering_type.$property_for.']]></offering_type>';
                $xml .= '<property_type><![CDATA['.$property->prop_type->code.']]></property_type>';
                $xml .= '<price_on_application><![CDATA['.($property->poa == true ? 'Yes' : 'No').']]></price_on_application>';
                
                if($property->property_for == 'sale'){
                    $xml .= '<price><![CDATA['.$property->price.']]></price>';
                }
                else{
                    $xml .= '<price>';
                        if($property->frequency == 'Yearly'){
                            $xml .= '<yearly>'.$property->price.'</yearly>';
                            $xml .= '<monthly></monthly>';
                            $xml .= '<weekly></weekly>';
                            $xml .= '<daily></daily>';
                        }
                        elseif($property->frequency == 'Monthly'){
                            $xml .= '<yearly></yearly>';
                            $xml .= '<monthly>'.$property->price.'</monthly>';
                            $xml .= '<weekly></weekly>';
                            $xml .= '<daily></daily>';
                        }
                        elseif($property->frequency == 'Weekly'){
                            $xml .= '<yearly></yearly>';
                            $xml .= '<monthly></monthly>';
                            $xml .= '<weekly>'.$property->price.'</weekly>';
                            $xml .= '<daily></daily>';
                        }
                        elseif($property->frequency == 'Daily'){
                            $xml .= '<yearly></yearly>';
                            $xml .= '<monthly></monthly>';
                            $xml .= '<weekly></weekly>';
                            $xml .= '<daily>'.$property->price.'</daily>';
                        }
                    $xml .= '</price>';
                }
                $xml .= '<cheques><![CDATA['.$property->cheques.']]></cheques>';
                $xml .= '<plot_size><![CDATA['.$property->plot_area.']]></plot_size>';
                $xml .= '<size><![CDATA['.$property->bua.']]></size>';
                $xml .= '<city><![CDATA['.($property->city ? $property->city->name : '').']]></city>';
                $xml .= '<community><![CDATA['.($property->community ? $property->community->name : '').']]></community>';
                $xml .= '<sub_community><![CDATA['.($property->sub_community ? $property->sub_community->name : '').']]></sub_community>';
                $xml .= '<property_name><![CDATA['.($property->tower ? $property->tower->name : '').']]></property_name>';
                $xml .= '<developer><![CDATA['.($property->developer ? $property->developer->name : '').']]></developer>';
                $xml .= '<title_en><![CDATA['.$property->title.']]></title_en>';
                $xml .= '<description_en><![CDATA['.strip_tags($property->desc).']]></description_en>';
                //print_r($property->amenities);
                $xml .= '<private_amenities>';
                    if (count($property->amenities) > 0) {
                        $amenitiesCount = count($property->amenities);
                        $currentAmenity = 1;
                    
                        foreach ($property->amenities as $amenity) {
                            //if($amenity->type == 'Private'){
                                $xml .= $amenity->code . '- ' . $amenity->name;
                                // Add a comma if it's not the last amenity
                                if ($currentAmenity < $amenitiesCount) {
                                    $xml .= ', ';
                                }
                        
                                $currentAmenity++;
                            //}
                            
                        }
                    }
                $xml .= '</private_amenities>';

                $xml .= '<commercial_amenities>';
                    if (count($property->amenities) > 0) {
                        $amenitiesCount = count($property->amenities);
                        $currentAmenity = 1;
                    
                        foreach ($property->amenities as $amenity) {
                            if($amenity->type == 'Commercial'){
                                $xml .= $amenity->code . '- ' . $amenity->name;
                                // Add a comma if it's not the last amenity
                                if ($currentAmenity < $amenitiesCount) {
                                    $xml .= ', ';
                                }
                        
                                $currentAmenity++;
                            }
                            
                        }
                    }
                $xml .= '</commercial_amenities>';

                $xml .= '<bedroom><![CDATA['.$property->beds.']]></bedroom>';
                $xml .= '<bathroom><![CDATA['.$property->baths.']]></bathroom>';
                $xml .= '<agent>';
                    $xml .= '<id><![CDATA['.($property->listing_agent ? $property->listing_agent->refno : '').']]></id>';
                    $xml .= '<name><![CDATA['.($property->listing_agent ? $property->listing_agent->name : '').']]></name>';
                    $xml .= '<email><![CDATA['.($property->listing_agent ? $property->listing_agent->email : '').']]></email>';
                    $xml .= '<phone><![CDATA['.($property->listing_agent ? $property->listing_agent->id : '').']]></phone>';
                    $xml .= '<photo><![CDATA['.($property->listing_agent ? ($property->listing_agent->photo != null ? asset('public/storage/'.$property->listing_agent->photo) : '') : '').']]></photo>';
                    $xml .= '<license_no><![CDATA['.($property->listing_agent ? $property->listing_agent->brn : '').']]></license_no>';
                    $xml .= '<info><![CDATA['.($property->listing_agent ? $property->listing_agent->designation : '').']]></info>';
                $xml .= '</agent>';
                $xml .= '<furnished><![CDATA[';

                if ($property->furnished == 'Furnished') {
                    $xml .= 'Yes';
                } elseif ($property->furnished == 'Partly Furnished') {
                    $xml .= 'Partly';
                } else {
                    $xml .= 'No';
                }

                $xml .= ']]></furnished>';

                $xml .= '<view><![CDATA['.$property->view.']]></view>';
                $xml .= '<parking><![CDATA['.$property->parking.']]></parking>';
                $xml .= '<floor><![CDATA['.$property->floor.']]></floor>';
                $xml .= '<completion_status><![CDATA['.str_replace(' ', '_', strtolower($property->project_status ? $property->project_status->name : '')).']]></completion_status>';
                $xml .= '<availability_date><![CDATA['.$property->next_availability_date.']]></availability_date>';

                $xml .= '<view360><![CDATA['.$property->floor.']]></view360>';
                $xml .= '<video_tour_url><![CDATA['.$property->live_tour_link.']]></video_tour_url>';
                $xml .= '<geopoint><![CDATA['.$property->id.']]></geopoint>';

                $xml .= '<photo>';
                    if(count($property->images) > 0){
                        foreach($property->images as $image){
                            if($image->floor_plan == false){
                                $xml .= '<url last_updated="'.$image->updated_at.'" watermark="yes"><![CDATA['.asset('public/storage/'.$image->path).']]></url>';
                            }
                        }
                    }
                $xml .= '</photo>';
                $xml .= '<floor_plan>';
                    if(count($property->images) > 0){
                        foreach($property->images as $image){
                            if($image->floor_plan == true){
                                $xml .= '<url last_updated="'.$image->updated_at.'" watermark="yes"><![CDATA['.asset('public/storage/'.$image->path).']]></url>';
                            }
                        }
                    }
                $xml .= '</floor_plan>';
                $xml .= '</property>';
            }
        }
        $xml .= '</list>';
        
        $filename = $portal->slug.'.xml';
        Storage::put($filename, $xml);

        return 'done';
    }

    private function bayutPortal($portal, $listings)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $lastUpdate = count($listings) > 0 ? $listings[0]->updated_at : '';
        $xml .= '<Properties>';
        
        if(count($listings) > 0){
            foreach($listings as $key => $listing){
                $property = Listings::where('id', $listing->id)->first();
                $xml .= '<Property>';
                $xml .= '<Property_Ref_No><![CDATA['.$property->external_refno.']]></Property_Ref_No>';
                $xml .= '<Permit_Number><![CDATA['.$property->external_refno.']]></Permit_Number>';
                $xml .= '<Property_purpose><![CDATA['.ucfirst($property->property_for == 'sale' ? 'Buy' : 'Rent' ).']]></Property_purpose>';
                $xml .= '<Property_Type><![CDATA['.($property->prop_type ? $property->prop_type->name : '').']]></Property_Type>';
                $xml .= '<Property_Status><![CDATA[live]]></Property_Status>';

                $xml .= '<City><![CDATA['.($property->city ? $property->city->name : '').']]></City>';
                $xml .= '<Locality><![CDATA['.($property->community ? $property->community->name : '').']]></Locality>';
                $xml .= '<Sub_Locality><![CDATA['.($property->sub_community ? $property->sub_community->name : '').']]></Sub_Locality>';
                $xml .= '<Tower_Name><![CDATA['.($property->tower ? $property->tower->name : '').']]></Tower_Name>';

                $xml .= '<Property_Title><![CDATA['.$property->title.']]></Property_Title>';
                $xml .= '<Property_Description><![CDATA['.strip_tags($property->desc).']]></Property_Description>';

                $xml .= '<Property_Size><![CDATA['.$property->bua.']]></Property_Size>';
                $xml .= '<Property_Size_Unit><![CDATA[SqFt]]></Property_Size_Unit>';

                $xml .= '<Bedrooms><![CDATA['.$property->beds.']]></Bedrooms>';
                $xml .= '<Bathroom><![CDATA['.$property->baths.']]></Bathroom>';
                $xml .= '<Price><![CDATA['.$property->price.']]></Price>';

                $xml .= '<Listing_Agent><![CDATA['.($property->listing_agent ? $property->listing_agent->name : '').']]></Listing_Agent>';
                $xml .= '<Listing_Agent_Email><![CDATA['.($property->listing_agent ? $property->listing_agent->email : '').']]></Listing_Agent_Email>';
                $xml .= '<Listing_Agent_Phone><![CDATA['.($property->listing_agent ? $property->listing_agent->id : '').']]></Listing_Agent_Phone>';

                $xml .= '<Furnished><![CDATA[';

                if ($property->furnished == 'Furnished') {
                    $xml .= 'Yes';
                } elseif ($property->furnished == 'Partly Furnished') {
                    $xml .= 'Partly';
                } else {
                    $xml .= 'No';
                }

                $xml .= ']]></Furnished>';

                $xml .= '<Features>';
                    if(count($property->amenities) > 0){
                        foreach($property->amenities as $amenity){
                            $xml .= '<Feature><![CDATA['.$amenity->name.']]></Feature>';
                        }   
                    }
                $xml .= '</Features>';

                $xml .= '<portals>';
                    if(count($property->portals) > 0){
                        foreach($property->portals as $portal){
                            if($portal->name == 'Bayut' || $portal->name == 'Dubizzle'){
                                $xml .= '<portal><![CDATA['.$portal->name.']]></portal>';
                            }
                        }   
                    }
                $xml .= '</portals>';

                $xml .= '<Images>';
                    if(count($property->images) > 0){
                        foreach($property->images as $image){
                            if($image->floor_plan == false){
                                $xml .= '<Image><![CDATA['.asset('public/storage/'.$image->path).']]></Image>';
                            }
                        }
                    }
                $xml .= '</Images>';

                $xml .= '<Floor_Plans>';
                    if(count($property->images) > 0){
                        foreach($property->images as $image){
                            if($image->floor_plan == true){
                                $xml .= '<Floor_Plan><![CDATA['.asset('public/storage/'.$image->path).']]></Floor_Plan>';
                            }
                        }
                    }
                $xml .= '</Floor_Plans>';
                $xml .= '<Last_Updated><![CDATA['.$property->updated_at.']]></Last_Updated>';
                if($property->property_for == 'rent'){
                    $xml .= '<Rent_Frequency><![CDATA['.$property->frequency.']]></Rent_Frequency>';
                }
                $xml .= '<Off_Plan><![CDATA[No]]></Off_Plan>';
                $xml .= '</Property>';
            }
        }
        $xml .= '</Properties>';

        $filename = 'bayut.xml';
        Storage::put($filename, $xml);
        return 'done';
    }

    private function dubizzlePortal($portal, $listings)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $lastUpdate = count($listings) > 0 ? $listings[0]->updated_at : '';
        $xml .= '<dubizzlepropertyfeed>';
        
        if(count($listings) > 0){
            foreach($listings as $key => $listing){
                $property = Listings::where('id', $listing->id)->first();
                $xml .= '<property>';
                $xml .= '<status>'.($property->project_status ? $property->project_status->name : '').'</status>';
                $xml .= '<type>'.$property->category->code.'</type>';
                $xml .= '<subtype>'.$property->prop_type->code.'</subtype>';

                $xml .= '<refno>'.$property->external_refno.'</refno>';

                $xml .= '<title><![CDATA['.$property->title.']]></title>';
                $xml .= '<description><![CDATA['.strip_tags($property->desc).']]></description>';
                $xml .= '<size>'.$property->bua.'</size>';
                $xml .= '<sizeunits>SqFt</sizeunits>';
                $xml .= '<price>'.$property->price.'</price>';
                $xml .= '<pricecurrency>AED</pricecurrency>';
                if($property->property_for == 'rent'){
                    $frequency = null;
                    if($property->frequency == 'Yearly'){
                        $frequency = 'YR';
                    }
                    elseif($property->frequency == 'Monthly'){
                        $frequency = 'MN';
                    }
                    elseif($property->frequency == 'Weekly'){
                        $frequency = 'WK';
                    }
                    elseif($property->frequency == 'Daily'){
                        $frequency = 'DL';
                    }
                    $xml .= '<rentpriceterm>'.$frequency.'</rentpriceterm>';
                    $xml .= '<rentispaid>1</rentispaid>';
                }

                $xml .= '<furnished>'.($property->furnished == 'Unfurnished' ? 1 : 0).'</furnished>';
                $xml .= '<bedrooms>'.$property->beds.'</bedrooms>';
                $xml .= '<bathrooms>'.$property->baths.'</bathrooms>';
                $xml .= '<developer><![CDATA['.($property->developer ? $property->developer->name : '').']]></developer>';

                $xml .= '<contactname>'.($property->listing_agent ? $property->listing_agent->name : '').'</contactname>';
                $xml .= '<contactemail>'.($property->listing_agent ? $property->listing_agent->email : '').'</contactemail>';
                $xml .= '<contactnumber>'.($property->listing_agent ? $property->listing_agent->id : '').'</contactnumber>';

                $xml .= '<city>'.($property->city ? $property->city->name : '').'</city>';
                $xml .= '<locationtext><![CDATA['.($property->community ? $property->community->name : '').']]></locationtext>';
                //$xml .= '<Sub_Locality><![CDATA['.($property->sub_community ? $property->sub_community->name : '').']]></Sub_Locality>';
                $xml .= '<building><![CDATA['.($property->tower ? $property->tower->name : '').']]></building>';

                $xml .= '<permit_number>'.$property->rera_permit.'</permit_number>';
                $xml .= '<privateamenities>'.$property->rera_permit.'</privateamenities>';

                $xml .= '<photos>';
                    if (count($property->images) > 0) {
                        $imageCount = count($property->images);
                        
                        foreach ($property->images as $index => $image) {
                            if ($image->floor_plan == false) {
                                $xml .= asset('public/storage/' . $image->path);
                                
                                // Add pipe character if it's not the last image
                                if ($index < $imageCount - 1) {
                                    $xml .= '|';
                                }
                            }
                        }
                    }
                $xml .= '</photos>';
                $xml .= '<video_url>'.$property->video_link.'</video_url>';
                $xml .= '<geopoint></geopoint>';
                $xml .= '</property>';
            }
        }
        $xml .= '</dubizzlepropertyfeed>';

        $filename = 'dubizzle.xml';
        Storage::put($filename, $xml);
        return 'done';
    }

    private function pFinderPortal($portal, $listings)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $lastUpdate = count($listings) > 0 ? $listings[0]->updated_at : '';
        $xml .= '<list last_update="'.$lastUpdate.'" listing_count="'.count($listings).'">';
        
        if(count($listings) > 0){
            foreach($listings as $key => $listing){
                $property = Listings::where('id', $listing->id)->first();
                $xml .= '<property last_update="'.$property->updated_at.'">';
                $xml .= '<reference_number><![CDATA['.$property->external_refno.']]></reference_number>';
                $xml .= '<permit_number><![CDATA['.$property->external_refno.']]></permit_number>';
                $xml .= '<offering_type><![CDATA['.$property->category->code.']]></offering_type>';
                $xml .= '<property_type><![CDATA['.$property->prop_type->code.']]></property_type>';
                $xml .= '<price_on_application><![CDATA['.($property->poa == true ? 'Yes' : 'No').']]></price_on_application>';
                $xml .= '<price><![CDATA['.$property->price.']]></price>';
                $xml .= '<cheques><![CDATA['.$property->cheques.']]></cheques>';
                $xml .= '<plot_size><![CDATA['.$property->plot_area.']]></plot_size>';
                $xml .= '<size><![CDATA['.$property->bua.']]></size>';
                $xml .= '<city><![CDATA['.($property->city ? $property->city->name : '').']]></city>';
                $xml .= '<community><![CDATA['.($property->community ? $property->community->name : '').']]></community>';
                $xml .= '<sub_community><![CDATA['.($property->sub_community ? $property->sub_community->name : '').']]></sub_community>';
                $xml .= '<property_name><![CDATA['.($property->tower ? $property->tower->name : '').']]></property_name>';
                $xml .= '<developer><![CDATA['.($property->developer ? $property->developer->name : '').']]></developer>';
                $xml .= '<title_en><![CDATA['.$property->title.']]></title_en>';
                $xml .= '<description_en><![CDATA['.strip_tags($property->desc).']]></description_en>';
                $xml .= '<private_amenities><![CDATA['.$property->title.']]></private_amenities>';
                $xml .= '<commercial_amenities><![CDATA['.$property->title.']]></commercial_amenities>';
                $xml .= '<bedroom><![CDATA['.$property->beds.']]></bedroom>';
                $xml .= '<bathroom><![CDATA['.$property->baths.']]></bathroom>';
                $xml .= '<agent>';
                    $xml .= '<id><![CDATA['.($property->listing_agent ? $property->listing_agent->refno : '').']]></id>';
                    $xml .= '<name><![CDATA['.($property->listing_agent ? $property->listing_agent->name : '').']]></name>';
                    $xml .= '<email><![CDATA['.($property->listing_agent ? $property->listing_agent->email : '').']]></email>';
                    $xml .= '<phone><![CDATA['.($property->listing_agent ? $property->listing_agent->id : '').']]></phone>';
                    $xml .= '<photo><![CDATA['.($property->listing_agent ? ($property->listing_agent->photo != null ? asset('public/storage/'.$property->listing_agent->photo) : '') : '').']]></photo>';
                    $xml .= '<license_no><![CDATA['.($property->listing_agent ? $property->listing_agent->brn : '').']]></license_no>';
                    $xml .= '<info><![CDATA['.($property->listing_agent ? $property->listing_agent->designation : '').']]></info>';
                $xml .= '</agent>';
                $xml .= '<furnished><![CDATA['.$property->furnished.']]></furnished>';
                $xml .= '<view><![CDATA['.$property->view.']]></view>';
                $xml .= '<parking><![CDATA['.$property->parking.']]></parking>';
                $xml .= '<floor><![CDATA['.$property->floor.']]></floor>';
                $xml .= '<completion_status><![CDATA['.($property->project_status ? $property->project_status->name : '').']]></completion_status>';
                $xml .= '<availability_date><![CDATA['.$property->next_availability_date.']]></availability_date>';

                $xml .= '<view360><![CDATA['.$property->floor.']]></view360>';
                $xml .= '<video_tour_url><![CDATA['.$property->live_tour_link.']]></video_tour_url>';
                $xml .= '<permit_number><![CDATA['.$property->rera_permit.']]></permit_number>';
                $xml .= '<geopoint><![CDATA['.$property->id.']]></geopoint>';

                $xml .= '<photo>';
                    if(count($property->images) > 0){
                        foreach($property->images as $image){
                            if($image->floor_plan == false){
                                $xml .= '<url last_updated="'.$image->updated_at.'" watermark="yes"><![CDATA['.asset('public/storage/'.$image->path).']]></url>';
                            }
                        }
                    }
                $xml .= '</photo>';
                $xml .= '<floor_plan>';
                    if(count($property->images) > 0){
                        foreach($property->images as $image){
                            if($image->floor_plan == true){
                                $xml .= '<url last_updated="'.$image->updated_at.'" watermark="yes"><![CDATA['.asset('public/storage/'.$image->path).']]></url>';
                            }
                        }
                    }
                $xml .= '</floor_plan>';
                $xml .= '</property>';
            }
        }
        $xml .= '</list>';

        $filename = 'propertyfinder.xml';
        Storage::put($filename, $xml);
        return 'done';
    }

    public function getPortal($portal)
    {
        try {
            // Check if the file exists
            if (!Storage::exists($portal)) {
                // Return a response with a 404 status code for "Not Found"
                return response()->json(['error' => 'File not found'], 404);
            }
            $file = Storage::get($portal);
            $response = new Response($file);
            return $response->withHeaders(['Content-Type' => 'text/xml']);

        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error getting portal: ' . $e->getMessage());

            // Return a generic error response
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // public function webhook(){

    // }

    public function webhookEmail()
    {
        $request_body = file_get_contents('php://input');
        $request_body = json_decode($request_body);
        $agent_id = null;
        $agent_name = null;
        $campaign = '';
        $campaign_match = '';
        $source_url = '';
        $source_note = null;
        $pf_ref = '';
        $source_name = null;
        $sub_source_name = null;

        if (isset($request_body->message->message->text))
        {
            $request_body->body = $request_body->message->message->text;
        }

        if (isset($_REQUEST['message']))
        {
            $request_body = new StdClass;
            $request_body->body = $_REQUEST['message'];
        }

        $hash = '';

        if (isset($request_body->body))
        {
            $source_note = $this->clean_text($request_body->body);
            $hash = md5($source_note);

            // strip known terms
            $request_body->body = $this->strip_known_terms($request_body->body);
        } else {
            $hash = md5(json_encode($_REQUEST));
        }

        // dupe check
        $lead = Leads::select('id')->where('hash', $hash)->get();
        if ($lead == null)
        {
            //return $this->returnStatus('info', 'Duplicate request, ignored');
            return 'Duplicate request, ignored';
        }

        if (isset($request_body->body) && strpos($request_body->body, 'Career Submission') !== false) {
            return $this->returnStatus('info', 'Website query is an career submission, so ignored.');
        }

        // skip anything where the body contains "Verified Listing rejection"
        if (isset($request_body->body) && strpos($request_body->body, 'Verified Listing rejection') !== false) {
            return $this->returnStatus('info', 'Irrelevant message, ignored');
        }
        // same with "Verified Listing success"
        if (isset($request_body->body) && strpos($request_body->body, 'Verified Listing success') !== false) {
            return $this->returnStatus('info', 'Irrelevant message, ignored');
        }

        // pf url matching (whatsapp incoming)
        // if (isset($request_body->body) && strpos($request_body->body, 'https://www.propertyfinder.ae/leads/v1/lead/message') !== false) {
        //     preg_match('/https:\/\/www\.propertyfinder\.ae\/leads\/v1\/lead\/message\/\S+/', $request_body->body, $matches);

        //     if (isset($matches[0])) {
        //         $url = $matches[0];
                
        //         if ($_SERVER['HTTP_HOST'] == 'lux.test')
        //         {
        //             $nodePath = "/opt/homebrew/bin/node";
        //             $scriptPath = "/Users/johnwarwick/Projects/lux/crm/application/libraries/pfurl/";
        //         } else {
        //             $nodePath = "/usr/local/bin/node";
        //             $scriptPath = "/usr/local/lsws/DEFAULT/html/application/libraries/pfurl/";
        //         }
                
        //         $command = $nodePath . ' ' . $scriptPath . 'index.mjs ' . escapeshellarg($url) . ' 2>&1';
        //         $output = shell_exec($command);
                
        //         if (strpos($output, 'http') !== false) {
        //             $request_body->body = $output;
        //             $source_note = $this->clean_text($request_body->body);
        //             $this->source_id = 150; // Propertyfinder

        //             if (strpos($source_note, 'https://web.whatsapp.com/send/') !== false) {
        //                 preg_match('/https:\/\/web\.whatsapp\.com\/send\/\S+/', $source_note, $matches);
        //                 if (isset($matches[0])) {
        //                     $url = urldecode($matches[0]);
        //                     preg_match('/https:\/\/www\.propertyfinder\.ae\/\S+/', $url, $matches);
        //                     if (isset($matches[0])) {
        //                         $url = $matches[0];
        //                         if (substr($url, -1) == '.') {
        //                             $url = substr($url, 0, -1);
        //                         }

        //                         $command = $nodePath . ' ' . $scriptPath . 'ref.mjs ' . escapeshellarg($url) . ' 2>&1';
        //                         $output = shell_exec($command);

        //                         if ($output && strlen($output) < 16) {
        //                             $pf_ref = $output;
        //                         }
        //                     }
        //                 }
        //             }

        //         } else {
        //             return $this->returnStatus('error', 'Unable to parse propertyfinder url');
        //         }
        //     }
        // }

        // if the body contains an url, set it to the source_url
        if (isset($request_body->body) && strpos($request_body->body, 'Page URL') !== false) {
            preg_match('/Page URL: \S+/', $request_body->body, $matches);

            if (isset($matches[0])) {
                $source_url = str_replace('Page URL: ', '', $matches[0]);
            }
        }

        if (strlen($source_url) > 255) {
            $source_url = substr($source_url, 0, 255);
        }

        // let's see if we can find a campaign variable
        if (isset($request_body->body) && isset($request_body->campaign)) {
            $campaign = $request_body->campaign;
        } else if (isset($_REQUEST['campaign'])) {
            $campaign = $_REQUEST['campaign'];
        } else if (strpos($request_body->body, 'Campaign: ') !== false) {
            preg_match('/Campaign: (.+)/', $request_body->body, $matches);

            if (isset($matches[0])) {
                $campaign = str_replace('Campaign: ', '', $matches[0]);
            }
        }

        // let's see if we can find a source variable, this is same as campaign
        if (isset($request_body->body) && isset($request_body->source)) {
            $campaign = $request_body->source;
        } else if (isset($_REQUEST['source'])) {
            $campaign = $_REQUEST['source'];
        } else if (strpos($request_body->body, 'Source: ') !== false) {
            preg_match('/Source: (.+)/', $request_body->body, $matches);

            if (isset($matches[0])) {
                $campaign = str_replace('Source: ', '', $matches[0]);
            }
        }

        if ($campaign)
        {
            $campaign_search = isset($campaign['utm_source']) ? $campaign['utm_source'] : $campaign;
            $campaign_match = Campaigns::with('users')->where('target_name', trim($campaign_search))->first();
            if ($campaign_match)
            {
                if ($campaign_match->users && count($campaign_match->users) > 0)
                {
                    // Get the array of agent IDs
                    $agents = $campaign_match->users->pluck('id')->toArray();

                    // if (isset($agents[$campaign_match->assignment_pointer])) {
                    //     // Get the agent ID using the assignment pointer
                    //     $agent_id = $agents[$campaign_match->assignment_pointer];

                    //     // Update the campaign's assignment_pointer column with the next user ID
                    //     $next_assignment_pointer = ($campaign_match->assignment_pointer + 1) % count($agents);
                    //     $campaign_match->update([
                    //         'assignment_pointer' => $next_assignment_pointer,
                    //     ]);
                    // } else {
                    //     // If the assignment pointer is invalid, reset it to the first agent
                    //     $agent_id = $agents[0];
                    //     $campaign_match->update([
                    //         'assignment_pointer' => 1,
                    //     ]);
                    // }

                    $next_assignment_pointer = 1;
                    if (isset($agents[$campaign_match->assignment_pointer])) {
                        // Get the agent ID using the assignment pointer
                        $agent_id = $agents[$campaign_match->assignment_pointer];

                        // Update the campaign's assignment_pointer column with the next user ID
                        $next_assignment_pointer = ($campaign_match->assignment_pointer + 1);
                        //return $agent_id;
                        $campaign_match->update([
                            'assignment_pointer' => $next_assignment_pointer,
                        ]);
                    } else {
                        // If the assignment pointer is invalid, reset it to the first agent
                        $agent_id = $agents[0];
                        $next_assignment_pointer = 1;
                        $campaign_match->update([
                            'assignment_pointer' => $next_assignment_pointer,
                        ]);
                    }
                }
            } else {
                // no campaign match, let's create a new campaign
                $new_campaign = [
                    'name' => trim($campaign_search),
                    'target_name' => trim($campaign_search),
                    //'status' => 'S',
                    'agents' => json_encode([]),
                    'assignment_pointer' => 0,
                    'match_count' => 1
                ];
                // Create a new campaign instance
                $campaign = new Campaigns($new_campaign);

                // Save the campaign to the database
                $campaign->save();
            }
        }

        if (is_array($campaign)) {
            $campaign = json_encode($campaign);
        }

        //original prompt
        //$original_prompt = "You are an assistant that analyses a plain text email, and returns a json-formatted object. Context text is an enquiry to a property agent from a property broker website. Valid json output fields are:\n\"name\" name or username of the person making the enquiry\n\"email\" the enquirer's contact email address\n\"phone\" the sender's phone number \n\"budget\" budget, if present \n\"bedrooms\" number of bedrooms required\n\"bathrooms\" number of bathrooms required\n\"refno\" the reference number of a property, if present\n\"enquiry\" the enquiry message written by the user, if present - or a summary of the request. Unknown values should be empty. Ignore noreply emails, or email addresses from bayut, propertyfinder, propsearch, dubizzle, starlingproperties.ae, etc. Enquiries are often sent to Trent Challis or the agent, but this is not the name of the person making the enquiry. Phone number is always required.";

        // prompty updated by noman
        //$agent_conditions = 'If the email body contains the phrase "Dear" followed by the phrase "You just missed a call," extract the name that appears after "Dear" and before "You just missed a call.", If the email body contains the phrase "Hello" followed by the phrase "A customer just tried to reach you," extract the name that appears after "Hello" and before "A customer just tried to reach you.", If the email body contains the phrase "Dear" followed by the phrase "You just received a call," extract the name that appears after "Dear" and before "You just received a call.", If the email body contains the phrase "you have a new lead!" followed by the agent name in the format "AgentName, you have a new lead!", extract the agent name., If the email body contains the phrase "Dear *" followed by the agent name in the format "Dear AgentName," extract the agent name.';
        $agent_conditions = 'If the email body contains the phrase "Dear" followed by the phrase "You just missed a call," extract the name that appears after "Dear" and before "You just missed a call.", If the email body contains the phrase "Hello" followed by the phrase "A customer just tried to reach you," extract the name that appears after "Hello" and before "A customer just tried to reach you.", If the email body contains the phrase "Dear" followed by the phrase "You just received a call," extract the name that appears after "Dear" and before "You just received a call.", If the email body contains the phrase "you have a new lead!" followed by the agent name in the format "AgentName, you have a new lead!", extract the agent name., If the email body contains the phrase "Dear *" followed by the agent name in the format "Dear AgentName," extract the agent name., If the email body contains the phrase "Missed By:" followed by the agent name, extract the agent name., If the email body contains the phrase "You just spoke to a customer through dubizzle," extract the name that appears after "Hello" and before the comma., If the email body contains the phrase "A customer just tried to reach you through dubizzle," extract the name that appears after "Hello" and before the comma.';
        $main_prompt = "You are an assistant that analyses a plain text email, and returns a json-formatted object. Context text is an enquiry to a property agent from a property broker website. Valid json output fields are:\n\"agent_name\" identify the agent's name based on the following conditions in the brackets (".$agent_conditions.")\n\"name\" name or username of the person making the enquiry\n\"email\" the enquirer's contact email address\n\"phone\" the sender's phone number \n\"budget\" budget, if present \n\"bedrooms\" number of bedrooms required\n\"bathrooms\" number of bathrooms required\n\"refno\" the reference number of a property, if present\n\"enquiry\" the enquiry message written by the user, if present - or a summary of the request. Unknown values should be empty. Ignore noreply emails, or email addresses from bayut, propertyfinder, propsearch, dubizzle, starlingproperties.ae, etc. Enquiries are often sent to Trent Challis or the agent, but this is not the name of the person making the enquiry. Phone number is always required. and this is the content from where you need to extract the information: ".$request_body->body;

        $request_fields = [
            "model" => "gpt-3.5-turbo-16k",
            "messages" => [
              [
                "role" => "system",
                "content" => $main_prompt
              ],
              [
                "role" => "user",
                "content" => $request_body->body
              ]
            ]
        ];

        $request_fields = json_encode($request_fields);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $request_fields,
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . $this->open_ai_key,
            "Content-Type: application/json"
        ),
        ));

        $response = curl_exec($curl);
        if ($response)
        {
            $response = json_decode($response);
        }

        $err = curl_error($curl);
        curl_close($curl);

        if ($err || !isset($response) || isset($response->error)) {
            // insert the request into crm_leads_queue
            if (isset($request_body->body))
            {
                $body = json_encode($request_body);
            } else {
                $body = json_encode($_REQUEST);
            }

            $lead_que = new LeadsQueue;
            $lead_que->body = $body;
            $lead_que->save();

            return $this->returnStatus('error', 'OpenAI request failed, inserted into queue');
        }

        $parsed_response = $response->choices[0]->message->content;
        $parsed_response = json_decode($parsed_response);

        //return $parsed_response;

        $contact_name = $contact_phone = $contact_email = $contact_enquiry = $external_refno = '';
        if (isset($parsed_response->name)) {
            $contact_name = $parsed_response->name;
        }
        if (isset($parsed_response->phone)) {
            $contact_phone = $parsed_response->phone;
        }
        if (isset($parsed_response->email)) {
            $contact_email = $parsed_response->email;
        }
        // if (isset($parsed_response->refno) && !$pf_ref) {
        //     $external_refno = $parsed_response->refno;
        // }
        if (isset($parsed_response->refno)) {
            $external_refno = $parsed_response->refno;
        }
        if (isset($parsed_response->enquiry)) {
            $contact_enquiry = $parsed_response->enquiry;
        }
        if (isset($parsed_response->agent_name) && !empty($parsed_response->agent_name)) {
            $agent_name = $parsed_response->agent_name;
        }
        if ($pf_ref) {
            $external_refno = trim($pf_ref);
        }

        if ($external_refno) {

            $property = Listings::where('external_refno', $external_refno)->first();

            // $property = $this->db->get_where('crm_listings', ['external_refno' => $external_refno])->row_array();
            
            if ($property && $property->agent_id) {
                $agent_id = $property->agent_id;
                //$external_refno = $property->refno;
                $external_refno = $property->external_refno;
            }
        }

        // contact name matches
        if (isset($request_body->body) && strpos($request_body->body, 'Your Name:') !== false) {
            preg_match('/Your Name:\s*(.+)/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $contact_name = $matches[1];
            }
        }
        if (isset($request_body->body) && strpos($request_body->body, 'Name :-') !== false) {
            preg_match('/Name :-\s*(.+)/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $contact_name = $matches[1];
            }
        }
        if (isset($request_body->body) && strpos($request_body->body, 'has sent you a message') !== false) {
            preg_match('/\r\n\r\n \*(.+)\* has sent you a message/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $contact_name = $matches[1];
            }
        }

        // agent name matches
        // if (isset($request_body->body) && strpos($request_body->body, 'Received By:') !== false) {
        //     preg_match('/Received By: (.+)/', $request_body->body, $matches);

        //     if (isset($matches[1])) {
        //         $agent_name = $matches[1];
        //         //$agent_name = explode(' ', $agent_name);

        //         if (!$contact_name) {
        //             $contact_name = 'Call Received';
        //         }

        //     }
        // }

        if (isset($request_body->body) && strpos($request_body->body, 'Received By:') !== false) {
            preg_match('/Received By:\s*(.+)/', $request_body->body, $matches);
        
            if (isset($matches[1])) {
                $agent_name = trim($matches[1]);
        
                if (!$contact_name) {
                    $contact_name = 'Call Received';
                    $sub_source_name = "Call";
                }
            }
        }

        // agent name matches
        if (isset($request_body->body) && strpos($request_body->body, 'Assign to agent:') !== false) {
            // preg_match('/Assign to agent: (.+)/', $request_body->body, $matches);
            preg_match('/Assign\s+to\s+agent:\s*(.+)/i', $request_body->body, $matches);
            //print_r($matches[1]); exit;
            if (isset($matches[1])) {
                $agent_name = $matches[1];
                //$agent_name = explode(' ', $agent_name);

                if (!$contact_name) {
                    $contact_name = 'Lead Received';
                }

            }
        }

        //print_r('outside'); exit;

        //echo 'done: '; print_r($agent_name); exit;

        // if we don't have a name, set it to "Unknown"
        if (!$contact_name) {
            $contact_name = 'Unknown';

            //  matches the term "missed" and "call"
            if (isset($request_body->body) && strpos($request_body->body, 'missed') !== false && strpos($request_body->body, 'call') !== false) {
                $contact_name = 'Missed Call';
                $sub_source_name = "Call";
            }

            // matches "just received a call"
            if (isset($request_body->body) && strpos($request_body->body, 'just received a call') !== false) {
                $contact_name = 'Missed Call';
                $sub_source_name = "Call";
            }

            //  matches the term "received" and "call"
            if (isset($request_body->body) && strpos($request_body->body, 'received') !== false && strpos($request_body->body, 'call') !== false) {
                $contact_name = 'Received A Call';
                $sub_source_name = "Call";
            
                // Check if "Talk time" is present and greater than 0s
                if (strpos($request_body->body, 'Talk time') !== false) {
                    $talk_time_position = strpos($request_body->body, 'Talk time');
                    $talk_time_end_position = strpos($request_body->body, 's', $talk_time_position);
                    
                    // Extract the talk time value
                    $talk_time = substr($request_body->body, $talk_time_position + 10, $talk_time_end_position - $talk_time_position - 10);
            
                    // Check if talk time is greater than 0s
                    if ((int) $talk_time > 0) {
                        // It's a received call
                        $contact_name = 'Received A Call';
                    } else {
                        // It's a missed call
                        $contact_name = 'Missed Call';
                    }
                }
            } else {
                $contact_name = 'Missed Call';
                $sub_source_name = "Call";
            }
            
        }

        // agent name matches 
        if (isset($request_body->body) && strpos($request_body->body, 'Dear') !== false && strpos($request_body->body, 'You just missed a call') !== false) {
            preg_match('/Dear (.+),\r\n You just missed a call/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $agent_name = $matches[1];
            }
        }
        if (isset($request_body->body) && strpos($request_body->body, 'Hello') !== false && strpos($request_body->body, 'A customer just tried to reach you') !== false) {
            preg_match('/Hello (.+),\r\n\r\nA customer just tried to reach you/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $agent_name = $matches[1];
            }
        }
        if (isset($request_body->body) && strpos($request_body->body, 'Dear') !== false && strpos($request_body->body, 'You just received a call') !== false) {
            preg_match('/Dear (.+),\r\n You just received a call/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $agent_name = $matches[1];
            }
        }

        if (preg_match('/([A-Za-z]+), you have a new lead!/', $request_body->body, $matches)) {
            if (isset($matches[1])) {
                $agent_name = $matches[1];
            }
        }
        

        if (isset($request_body->body) && strpos($request_body->body, 'Dear *') !== false) {
            preg_match('/Dear \*(.+)\*/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $agent_name = $matches[1];
            }
        }
        
        // match Missed By: <firstname> <lastname> Call
        if (isset($request_body->body) && strpos($request_body->body, 'Missed By:') !== false) {
            preg_match('/Missed By: (.+) Call/', $request_body->body, $matches);

            if (isset($matches[1])) {
                $agent_name = $matches[1];
                $sub_source_name = "Call";
                //$agent_name = explode(' ', $agent_name);
            }
        }

        // match "You just spoke to a customer through dubizzle"
        if (isset($request_body->body) && strpos($request_body->body, 'You just spoke to a customer through dubizzle') !== false) {
            // match "Hello <firstname> <lastname>," for agent
            preg_match('/Hello (.+),/', $request_body->body, $matches);
            if (isset($matches[1])) {
                $agent_name = $matches[1];
                //$agent_name = explode(' ', $agent_name);
            }

            preg_match('/Caller Number \+\d+/', $request_body->body, $matches);
            if (isset($matches[0])) {
                $contact_phone = str_replace('Caller Number +', '', $matches[0]);
                $contact_name = 'Dubizzle Call';
                $sub_source_name = "Call";
            }
        }

        // match "A customer just tried to reach you through dubizzle"
        if (isset($request_body->body) && strpos($request_body->body, 'A customer just tried to reach you through dubizzle') !== false) {
            // match "Hello <firstname> <lastname>," for agent
            preg_match('/Hello (.+),/', $request_body->body, $matches);
            if (isset($matches[1])) {
                $agent_name = $matches[1];
                //$agent_name = explode(' ', $agent_name);
            }

            preg_match('/Caller Number \+\d+/', $request_body->body, $matches);
            if (isset($matches[0])) {
                $contact_phone = str_replace('Caller Number +', '', $matches[0]);
                $contact_name = 'Dubizzle Call';
                $sub_source_name = "Call";
            }
        }
       
        // if we have an agent name, look up the agent with firstname/lastname
        if (isset($agent_name) && !is_array($agent_name)) {
            //echo $agent_name; exit;
            $agent = User::select('id')->where('name', $agent_name)->first();

            if ($agent) {
                $agent_id = $agent->id;
            }
        }
        //echo 'not set'; exit;
        // print_r($agent_id); exit;

        // if we don't have a phone or an email, error
        if (!$contact_phone && !$contact_email) {
            return $this->returnStatus('error', 'Missing phone or email');
        }

        // campaign source type matching
        // $campaign_contact_type = 7;
        // if ($campaign_match && isset($campaign_match['contact_type']))
        // {
        //     $campaign_contact_type = $campaign_match['contact_type'];
        // }


        if (isset($request_body->body) && strpos($request_body->body, 'Source Name:') !== false) {
            preg_match('/Source Name:\s*(.+)/', $request_body->body, $matches);
        
            if (isset($matches[1])) {
                $source_name = trim($matches[1]);
        
                // if ($source_name && $source_name == 'Facebook') {
                //     $source_id = 142;
                // }
            }
        }

        // let's do some final checks to categorise the source, if we can 
        // propertyfinder
        if (isset($request_body->body) && strpos($request_body->body, 'propertyfinder') !== false) {
            $source_name = 'Property Finder';
            //$this->source_id = 150;
        }
        // propsearch 
        if (isset($request_body->body) && strpos($request_body->body, 'propsearch') !== false) {
            $source_name = 'Prop Search';
            //$this->source_id = 132;
        }
        // Bayut.com
        if (isset($request_body->body) && strpos($request_body->body, 'bayut.com') !== false) {
            //$this->source_id = 112;
            $source_name = 'Bayut';
        }
        // dubizzle
        if (isset($request_body->body) && strpos($request_body->body, 'dubizzle') !== false) {
            //$this->source_id = 147;
            $source_name = 'Dubizzle';
        }
        // if the body contains "User Agent:", it's probably a web enquiry
        if (isset($request_body->body) && strpos($request_body->body, 'Contact Query from Starling Properties') !== false) {
            //$this->source_id = 139;
            $source_name = 'Website';
        }

        if($source_name != null){
            $get_source = Sources::where('name', $source_name)->first();
            $this->source_id = $get_source != null ? $get_source->id : $this->source_id;
        }

        // if there's a matched campaign that has a source ID, use that
        if (($campaign_match || $campaign_match != '') && isset($campaign_match->source_id))
        {
            $this->source_id = $campaign_match->source_id;
        }

        if($source_name != null && $sub_source_name != null){
            $get_sub_source = SubSources::where('name', $sub_source_name)->where('source_id', $this->source_id)->first();
            $this->sub_source_id = $get_sub_source != null ? $get_sub_source->id : $this->sub_source_id;
        }
        else if($source_name != null && $sub_source_name == null){
            $get_sub_source = SubSources::where('name', "Email")->where('source_id', $this->source_id)->first();
            $this->sub_source_id = $get_sub_source != null ? $get_sub_source->id : $this->sub_source_id;
        }

        $contact_id = 0;
        $contact = $this->import_contact($contact_name, $contact_phone, $contact_email, $agent_id, null, $this->source_id, $this->sub_source_id);
        if ($contact['status'] == 'error') {
            return $this->returnStatus('error', 'Error saving contact');
        } else {
            $contact_id = $contact['data']->id;
        }

        $lead_check = Leads::select('id')->where('contact_id', $contact_id)->where('updated_at', '>', 'DATE_SUB(NOW(), INTERVAL 15 MINUTE)')->first();
        
        if ($lead_check)
        {
            return $this->returnStatus('info', 'Duplicate request within 15 minutes, ignored');
        }

        // return $parsed_response;

        // insert the lead

        //return $agent_id ? 'Assigned' : 'Unassigned';

        $lead = new Leads;
        $lead->contact_id = $contact_id;
        $lead->refno = $this->get_next_refkey_lead();
        $lead->lead_stage = 'Cold';
        $lead->source_id = $this->source_id;
        $lead->sub_source_id = $this->sub_source_id;
        $lead->status_id = 7;
        $lead->agent_id = $agent_id;
        $lead->assign_status = $agent_id ? 'Assigned' : 'Unassigned';
        $lead->assigned_date = $agent_id ? date('Y-m-d H:i:s') : null;
        $lead->enquiry_date = date('Y-m-d H:i:s');
        $lead->campaign_id = $campaign_match != '' ? $campaign_match->id : null;
        $lead->hash = $hash;

        if ($external_refno != '') {
            $property = Listings::where('external_refno', trim($external_refno))->first();
            if ($property != null) {
                $lead->listing_id = $property->id;
                //$lead['property_id'] = $property['id'];
            }
            //$lead['property_refno'] = $external_refno;
            $lead->listing_refno = $external_refno;
        }
        $lead->save();
        $lead_id = $lead->id;

        activity()
            ->performedOn($lead)
            //->causedBy(auth()->user())
            ->withProperties('Lead imported from email.')
            ->log('created');

        // insert a lead note

        if($contact_enquiry != ''){
            Notes::create([
                'object' => 'Notes',
                'object_id' => $lead->id,
                'object_type' => Leads::class,
                'note' => $contact_enquiry,
                'type' => 'note',
                'status' => true,
            ]);
        }

        // insert a source lead note 
        if ($source_note != null)
        {
            Notes::create([
                'object' => 'Notes',
                'object_id' => $lead->id,
                'object_type' => Leads::class,
                'note' => "Enquiry source data: \n\n" . $source_note,
                'type' => 'note',
                'status' => true,
            ]);
        }
        
        $lead_details = new LeadDetails;
        $lead_details->lead_id = $lead_id;
        $lead_details->budget = $parsed_response->budget ? preg_replace('/[^0-9]/', '', $parsed_response->budget) : null;
        $lead_details->bedroom = $parsed_response->bedrooms ? trim($parsed_response->bedrooms) : null;
        $lead_details->bathroom = $parsed_response->bathrooms ? preg_replace('/[^0-9]/', '', $parsed_response->bathrooms) : null;

        // Check if $campaign_match is set and not null before accessing its properties
        if (!empty($campaign_match)) {
            $lead_details->community = $campaign_match->community;
            $lead_details->subcommunity = $campaign_match->subcommunity;
            $lead_details->property = $campaign_match->property;
        }
        else{
            // $community_id = null;
            // $sub_community_id = null;
            // $building_id = null;
            // if ($parsed_response->community) {
            //     $get_community = Community::where('name', 'like', '%' . $parsed_response->community . '%')->first();
            //     if($get_community != null){
            //         $community_id = $get_community->id;
            //     }
            // }

            // $lead_details->community = $parsed_response->bedrooms ? trim($parsed_response->bedrooms) : null;
            // $lead_details->subcommunity = $campaign_match->subcommunity;
            // $lead_details->property = $campaign_match->property;
        }

        $lead_details->save();


        //LeadDetails::create($lead_details);

        // notify agent
        if ($agent_id)
        {
            //$this->notify($agent_id);
            //$this->whatsAppService->notify($agent_id);
        }

        return $this->returnStatus('success', 'Lead saved successfully');
    }

    // function notify($agent_id, $template = 'new_crm_lead_v9')
    // {
    //     if (is_numeric($agent_id))
    //     {
    //         $agent = User::select('name', 'email', 'phone')->where('id', $agent_id)->first();
    //         $this->whatsapp_notify($agent->phone, $template, $agent->name, $agent->email);
    //     }
    // }

    private function get_next_refkey_lead(){
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

    function import_contact($name, $mobile, $email, $agent_id = null, $contact_type = null, $source_id = null, $sub_source_id = null)
	{
		// some initial values 
		$contact_id = 0;
		$status = 'error';

		// if we have a mobile number, let's remove any non-numeric characters
		if (!empty($mobile)) {
			$mobile = preg_replace('/[^0-9]/', '', $mobile);
		}

		// now let's see if we can find a contact with this mobile number (which could match either the phone or mobile field) or email address
        $query = contacts::select('email', 'phone', 'id');
        
		if (!empty($email)) {
			$query->orWhere('email', $email);
		}
		
		if (!empty($mobile)) {
            $query->orWhere('phone', $mobile);
            //$contact->orWhere('phone', $mobile);
		}

		$query = $query->first();

		if ($query) {
			$contact = $query;
			$contact_id = $contact->id;

			$update = array();

			if (!empty($email) && $email != $contact->email && empty($contact->email)) {
				$update['email'] = $email;
			} 
            // else if (!empty($email) && $email != $contact->email) {
			// 	$update['email2'] = $email;
			// }

			if (!empty($mobile))
			{ 
                if ($mobile != $contact->mobile && empty($contact->phone)) {
					$update['phone'] = $mobile;
				}
                // else if ($mobile != $contact->mobile && empty($contact->mobile)) {
				// 	$update['mobile'] = $mobile;
				// }
			}

			if (!empty($update)) {
                $contact->update($update);
			}

			$status = 'matched';
		} else {
			// the contact doesn't exist, so let's insert a new one
            $insert = new contacts;
            $insert->name = $name;
            $insert->contact_type = $contact_type;
            $insert->status = 1;
            $insert->email = $email;
            $insert->phone = $mobile;
            $insert->source_id = $source_id;
            $insert->sub_source_id = $sub_source_id;
            $insert->refno = $this->getNextRefNoContact();
            $insert->save();

			$contact_id = $insert->id;
			$status = 'created';
		}

		if ($contact_id > 0) {
            $query = contacts::select('id', 'refno')->where('id', $contact_id)->first();

			return ['status' => $status, 'data' => $query];
		} else {
			return ['status' => 'error', 'errors' => ['Unable to create or match contact']];
		}
	}

    private function getNextRefNoContact(){
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

    function respond()
    {
        $required_tag_name = 'completed_flow';

        $request_body = file_get_contents('php://input');
        $request_body = json_decode($request_body);

        if (!isset($request_body->action) || $request_body->action != 'add') {
            return $this->returnStatus('error', 'Invalid action');
        } else if (!isset($request_body->tag) || $request_body->tag != $required_tag_name) {
            return $this->returnStatus('error', 'Invalid tag');
        }

        $get_source = Sources::where('name', 'WhatsApp')->first();

        $this->source_id = $get_source ? $get_source->id : null; // WhatsApp

        $this->bypass_auth = true;
        return $this->lead();
    }

    public function lead()
	{
        $request_body = file_get_contents('php://input');
        $request_body = json_decode($request_body);

        if (!$this->bypass_auth) {
            $headers = getallheaders();
            if (!isset($headers['Authorization'])) {
                return $this->returnStatus('error', 'Missing authorization header');
            }

            if ($headers['Authorization'] != 'Bearer ' . $this->token) {
                return $this->returnStatus('error', 'Invalid authorization header');
            }
        }

        $_REQUEST = array_change_key_case($_REQUEST, CASE_LOWER);

        $name = $phone = $email = $enquiry = '';
        $source_id = $this->source_id;
        $source_name = null;

        // if this is a tilda submission, set source_id to 139
        if (isset($_REQUEST['name']) && !isset($request_body->event_type)) {
            //$source_id = 139;
            $get_source = Sources::where('name', 'Website')->first();
            $source_id = $get_source ? $get_source->id : null;
        }

        // try and assign name based on options
        if (isset($request_body->name)) {
            $name = $request_body->name;
        } else if (isset($_REQUEST['name'])) {
            $name = $_REQUEST['name'];
        } else if (isset($request_body->contact))
        {
            $name = $request_body->contact->firstName . ' ' . $request_body->contact->lastName;
        }

        // try and assign phone based on options
        if (isset($request_body->phone)) {
            $phone = $request_body->phone;
        } else if (isset($request_body->mobile)) {
            $phone = $request_body->mobile;
        } else if (isset($_REQUEST['phone'])) {
            $phone = $_REQUEST['phone'];
        } else if (isset($_REQUEST['mobile'])) {
            $phone = $_REQUEST['mobile'];
        } else if (isset($request_body->contact))
        {
            $phone = $request_body->contact->phone;
        }

        // try and assign email based on options
        if (isset($request_body->email)) {
            $email = $request_body->email;
        } else if (isset($_REQUEST['email'])) {
            $email = $_REQUEST['email'];
        } else if (isset($request_body->contact))
        {
            $email = $request_body->contact->email;
        }

        // try and assign enquiry based on options
        if (isset($request_body->enquiry)) {
            $enquiry = $request_body->enquiry;
        } else if (isset($_REQUEST['enquiry'])) {
            $enquiry = $_REQUEST['enquiry'];
        } else if (isset($_REQUEST['textarea'])) {
            $enquiry = $_REQUEST['textarea'];
        } else if (isset($request_body->contact))
        {
            $enquiry = 'Automatically generated from respond.io lead webhook';
        }

        // try and assign source_id based on options
        if (isset($request_body->source_id)) {
            $source_id = $request_body->source_id;
        } else if (isset($_REQUEST['source_id'])) {
            $source_id = $_REQUEST['source_id'];
        }

        // if we have a campaign value, let's include it
        $campaign = '';
        if (isset($request_body->campaign)) {
            $campaign = $request_body->campaign;
        } else if (isset($_REQUEST['campaign'])) {
            $campaign = $_REQUEST['campaign'];
        }

        // make sure we have a name, and either an email address or phone number
        if ($name == '') {
            return $this->returnStatus('error', 'Missing name');
        } else if ($email == '' && $phone == '') {
            return $this->returnStatus('error', 'Missing contact method');
        }

        // validate email
        if ($email)
        {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->returnStatus('error', 'Invalid email address');
            }
        }

        // required fields
        $required_fields = [
            'name' => 'Name',
            'email' => 'Email',
            'mobile' => 'Mobile',
            'enquiry' => 'Enquiry'
        ];

        // optional fields
        $optional_fields = [
            'source_id' => 'Source ID'
        ];

        // any fields that aren't required or optional, let's append them to the enquiry
        $additional = '';
        if ($request_body)
        {
            foreach ($request_body as $key => $value) {
                if (!isset($required_fields[$key]) && !isset($optional_fields[$key])) {
                    if (is_string($value)) {
                        $additional .= $key . ': ' . $value . "\n";
                    } elseif (is_array($value) || is_object($value)) {
                        $additional .= $key . ': ' . json_encode($value) . "\n";
                    }
                }
            }
        } else {
            foreach ($_REQUEST as $key => $value) {
                if (!isset($required_fields[$key]) && !isset($optional_fields[$key])) {
                    $additional .= $key . ': ' . $value . "\n";
                }
            }
        }

        if ($additional != '') {
            $enquiry .= "\n\nAdditional request fields:\n" . $additional;
        }

        // let's upsert the contact
        $contact_id = 0;
        $contact = $this->import_contact($name, $phone, $email, null, null, $source_id);
        // if (!$contact) {
        //     $this->returnStatus('error', 'Error saving contact');
        // } else {
        //     $contact_id = $contact['data']['id'];
        // }

        if ($contact['status'] == 'error') {
            return $this->returnStatus('error', 'Error saving contact');
        } else {
            $contact_id = $contact['data']->id;
        }

        // get campaign

        $get_campaign = Campaigns::where('name', $campaign)->first();

        // insert the lead

        $lead = new Leads;
        $lead->contact_id = $contact_id;
        $lead->refno = $this->get_next_refkey_lead();
        $lead->lead_stage = 'Cold';
        $lead->source_id = $source_id;
        $lead->status_id = 7;
        $lead->assign_status = 'Unassigned';
        $lead->enquiry_date = date('Y-m-d H:i:s');
        $lead->campaign_id = $get_campaign ? $get_campaign->id : null;
        $lead->save();

        // insert a lead note 

        Notes::create([
            'object' => 'Notes',
            'object_id' => $lead->id,
            'object_type' => Leads::class,
            'note' => $enquiry,
            'type' => 'note',
            'status' => true,
        ]);

        return $this->returnStatus('success', 'Lead saved successfully.');
	}

    public function reassignLeads()
    {
        $get_status = Statuses::where('name', 'Not Yet Contacted')->first();
        $agent = User::where('name', 'Gridi Sula')->first();
        
        $getSourceIds = Sources::whereIn('name', ['Bayut', 'Propsearch', 'Dubizzle', 'Call', 'Property Finder'])->pluck('id')->toArray();
        // Select leads with at least one note
        $leads = Leads::select('leads.*', 'users.name as lead_agent_name')
            ->join('users', 'users.id', '=', 'leads.agent_id')
            ->where('leads.status_id', $get_status->id)
            ->where('leads.assign_status', 'Assigned')
            ->whereIn('leads.source_id', $getSourceIds)
            // ->whereNotExists(function ($query) {
            //     $query->select(DB::raw(1))
            //           ->from('notes')
            //           ->whereRaw('notes.object_id = leads.id')->where('object_type', Leads::class);
            // })
            ->where('leads.agent_id', '!=', $agent->id)
            ->get();
        
        // return;

        foreach($leads as $row){

            // Check if added_date is greater than 24 hours from now
            $addedDate = strtotime($row->assigned_date);
            $currentDate = time();
            $twentyFourHoursAgo = $currentDate - (24 * 3600); // 24 hours in seconds

            if ($addedDate < $twentyFourHoursAgo) {

                $lead = Leads::where('refno', $row->refno)->first();
                $lead_id = $lead->id;

                //echo $lead_id.'<br>';

                $old_agent = $row->lead_agent_name;
                $new_agent = $agent->name;

                // Update lead_agent with the new agent's ID

                //$maindata['updated_date'] = date('Y-m-d H:i:s');
                $maindata['lead_agent'] = $agent->id;

                $update = Leads::find($lead_id);
                $update->agent_id = $agent->id;
                $update->assign_status = 'Assigned';
                $update->assigned_date = date('Y-m-d H:i:s');
                $update->accepted_date = null;
                $update->save();
                
                echo 'Lead agent updated for lead refno ' . $row->refno . '<br>';

                $log = 'Lead reassigned from Agent "'.$old_agent.'" to "'.$new_agent.'" by auto reassign system. Reason no activity on lead.';

                activity()
                    ->performedOn($lead)
                    //->causedBy(auth()->user())
                    ->withProperties(['details' => $log])
                    ->log('updated');

            }
            else{
                echo 'nothing <br>';
            }

        }
    }

    public function reassignLeadsOffPlan()
    {
        $get_status = Statuses::where('name', 'Not Yet Contacted')->first();
        //$agent = User::where('name', 'Gridi Sula')->first();
        
        //$getSourceIds = Sources::whereIn('name', ['Bayut', 'Propsearch', 'Dubizzle', 'Call', 'Property Finder'])->pluck('id')->toArray();
        // Select leads with at least one note
        $leads = Leads::select('leads.*', 'users.name as lead_agent_name')
            ->join('users', 'users.id', '=', 'leads.agent_id')
            ->where('leads.status_id', $get_status->id)
            ->where('leads.assign_status', 'Assigned')
            ->where('campaign_id', '!=', null)
            // ->whereNotExists(function ($query) {
            //     $query->select(DB::raw(1))
            //           ->from('notes')
            //           ->whereRaw('notes.object_id = leads.id')->where('object_type', Leads::class);
            // })
            //->where('leads.agent_id', '!=', $agent->id)
            ->get();
        
        // return;

        foreach($leads as $row){

            $campaign = Campaigns::with('users')->where('id', $row->campaign_id)->first();
            $agent_id = $row->agent_id;
            if ($campaign)
            {
                if ($campaign->users && count($campaign->users) > 0)
                {
                    // Get the array of agent IDs
                    $agents = $campaign->users->pluck('id')->toArray();

                    //return $agents;

                    //return $campaign->assignment_pointer;
                    $next_assignment_pointer = 1;
                    if (isset($agents[$campaign->assignment_pointer])) {
                        // Get the agent ID using the assignment pointer
                        $agent_id = $agents[$campaign->assignment_pointer];

                        // Update the campaign's assignment_pointer column with the next user ID
                        $next_assignment_pointer = ($campaign->assignment_pointer + 1);
                        //return $agent_id;
                        $campaign->update([
                            'assignment_pointer' => $next_assignment_pointer,
                        ]);
                    } else {
                        // If the assignment pointer is invalid, reset it to the first agent
                        $agent_id = $agents[0];
                        $next_assignment_pointer = 1;
                        $campaign->update([
                            'assignment_pointer' => $next_assignment_pointer,
                        ]);
                    }

                    // re assign
                    $agent = User::where('id', $agent_id)->first();

                    // Check if added_date is greater than 24 hours from now
                    $addedDate = strtotime($row->assigned_date);
                    $currentDate = time();
                    $twentyFourHoursAgo = $currentDate - (24 * 3600); // 24 hours in seconds

                    if ($addedDate < $twentyFourHoursAgo) {

                        $lead = Leads::where('refno', $row->refno)->first();
                        $lead_id = $lead->id;

                        //echo $lead_id.'<br>';

                        $old_agent = $row->lead_agent_name;
                        $new_agent = $agent->name;

                        // Update lead_agent with the new agent's ID

                        $update = Leads::find($lead_id);
                        $update->agent_id = $agent->id;
                        $update->assign_status = 'Assigned';
                        $update->assigned_date = date('Y-m-d H:i:s');
                        $update->accepted_date = null;
                        $update->save();
                        
                        echo 'Lead agent updated for lead refno ' . $row->refno . '<br>';

                        $log = 'Lead reassigned from Agent "'.$old_agent.'" to "'.$new_agent.'" by auto reassign system. Reason no activity on lead.';

                        activity()
                            ->performedOn($lead)
                            //->causedBy(auth()->user())
                            ->withProperties(['details' => $log])
                            ->log('updated');

                    }
                    else{
                        echo 'nothing <br>';
                    }


                }
            } 

        }
    }

    public function brochure($refno){
        $path = 'public/';

        if($refno == null || $refno == ''){
            return 'nothing';
        }

        $listing = Listings::with('listing_agent', 'amenities', 'images', 'category')->where('refno', $refno)->where('agent_id', '!=', null)->whereHas('status', function ($query) use ($refno) {
            $query->where('name', '!=', "Prospect");
        })->first();

        if($listing){
            $details = [];
            $details['location'] = $listing->location_details();
            //return $details['location'];
            $frequency = null;
            if ($listing->property_for != 'sale') {
                switch ($listing->frequency)
                {
                    case 'Yearly':
                        $frequency = '/ Year';
                        break;
                    case 'Monthly':
                        $frequency = '/ Month';
                        break;
                    case 'Weekly':
                        $frequency = '/ Week';
                        break;
                    case 'Daily':
                        $frequency = '/ Day';
                        break;
                }
            }

            $details['price'] = number_format($listing->price);

            // pretty up the sqft
            $details['bua'] = number_format($listing->bua);

            $property_for = $listing->property_for == 'sale' ? 'For Sale' : 'Rental';
            $details['agent'] = $listing->listing_agent;
            $details['agent_image'] = $listing->listing_agent->profileImage();
            $details['category'] = $listing->category;
            $details['images'] = $listing->images;
            $details['amenities'] = $listing->amenities;
            $details['listing'] = $listing;

            //dd($details['images']);

            $dompdf = Pdf::loadView('admin/listings/brochure', $details);;
            // (Optional) Setup the paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream($refno . '.pdf', ['Attachment' => false]);
        }
        else{
            return 'not found';
        }

    }

    public function listingPreview(){

    }
}
