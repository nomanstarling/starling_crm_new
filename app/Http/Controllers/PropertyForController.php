<?php

namespace App\Http\Controllers;

use App\Models\property_for;
use Illuminate\Http\Request;

class PropertyForController extends Controller
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
     * @param  \App\Models\property_for  $property_for
     * @return \Illuminate\Http\Response
     */
    public function show(property_for $property_for)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\property_for  $property_for
     * @return \Illuminate\Http\Response
     */
    public function edit(property_for $property_for)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\property_for  $property_for
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, property_for $property_for)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\property_for  $property_for
     * @return \Illuminate\Http\Response
     */
    public function destroy(property_for $property_for)
    {
        //
    }
}
