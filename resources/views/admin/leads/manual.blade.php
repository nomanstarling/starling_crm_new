@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header py-4">
        <h2 class="card-title fw-bold">
            Import Lead Manually
        </h2>
    </div>

    <div class="card-body">
        @if(session('success'))
        <!-- Success Alert -->
        <div class="alert alert-success message-box mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <!-- Error Alert -->
        <div class="alert alert-danger message-box mb-4" role="alert">
            {{ session('error') }}
        </div>
        @endif
        <form action="{{ route('leads.manualPost') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="">Lead Content</label>
                <textarea name="content" required id="content" class="form-control form-control-solid mt-4 border" rows="10"></textarea>

                <button type="submit" class="btn btn-primary btn-sm mt-3">Create Lead</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>

</script>
@endsection
