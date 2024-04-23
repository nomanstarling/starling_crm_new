@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header py-4">
        <h2 class="card-title fw-bold">
            Import Leads
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
        <form action="{{ route('leads.importPost') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="" class="form-label">Select Excel File</label>
                <input type="file" name="file" class="form-control form-control-sm form-control-solid border mt-1" required>
                <a href="{{ asset('public/storage/uploads/files/leads_import_sample.xlsx') }}" class="d-block mt-2 text-primary fw-bold text-end">
                    <i class="fa fa-file-excel text-primary"></i>
                    Download the example file for Leads Import
                </a>
                <button type="submit" class="btn btn-primary btn-sm mt-3">Import</button>
            </div>
        </form>

        @if(session('output'))
            <div class="bg-light p-4 mt-3 rounded response_output">
                @php
                    $output = session('output');
                @endphp

                Leads imported successfully.<br>
                @foreach($output as $message)
                    {{ $message }}<br>
                @endforeach
            </div>
        @endif

    </div>
</div>

@endsection

@section('scripts')
<script>

</script>
@endsection
