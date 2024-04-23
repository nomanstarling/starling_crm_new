@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header px-5 py-3">
        <h2 class="card-title fw-bold">
            Profile
        </h2>
    </div>
    <div class="card-body">
        <form action="{{ route('profilePost') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3 form-group">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-sm form-control-solid border" value="{{ auth()->user()->name }}">
                </div>

                <div class="col-md-4 mb-3 form-group">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-sm form-control-solid border" value="{{ auth()->user()->email }}">
                </div>

                <div class="col-md-4 mb-3 form-group">
                    <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control form-control-sm form-control-solid border phone" value="+{{ auth()->user()->phone }}">
                </div>

                <div class="col-md-4 mb-3 form-group">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" id="gender" class="form-control form-control-sm form-control-solid border" placeholder="Select Gender" required>
                        <option value="">Select gender</option>
                        <option value="Male" {{ auth()->user()->gender == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ auth()->user()->gender == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3 form-group">
                    <label for="phone" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="user_name" class="form-control form-control-sm form-control-solid border disabled" disabled value="{{ auth()->user()->user_name }}">
                </div>

                <div class="col-md-4 mb-3 form-group">
                    <label for="phone" class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control form-control-sm form-control-solid border mb-2" autocomplete="off">
                    <span class="text-muted">Do only if you want to change the password, else leave it blank.</span>
                </div>
            </div>

            <div class="form-group text-end mt-4">
                <button class="btn btn-primary btn-sm">Save</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
@if ($errors->any())
  <script>
    toastr.error('{{ $errors->first('error') }}');
  </script>
@endif


@if (session('success'))
  <script>
    toastr.success('{{ session('success') }}');
  </script>
@endif

@if (session('error'))
  <script>
    toastr.error('{{ session('error') }}');
  </script>
@endif
@endsection