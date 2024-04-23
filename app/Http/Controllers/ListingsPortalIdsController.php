<?php

namespace App\Http\Controllers;

use App\Models\listings_portal_ids;
use Illuminate\Http\Request;

class ListingsPortalIdsController extends Controller
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
     * @param  \App\Models\listings_portal_ids  $listings_portal_ids
     * @return \Illuminate\Http\Response
     */
    public function show(listings_portal_ids $listings_portal_ids)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\listings_portal_ids  $listings_portal_ids
     * @return \Illuminate\Http\Response
     */
    public function edit(listings_portal_ids $listings_portal_ids)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\listings_portal_ids  $listings_portal_ids
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, listings_portal_ids $listings_portal_ids)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\listings_portal_ids  $listings_portal_ids
     * @return \Illuminate\Http\Response
     */
    public function destroy(listings_portal_ids $listings_portal_ids)
    {
        //
    }
}
