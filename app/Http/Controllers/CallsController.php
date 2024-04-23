<?php

namespace App\Http\Controllers;

use App\Models\crm_calls;
use App\Models\Teams;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class CallsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('stats_all_calls');
        $firstDate = crm_calls::min('created_at');
        return view('admin.crm_calls.index', compact('firstDate'));
    }

    public function getCalls(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $startAnswerDate = $request->query('startAnswerDate');
        $endAnswerDate = $request->query('endAnswerDate');

        $calls = crm_calls::with('user');

        if ($startDate && $endDate) {
            $calls->where(function ($query) use ($startDate, $endDate) {
                if ($startDate === $endDate) {
                    $query->whereDate('start_date', $startDate);
                } else {
                    $query->whereBetween('start_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                }
            });
        }

        if ($startAnswerDate && $endAnswerDate) {
            $calls->where(function ($query) use ($startAnswerDate, $endAnswerDate) {
                if ($startAnswerDate === $endAnswerDate) {
                    $query->whereDate('answer_date', $startAnswerDate);
                } else {
                    $query->whereBetween('answer_date', [$startAnswerDate . ' 00:00:00', $endAnswerDate . ' 23:59:59']);
                }
            });
        }

        if ($request->input('agent') != null) {
            $calls->whereHas('user', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('agent') . '%');
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
                    $calls->whereIn('user_id', $user_ids);
                }
            }
            else{
                $calls->where('user_id', auth()->user()->id);
            }
        }

        $totalRecords = $calls->count(); // Fetch the total count
        $perPage = $request->input('length', 10); // Number of records per page
        
        $calls = $calls->latest('updated_at')
            ->skip(($request->input('start', 0) / $perPage) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'draw' => (int)$request->input('draw'),
            'recordsTotal' => $totalRecords, // Use the total count
            'recordsFiltered' => $totalRecords,
            'data' => $calls,
            'pagination' => [
                'total' => $totalRecords,
                'perPage' => $perPage,
                'currentPage' => $request->input('start', 0) / $perPage + 1,
            ],
        ]);

        // $calls = $calls->latest('updated_at')->get();
        // return response()->json(['calls' => $calls]);
    }
}
