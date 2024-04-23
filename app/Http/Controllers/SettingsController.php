<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Spatie\Settings\Settings;
use Spatie\LaravelSettings\Settings;
use Spatie\Valuestore\Valuestore;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $settings = Valuestore::make(config('settings.path'));

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Validate the form input as needed

        // Update the settings
        $settings = Valuestore::make(config('settings.path'));

        $settings->put([
            'site_name' => $request->input('site_name'),
            'phone' => $request->input('phone'),
            'whatsapp' => $request->input('whatsapp'),
            'landline' => $request->input('landline'),
            'address' => $request->input('address'),
            'short_name' => $request->input('short_name'),
            'primary_color' => $request->input('primary_color'),
            'secondary_color' => $request->input('secondary_color'),
            'calls_goal' => $request->input('calls_goal'),
            'off_market_goal' => $request->input('off_market_goal'),
            'published_goal' => $request->input('published_goal'),

            'open_ai_key' => $request->input('open_ai_key'),
            'respond_io_key' => $request->input('respond_io_key'),
            'google_secret' => $request->input('google_secret'),
            'google_api_key' => $request->input('google_api_key'),
        ]);

        // Optionally add more validation and error handling as needed

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
