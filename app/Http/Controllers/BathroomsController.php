<?php

namespace App\Http\Controllers;

use App\Models\bathrooms;
use Illuminate\Http\Request;

class BathroomsController extends Controller
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
     * @param  \App\Models\bathrooms  $bathrooms
     * @return \Illuminate\Http\Response
     */
    public function show(bathrooms $bathrooms)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\bathrooms  $bathrooms
     * @return \Illuminate\Http\Response
     */
    public function edit(bathrooms $bathrooms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\bathrooms  $bathrooms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, bathrooms $bathrooms)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\bathrooms  $bathrooms
     * @return \Illuminate\Http\Response
     */
    public function destroy(bathrooms $bathrooms)
    {
        //
    }
}
