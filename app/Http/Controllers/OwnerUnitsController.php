<?php

namespace App\Http\Controllers;

use App\Models\owner_units;
use Illuminate\Http\Request;

class OwnerUnitsController extends Controller
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
     * @param  \App\Models\owner_units  $owner_units
     * @return \Illuminate\Http\Response
     */
    public function show(owner_units $owner_units)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\owner_units  $owner_units
     * @return \Illuminate\Http\Response
     */
    public function edit(owner_units $owner_units)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\owner_units  $owner_units
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, owner_units $owner_units)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\owner_units  $owner_units
     * @return \Illuminate\Http\Response
     */
    public function destroy(owner_units $owner_units)
    {
        //
    }
}
