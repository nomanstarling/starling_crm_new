<?php

namespace App\Http\Controllers;

use App\Models\media_gallery;
use Illuminate\Http\Request;

class MediaGalleryController extends Controller
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
     * @param  \App\Models\media_gallery  $media_gallery
     * @return \Illuminate\Http\Response
     */
    public function show(media_gallery $media_gallery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\media_gallery  $media_gallery
     * @return \Illuminate\Http\Response
     */
    public function edit(media_gallery $media_gallery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\media_gallery  $media_gallery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, media_gallery $media_gallery)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\media_gallery  $media_gallery
     * @return \Illuminate\Http\Response
     */
    public function destroy(media_gallery $media_gallery)
    {
        //
    }
}
