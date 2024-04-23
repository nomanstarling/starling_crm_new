@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            Settings
        </h2>

        <div class="card-toolbar">
            <a class="btn btn-flex btn-primary btn-sm mr-1" href="{{ 'dashboard' }}">
                <i class="ki-duotone ki-plus fs-3"></i>
                Back to Dashboard
            </a>

        </div>
    </div>
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

            <form id="editForm" action="{{ route('settings.update') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="site_name" class="form-label">WebSite Title</label>
                        <input type="text" class="form-control form-control-sm" id="site_name" name="site_name" value="{{ $settings->get('site_name') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control form-control-sm phone" id="phone" name="phone" value="{{ $settings->get('phone') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="whatsapp" class="form-label">WhatsApp</label>
                        <input type="text" class="form-control form-control-sm phone" id="whatsapp" name="whatsapp" value="{{ $settings->get('whatsapp') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="landline" class="form-label">Landline</label>
                        <input type="text" class="form-control form-control-sm phone" id="landline" name="landline" value="{{ $settings->get('landline') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control form-control-sm" id="address" name="address" value="{{ $settings->get('address') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="short_name" class="form-label">Short Name</label>
                        <input type="text" class="form-control form-control-sm" id="short_name" name="short_name" value="{{ $settings->get('short_name') }}" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="theme_color" class="form-label">Primary Color</label>
                        <input type="color" class="form-control form-control-sm" id="primary_color" name="primary_color" value="{{ $settings->get('primary_color') }}">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="theme_color" class="form-label">Secondary Color</label>
                        <input type="color" class="form-control form-control-sm" id="secondary_color" name="secondary_color" value="{{ $settings->get('secondary_color') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="calls_goal" class="form-label">Calls Goal</label>
                        <div class="input-group mb-5 input-group-sm">
                            <input type="text" class="form-control" id="calls_goal" name="calls_goal" aria-describedby="basic-addon3" value="{{ $settings->get('calls_goal') }}"/>
                            <span class="input-group-text" id="basic-addon3">/ month</span>
                            <span class="input-group-text" id="basic-addon3"><span class="calls_day"></span> / day</span>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="off_market_goal" class="form-label">Off-Market Listings</label>
                        <div class="input-group mb-5 input-group-sm">
                            <input type="text" class="form-control" id="off_market_goal" name="off_market_goal" aria-describedby="basic-addon3" value="{{ $settings->get('off_market_goal') }}"/>
                            <span class="input-group-text" id="basic-addon3">/ month</span>
                            <span class="input-group-text" id="basic-addon3"><span class="off_market_day"></span> / day</span>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="published_goal" class="form-label">Published Listings</label>
                        <div class="input-group mb-5 input-group-sm">
                            <input type="text" class="form-control" id="published_goal" name="published_goal" aria-describedby="basic-addon3" value="{{ $settings->get('published_goal') }}"/>
                            <span class="input-group-text" id="basic-addon3">/ month</span>
                            <span class="input-group-text" id="basic-addon3"><span class="live_listings_day"></span> / day</span>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="separator my-3"></div>
                        <h4>API Settings</h4>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="open_ai_key" class="form-label">Open AI Key</label>
                        <input type="text" class="form-control form-control-sm" id="open_ai_key" name="open_ai_key" value="{{ $settings->get('open_ai_key') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="respond_io_key" class="form-label">Respond.io Secret Key</label>
                        <input type="text" class="form-control form-control-sm" id="respond_io_key" name="respond_io_key" value="{{ $settings->get('respond_io_key') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="google_secret" class="form-label">Google+ Auth Secret</label>
                        <input type="text" class="form-control form-control-sm" id="google_secret" name="google_secret" value="{{ $settings->get('google_secret') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="google_api_key" class="form-label">Google+ Auth API Key</label>
                        <input type="text" class="form-control form-control-sm" id="google_api_key" name="google_api_key" value="{{ $settings->get('google_api_key') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                    </div>
                </div>
            </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function call_goals() {
        var calls_goal = $('#calls_goal').val();
        var calls_per_day = Math.round(calls_goal / 22);
        $('.calls_day').text(calls_per_day);
    }

    call_goals();

    $('#calls_goal').on('input', function() {
        call_goals();
    });

    function off_market_goals() {
        var off_market_goal = $('#off_market_goal').val();
        var off_market_per_day = Math.round(off_market_goal / 22);
        $('.off_market_day').text(off_market_per_day);
    }

    off_market_goals();

    $('#off_market_goal').on('input', function() {
        off_market_goals();
    });

    function live_listings_goals() {
        var published_goal = $('#published_goal').val();
        var live_listings_per_day = Math.round(published_goal / 22);
        $('.live_listings_day').text(live_listings_per_day);
    }

    live_listings_goals();

    $('#published_goal').on('input', function() {
        live_listings_goals();
    });

    

    
</script>
@endsection