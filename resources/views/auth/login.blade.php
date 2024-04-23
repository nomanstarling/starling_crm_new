@extends('layouts.auth_app')

@section('content')

<div class="d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px">
    <div class="d-flex flex-center bg-body py-15 px-15 rounded">
        <form method="POST" action="{{ route('login') }}" class="form w-100" novalidate="novalidate" id="">
            @csrf
            <div class="text-center mb-8">
                <h1 class="text-gray-900 fw-bolder mb-3">
                    Sign In
                </h1>
                <div class="text-gray-500 fw-semibold fs-6">
                    Login and look into your Leads
                </div>
            </div>
            
            <div class="row g-3 mb-9">
                <div class="col-md-6 offset-3">
                    
                    <a href="{{ route('login.google') }}" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                        <img alt="Logo" src="{{ asset('assets/media/svg/brand-logos/google-icon.svg') }}" class="h-15px me-3" />
                        Sign in with Google
                    </a>
                </div>
            </div>
            <div class="separator separator-content my-14">
                <span class="w-200px text-gray-500 fw-semibold fs-7">Or with user name</span>
            </div>

            <div class="fv-row mb-8">
                <input type="text" placeholder="Username" name="user_name" value="{{ old('email') }}" required autocomplete="user_name" autofocus class="form-control bg-transparent @error('user_name') is-invalid @enderror" />
                @error('user_name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="fv-row mb-3">
                <input type="password" placeholder="Password" name="password" required autocomplete="current-password" class="form-control bg-transparent @error('password') is-invalid @enderror" />
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="fv-row mt-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="form-check-label" for="remember">
                        {{ __('Remember Me') }}
                    </label>
                </div>
            </div>

            <div class="d-grid mt-6">
                <button type="submit" id="kt_sign_in_submit" class="btn btn-dark">
                    <span class="indicator-label">
                        Sign In</span>
                    <span class="indicator-progress">
                        Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </div>
        </form>
    </div>
    
</div>
@endsection
