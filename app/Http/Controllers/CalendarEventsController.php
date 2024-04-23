<?php

namespace App\Http\Controllers;

use App\Models\calendar_events;
use Illuminate\Http\Request;

class CalendarEventsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\calendar_events  $calendar_events
     * @return \Illuminate\Http\Response
     */
    public function show(calendar_events $calendar_events)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\calendar_events  $calendar_events
     * @return \Illuminate\Http\Response
     */
    public function edit(calendar_events $calendar_events)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\calendar_events  $calendar_events
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, calendar_events $calendar_events)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\calendar_events  $calendar_events
     * @return \Illuminate\Http\Response
     */
    public function destroy(calendar_events $calendar_events)
    {
        //
    }
}
