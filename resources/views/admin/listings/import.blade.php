@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header py-3 px-5">
        <h2 class="card-title fw-bold">
            Import Listings
        </h2>
    </div>

    <div class="card-body">
        <form id="editForm" action="{{ route('listings.import') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-12">
                    <label for="">Select Excel file</label>
                    <input type="file" class="form-control form-control-sm mt-3" name="file">
                    <div class="d-flex justify-content-between mt-2">
                        <p class="text-danger">Listing matches with: Property Type, Property For, Unit No, Community, Sub Community and Building.</p>
                        <a href="{{ asset('public/storage/uploads/files/istings_import_format.xlsx') }}" class="d-block text-primary fw-bold text-end">
                            <i class="fa fa-file-excel text-primary"></i>
                            Download the example file for Listings Import
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm mt-4">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection

@section('scripts')

@endsection
