<?php

namespace App\Http\Controllers;

use App\Models\teams;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function calls(){
        $this->authorize('stats_team_calls');
        return view('admin.stats.calls');
    }

    // public function getCalls(){
    //     $teams = teams::with('users')->get();
    //     foreach($teams as $team){
    //         foreach($team->users as $team_user){
    //             echo count($team_user->calls).'<br>';
    //         }
    //     }
    // }

    // public function getCalls()
    // {
    //     $teams = teams::with('team_leader', 'users.calls')->get();
    //     return response()->json(['teams' => $teams]);
    // }

    // public function getCalls()
    // {
    //     $teams = teams::with(['users' => function ($query) {
    //         $query->withCount(['calls', 'calls as answered_calls' => function ($query) {
    //             $query->whereNotNull('answer_date');
    //         }]);
    //     }])->get();

    //     return response()->json(['teams' => $teams]);
    // }

    // public function getCalls()
    // {
    //     $teams = teams::with(['users' => function ($query) {
    //         $query->withCount(['outGoingCalls as total_calls', 'ansCalls as answered_calls']);
    //     }])->get();
    
    //     $teamsWithDetails = $teams->map(function ($team) {
    //         $teamDetails = $team->toArray();
    //         $usersWithDetails = $team->users->map(function ($user) {
    //             return [
    //                 'user_id' => $user->id,
    //                 'name' => $user->name,
    //                 'profile_image' => $user->profileImage(),
    //                 'total_calls' => $user->total_calls,
    //                 'answered_calls' => $user->answered_calls,
    //                 'calls_goal' => $user->callsGoal(),
    //             ];
    //         });
    
    //         $teamDetails['users'] = $usersWithDetails->toArray();
    //         return $teamDetails;
    //     });
    
    //     return response()->json(['teams' => $teamsWithDetails]);
    // }

    public function getCalls()
    {
        $teams = teams::with(['users' => function ($query) {
            $query->withCount(['outGoingCalls as total_calls', 'ansCalls as answered_calls']);
        }])->get();

        $teamsWithDetails = $teams->map(function ($team) {
            $teamDetails = $team->toArray();
            $teamDetails['users'] = $team->users->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'profile_image' => $user->profileImage(),
                    'total_calls' => $user->total_calls,
                    'answered_calls' => $user->answered_calls,
                    'calls_goal' => $user->callsGoal(),
                ];
            })->sortByDesc('total_calls')->values()->toArray();

            return $teamDetails;
        })->sortByDesc(function ($team) {
            return count($team['users']);
        })->values();

        return response()->json(['teams' => $teamsWithDetails]);
    }

    



}
