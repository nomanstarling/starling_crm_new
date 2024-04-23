@extends('layouts.app')

@section('content')
@php
    $columns = [
        'type' => ['index' => 1, 'visible' => true],
        'contact' => ['index' => 2, 'visible' => true],
        'refno' => ['index' => 3, 'visible' => true],
        'source' => ['index' => 4, 'visible' => true],
        'sub_source' => ['index' => 5, 'visible' => true],
        'created_by' => ['index' => 6, 'visible' => true],
        'updated_by' => ['index' => 7, 'visible' => true],
        'created_date' => ['index' => 8, 'visible' => false],
        'last_update' => ['index' => 9, 'visible' => false],
        'actions' => ['index' => 10, 'visible' => true],
    ];
@endphp

<style>
    .dropzone{
        min-height: 60px !important;
    }
</style>

<input type="hidden" name="update_perm" id="update_perm" value="{{ auth()->user()->can('contacts_update') ? 'true' : 'false' }}">

<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            {{ isset($_GET['status']) ? ucfirst($_GET['status']) : 'Active' }} {{ isset($_GET['type']) ? ucfirst($_GET['type']).'s' : 'All Contacts' }}
            @can('contacts_delete')
                <div class="ml-4 bulkActions d-none">
                    @if(isset($_GET['status']) && $_GET['status'] == 'deleted')
                        <button class="btn btn-flex btn-danger btn-sm btn-icon" id="bulkRestoreBtn" type="button">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </button>
                    @else
                        <button class="btn btn-flex btn-danger btn-sm btn-icon" id="bulkDeleteBtn" type="button">
                            <i class="fa fa-trash"></i>
                        </button>

                        <button class="btn btn-flex btn-warning btn-sm btn-icon" id="bulkActivateBtn" type="button">
                            <i class="fa-solid fa-circle-check"></i>
                        </button>
                        <button class="btn btn-flex btn-secondary btn-sm btn-icon" id="bulkDeactivateBtn" type="button">
                            <i class="fa fa-ban" aria-hidden="true"></i>
                        </button>
                    @endif
                    
                </div>
            @endcan
        </h2>

        <div class="card-toolbar">
            @can('contacts_create')
                <button class="btn btn-flex btn-primary btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#editModal" data-action="create">
                    <i class="ki-duotone ki-plus fs-3"></i>
                    Add
                </button>
            @endcan

            <a class="btn btn-flex btn-dark btn-sm mr-1" href="{{ route('contacts.index') }}?status={{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'active' : 'inactive' }}">
                {{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'Active' : 'Inactive' }}
            </a>

            <a class="btn btn-flex btn-danger btn-sm mr-1" href="{{ route('contacts.index') }}?status=deleted">
                Deleted
            </a>

            <button class="btn btn-flex btn-dark btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#columnsModal">
                <i class="fa fa-eye"></i>
                Columns
            </button>

            @can('contacts_export')
                <button class="btn btn-flex btn-dark btn-sm mr-1" id="exportBtn" type="button">
                    <i class="fa fa-file"></i>
                    Export
                </button>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <div class="tableDiv" id="tableDiv">
            <table class="table w-100 table-hover table-row-dashed" id="dataTable">
                <thead class="text-start bg-dark text-white fw-bold fs-7 text-uppercase gs-0">
                    <th id="0" class="px-2 rounded-start text-center" style="width:35px;"><input type="checkbox" class="selectAll" id="selectAll"></th>
                    @foreach($columns as $columnName => $columnDetails)
                        <th id="{{ $columnDetails['index'] }}" class="{{ $loop->last ? ' px-2 rounded-end text-end' : '' }}" style="{{ $loop->last ? 'width:90px;' : '' }}">{{ $columnName }}</th>
                    @endforeach
                </thead>
                <thead>
                    <tr id="filterHead">
                        <th id="0"></th>

                        <th id="1" class="{{ $columns['type']['visible'] ? '' : 'd-none' }}">
                            <select name="searchType" id="searchType" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchType input">
                                <option value="" data-name="">All</option>
                                <option value="Tenant" data-name="Tenant">Tenant</option>
                                <option value="Buyer" data-name="Buyer">Buyer</option>
                                <option value="Landlord" data-name="Landlord">Landlord</option>
                                <option value="Seller" data-name="Seller">Seller</option>
                                <option value="Landlord+Seller" data-name="Landlord+Seller">Landlord+Seller</option>
                                <option value="Agent" data-name="Agent">Agent</option>
                                <option value="Portal" data-name="Portal">Portal</option>
                                <option value="Buyer/Tenant" data-name="Buyer/Tenant">Buyer/Tenant</option>
                                <option value="Unrecognized" data-name="Unrecognized">Unrecognized</option>
                                <option value="Other" data-name="Other">Other</option>
                            </select>
                        </th>
                        
                        <th id="2" class="{{ $columns['contact']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchContact input" placeholder="Search by Contact name, email, phone"></th>
                        <th id="3" class="{{ $columns['refno']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchRefno input" placeholder="Search by Ref No" />
                        </th>
                        
                        <th id="4" class="{{ $columns['source']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSource" id="searchSource" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchSource input">
                                <option value="" data-name="">All</option>
                                @if(count($sources))
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}" data-name="{{ $source->name }}">{{ $source->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>
                        <th id="5" class="{{ $columns['sub_source']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSubSource" id="searchSubSource" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchSubSource input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>
                        <th id="6" class="{{ $columns['created_by']['visible'] ? '' : 'd-none' }}">
                            <select name="searchCreatedBy" id="searchCreatedBy" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchCreatedBy input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>
                        <th id="7" class="{{ $columns['updated_by']['visible'] ? '' : 'd-none' }}">
                            <select name="searchUpdatedBy" id="searchUpdatedBy" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchUpdatedBy input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="8" class="{{ $columns['created_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCreateDate input" placeholder="Search by Date input" /></th>
                        <th id="9" class="{{ $columns['last_update']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDate input" placeholder="Search by Date input" /></th>
                        <th id="10"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade modalRight w-90" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-grey">
            <div class="modal-header py-3 bg-white">
                <h5 class="modal-title" id="editModalLabel">Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="{{ route('contacts.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="page-loader flex-column bg-dark bg-opacity-25">
                        <span class="spinner-border text-primary" role="status"></span>
                        <span class="text-gray-800 fs-6 fw-semibold mt-5">Loading...</span>
                    </div>
                    <input type="hidden" id="editId" name="id">

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark d-flex align-items-between">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Basic Details</h3>
                            <span class="badge badge-light">RefNo#: &nbsp <span id="modalRefNo"></span></span>
                        </div>
                        <div class="card-body py-4 px-5">

                            <div class="row mt-4">
                                <div class="col-lg-2">
                                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url({{ asset('assets/media/svg/avatars/blank-dark.svg') }})">
                                        <div class="image-input-wrapper w-150px h-150px userImage" style="background-image: url({{ asset('assets/media/svg/avatars/blank-dark.svg') }})"></div>
                                        <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Change avatar">
                                            <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>

                                            <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                                            <input type="hidden" name="avatar_remove" />
                                        </label>
                                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                            <i class="ki-outline ki-cross fs-3"></i>
                                        </span>
                                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Remove avatar">
                                            <i class="ki-outline ki-cross fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-lg-10">
                                    <div class="row">
                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text p-0 border-0" id="basic-addon1">
                                                    <select name="title" id="title" class="form-control form-control-sm title" style="border-top-right-radius:0px; border-bottom-right-radius:0px;">
                                                        <option value="Mr">Mr</option>
                                                        <option value="Mrs">Mrs</option>
                                                        <option value="Ms">Ms</option>
                                                        <option value="Miss">Miss</option>
                                                        <option value="Mx">Mx</option>
                                                        <option value="Master">Master</option>
                                                        <option value="Sir">Sir</option>
                                                        <option value="Madam">Madam</option>
                                                        <option value="Dr">Dr</option>
                                                        <option value="Prof">Prof</option>
                                                        <option value="Hon">Hon</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </span>
                                                <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Contact Name" required>
                                            </div>

                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm phone" id="phone" name="phone" placeholder="Phone" required>
                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control form-control-sm" id="email" name="email" placeholder="Email">
                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="dob" class="form-label">Date Of Birth</label>
                                            <input type="text" class="form-control form-control-sm singleDate" id="dob" name="dob" placeholder="Date Of Birth">
                                        </div>

                                        <div class="col-lg-3 mb-3">
                                            <label for="country_id" class="form-label">Country</label>
                                            <select name="country_id" id="country_id" class="form-control form-control-sm country selectTwoModal">
                                                <option value="">Select Country</option>
                                                @if(count($countries) > 0)
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-3">
                                            <label for="city_id" class="form-label">City</label>
                                            <select name="city_id" id="city_id" class="form-control form-control-sm city">
                                                <option value="">Select City</option>
                                                
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="address" class="form-label">Address</label>
                                            <input type="text" class="form-control form-control-sm address" id="address" name="address" placeholder="Address">
                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="contact_type" class="form-label">Contact Type</label>
                                            <select name="contact_type" id="contact_type" class="form-control form-control-sm selectTwoModal contact_type">
                                                <option value="">Select Contact Type</option>
                                                <option value="Tenant" data-name="Tenant">Tenant</option>
                                                <option value="Buyer" data-name="Buyer">Buyer</option>
                                                <option value="Landlord" data-name="Landlord">Landlord</option>
                                                <option value="Seller" data-name="Seller">Seller</option>
                                                <option value="Landlord+Seller" data-name="Landlord+Seller">Landlord+Seller</option>
                                                <option value="Agent" data-name="Agent">Agent</option>
                                                <option value="Portal" data-name="Portal">Portal</option>
                                                <option value="Buyer/Tenant" data-name="Buyer/Tenant">Buyer/Tenant</option>
                                                <option value="Unrecognized" data-name="Unrecognized">Unrecognized</option>
                                                <option value="Other" data-name="Other">Other</option>
                                            </select>
                                        </div>

                                    
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body py-4 px-5">
                            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                                <li class="nav-item">
                                    <a class="nav-link text-dark fw-bold active" data-bs-toggle="tab" href="#tabeNotes"> <i class="fa fa-sticky-note"></i> Notes</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-dark fw-bold" data-bs-toggle="tab" href="#tabOtherDetails"> <i class="fa fa-info-circle"></i> Other Details</a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link text-dark fw-bold" data-bs-toggle="tab" href="#tabDocuments"><i class="fa fa-file"></i> Documents</a>
                                </li>
                            </ul>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="tabeNotes" role="tabpanel">
                                    <div class="notesDiv">
                                        <!-- <textarea class="form-control mb-3 note" id="note" name="note" rows="3" data-kt-element="input" placeholder="Type a note"></textarea>
                                        <button type="button" class="btn btn-primary btn-xs noteAddBtn" disabled onclick="addNoteFunction()">Add Note</button> -->

                                        <div class="mt-4">
                                            <ol class="timeline">
                                                <li class="timeline-item">
                                                    <span class="timeline-item-icon | avatar-icon">
                                                        <i class="avatar">
                                                            <img class="img-fluid" src="{{ auth()->user()->profileImage() }}" />
                                                        </i>
                                                    </span>
                                                    <div class="new-comment bg-light-primary p-3 rounded">
                                                        <textarea class="form-control note border-0 shadow-sm" id="note" name="note" rows="3" data-kt-element="input" placeholder="Type a note"></textarea>
                                                        <button type="button" class="btn btn-primary btn-xs noteAddBtn mt-3" disabled onclick="addNoteFunction()">Add Note</button>
                                                    </div>
                                                </li>
                                                <div class="notesTable">
                                                    
                                                </div>
                                                
                                            </ol>
                                        </div>
                                        <!-- <div class="notesTable mt-4">
                                            

                                        </div> -->
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="tabOtherDetails" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="source_id" class="form-label">Source </label>
                                            <select name="source_id" id="source_id" class="form-control form-control-sm selectTwoModal source_id">
                                                
                                                <option value="">Select Source</option>
                                                @if(count($sources))
                                                    @foreach($sources as $source)
                                                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="sub_source_id" class="form-label">Sub Source </label>
                                            <select name="sub_source_id" id="sub_source_id" class="form-control form-control-sm selectTwoModal sub_source_id">
                                                <option value="">All</option>
                                                
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="company" class="form-label">Company </label>
                                            <input type="text" class="form-control form-control-sm" name="company" id="company" placeholder="Company">
                                        </div>

                                        <div class="col-lg-3 mb-3 form-group">
                                            <label for="designation" class="form-label">Designation </label>
                                            <input type="text" class="form-control form-control-sm" name="designation" id="designation" placeholder="designation">
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="tabDocuments" role="tabpanel">
                                    <div>
                                        <div class="bg-light-primary p-4 rounded shadow-sm">
                                            @include('admin.components.dropzone', ['elementId' => 'documentsZone', 'elementClass' => 'documentsZone'])
                                        </div>

                                        <div class="row">
                                            <div class="col-12 documents_edit mt-4">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-clock text-white mr-1"></i> Change Log</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <table class="table">
                                <thead class="text-start bg-primary text-white fw-bold fs-5 text-uppercase gs-0">
                                    <th class="px-2 rounded-start">Notes</th>
                                    <th>By</th>
                                    <th class="px-2 rounded-end">Date</th>
                                </thead>
                                <tbody class="changeLog">
                                
                                </tbody>
                            </table>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white py-2">
                    <button type="submit" class="btn btn-primary btn-sm">Save Contact</button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modalRight" id="columnsModal" tabindex="-1" aria-labelledby="columnsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="columnsModalLabel">Column Visibility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach($columns as $columnName => $visibility)
                        <div class="form-check col-md-4 mb-3">
                            <input type="checkbox" class="form-check-input toggle-column" id="toggle{{ ucfirst($columnName) }}" data-column="{{ $loop->index + 1 }}" {{ $visibility['visible'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="toggle{{ ucfirst($columnName) }}">{{ ucfirst($columnName) }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark btn-sm">Reset</button>
                <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@include('layouts.scripts')

<script>
    // set the dropzone container id
    const id = "#documentsZone";
    const dropzone = document.querySelector(id);

    // set the preview element template
    var previewNode = dropzone.querySelector(".dropzone-item");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);

    var myDropzone = new Dropzone(id, { // Make the whole body a dropzone
        url: "https://starlingproperties.ae", // Set the url for your upload script location
        parallelUploads: 20,
        previewTemplate: previewTemplate,
        maxFilesize: 1, // Max filesize in MB
        autoQueue: false, // Make sure the files aren't queued until manually added
        previewsContainer: id + " .dropzone-items", // Define the container to display the previews
        clickable: id + " .dropzone-select" // Define the element that should be used as click trigger to select files.
    });

    myDropzone.on("addedfile", function (file) {
        // Hookup the start button
        file.previewElement.querySelector(".dropzone-start").onclick = function () { myDropzone.enqueueFile(file); };
        const dropzoneItems = dropzone.querySelectorAll('.dropzone-item');
        dropzoneItems.forEach(dropzoneItem => {
            dropzoneItem.style.display = '';
        });
        // dropzone.querySelector('.dropzone-upload').style.display = "inline-block";
        dropzone.querySelector('.dropzone-remove-all').style.display = "inline-block";

        // Create and append a reference name input for each file
        var referenceInput = document.createElement("input");
        referenceInput.setAttribute("type", "text");
        referenceInput.setAttribute("name", "document_name[]");
        referenceInput.setAttribute("class", "form-control form-control-sm");
        referenceInput.setAttribute("placeholder", "Document Reference Name");
        file.previewElement.querySelector(".dropzone-file").appendChild(referenceInput);
    });
    // Hide the total progress bar when nothing's uploading anymore
    myDropzone.on("complete", function (progress) {
        const progressBars = dropzone.querySelectorAll('.dz-complete');

        setTimeout(function () {
            progressBars.forEach(progressBar => {
                progressBar.querySelector('.progress-bar').style.opacity = "0";
                progressBar.querySelector('.progress').style.opacity = "0";
                progressBar.querySelector('.dropzone-start').style.opacity = "0";
            });
        }, 300);
    });

    // Setup the button for remove all files
    dropzone.querySelector(".dropzone-remove-all").addEventListener('click', function () {
        dropzone.querySelector('.dropzone-upload').style.display = "none";
        dropzone.querySelector('.dropzone-remove-all').style.display = "none";
        myDropzone.removeAllFiles(true);
    });

    // On all files removed
    myDropzone.on("removedfile", function (file) {
        if (myDropzone.files.length < 1) {
            dropzone.querySelector('.dropzone-upload').style.display = "none";
            dropzone.querySelector('.dropzone-remove-all').style.display = "none";
        }
    });
</script>

<script>

    // columns visib start
    function updateColumnVisibility() {
        $('.toggle-column').each(function () {
            var columnName = $(this).data('column');
            var isVisible = $(this).prop('checked');
            dataTable.column(columnName).visible(isVisible);
            $('#filterHead th[id="' + columnName + '"]').toggleClass('d-none', !isVisible);
        });
    }

    // Event listener for changes in column visibility checkboxes
    $('.toggle-column').on('change', function () {
        updateColumnVisibility();
    });

    var urlParams = new URLSearchParams(window.location.search);
    var status = urlParams.get('status');
    var contact_type = urlParams.get('type');
    const refnoParam = urlParams.get('refno');

    initializeDateRange('singleDate', null, 'single');

    initializeDateRange('searchDate', '{{ $firstDate }}');
    initializeDateRange('searchCreateDate', '{{ $firstDate }}');
    var columnVisibility = {!! json_encode($columns) !!};


    var url = '{{ route('contacts.getContacts') }}';

    
    // Check if contact_type is available and append it to the URL
    if (contact_type !== null) {
        url += (url.includes('?') ? '&' : '?') + 'type=' + contact_type;
    }

    // Check if status is available and not null, then append it to the URL
    if (status !== null) {
        url += (url.includes('?') ? '&' : '?') + 'status=' + status;
    }

    //columns visib end

    var dataTable = new DataTable('#dataTable', {
        select: {
            style: 'multi',
            selector: 'td:first-child input[type="checkbox"]',
            info: false,
            search: false,
        },
        responsive: true,
        serverSide: true,
        processing: true,
        paging: true,
        order: [[2, 'asc'], [3, 'asc']],
        ajax: {
            // url: '{{ route('contacts.getContacts') }}' + (status ? '?status=' + status : ''),
            url: url,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function (d) {
                d.startDate = $('.searchDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endDate = $('.searchDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.startCreatedDate = $('.searchCreateDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endCreatedDate = $('.searchCreateDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.contact_name = $('.searchContact').val();
                d.contact_type = $('.searchType').val();
                d.refno = $('.searchRefno').val();
                d.whatsapp = $('.searchWhatsapp').val();
                d.source_name = $('.searchSource :selected').text() == 'All' ? '' : $('.searchSource :selected').text();
                d.sub_source_name = $('.searchSubSource :selected').text() == 'All' ? '' : $('.searchSubSource :selected').text();
                d.created_by_name = $('.searchCreatedBy :selected').text() == 'All' ? '' : $('.searchCreatedBy :selected').text();
                d.updated_by_name = $('.searchUpdatedBy :selected').text() == 'All' ? '' : $('.searchUpdatedBy :selected').text();
            },
            //dataSrc: 'contacts',
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    // Checkbox column
                    return '<div class="text-center"><input type="checkbox" class="item-checkbox" value="' + row.id + '"></div>';
                },
                orderable: false,
                searchable: false,
            },
            {
                data: 'contact_type',
                visible: columnVisibility['type']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Customize the content as needed
                    return '<div class="d-flex align-items-center">' +
                        '<div class="symbol symbol-50px symbol-md-70px"><img src="' + row.profile_image_url + '" alt="' + row.name + '" class="img-fluid rounded"> </div>' +
                        '<div class="ms-3">' +
                        '<div class="fw-bold">' + row.name + '</div>' +
                        '<div> <a class="link" href="mailto:'+ row.email +'">' + row.email + '</a></div>' +
                        '<div> <a class="link" href="tel:'+ row.phone +'">' + row.phone + '</a></div>' +
                        '</div>' +
                        '</div>';
                },
                visible: columnVisibility['contact']['visible'],
            },
            {
                data: 'refno',
                visible: columnVisibility['refno']['visible'],
            },
            // {
            //     data: 'whatsapp',
            //     render: function (data, type, row) {
            //         // Customize the content as needed
            //         return '<a class="link" target="_blank" href="https://wa.me/'+ data +'">' + data + '</a>';
            //     },
            //     visible: columnVisibility['whatsapp']['visible'],
            // },
            {
                data: 'source.name',
                visible: columnVisibility['source']['visible'],
            },
            {
                data: 'sub_source.name',
                visible: columnVisibility['sub_source']['visible'],
            },
            {
                data: 'created_by_user.name',
                visible: columnVisibility['created_by']['visible'],
            },
            {
                data: 'updated_by_user.name',
                visible: columnVisibility['updated_by']['visible'],
            },
            {
                data: 'created_at',
                render: function(data, type, row) {
                    return moment.utc(data).format('MMMM D, YYYY') + ' (' + moment.utc(data).fromNow() + ')';
                },
                visible: columnVisibility['created_date']['visible'],
            },
            {
                data: 'updated_at',
                render: function(data, type, row) {
                    return moment.utc(data).fromNow();
                },
                visible: columnVisibility['last_update']['visible'],
            },
            {
                data: 'id',
                render: function (data) {
                    return '<div class="text-end">' +
                        '<button class="btn btn-sm btn-icon mr-1 btn-primary btn-active-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-action="edit" data-id="' + data + '"><i class="fa fa-pencil"></i></button>' +
                    '</div>';
                },
                visible: columnVisibility['actions']['visible'],
            },
        ],
        initComplete: function () {
            
            var api = this.api();

            // if (refnoParam) {
            //     // Search for the 'refno' in the DataTable and select the matching row
            //     //api.columns(1).search(refnoParam).draw();
            //     var rowIndex = api.column(2).data().indexOf(refnoParam);


            //     // Get the data of the matching row
            //     //var matchingRowData = api.row(':eq(0)', { search: 'applied' }).data();

            //     // Check if a matching row is found
            //     if (rowIndex !== -1) {
            //         // Calculate the page number based on the index and page length
            //         var page = Math.floor(rowIndex / api.page.len());

            //         // Go to the calculated page
            //         api.page(page).draw(false);

            //         // Get the data of the matching row
            //         var matchingRowData = api.row(rowIndex).data();

            //         // Trigger the modal to open for the matching record
            //         $('#editModal').modal('show', {
            //             backdrop: 'static',
            //             keyboard: false
            //         });

            //         // Additional logic when the modal is shown
            //         handleModalShown(matchingRowData.id, 'edit');
            //     }
            //     else{
            //         Swal.fire({
            //             text: "No record found against your query.",
            //             icon: "error",
            //             showCancelButton: false,
            //             confirmButtonText: "Ok",
            //             //cancelButtonText: "Cancel",
            //             confirmButtonColor: "#DF405C",
            //             //cancelButtonColor: "#6c757d"
            //         });
            //     }
            // }

            if (refnoParam) {
                $.ajax({
                    url: '{{ route('contacts.searchRefno') }}',
                    method: 'POST',
                    data: {
                        refno: refnoParam,
                        length: api.page.len(),
                    },
                    success: function (response) {
                        if (response && response.record) {

                            var update_perm = $('#update_perm').val();
                            if(update_perm == 'false'){
                                Swal.fire({
                                    title: "Permission Denied",
                                    text: "You don't have the permission to update any owner.",
                                    icon: "error",
                                    showCancelButton: false,
                                    confirmButtonText: "Ok",
                                    confirmButtonColor: "#DF405C",
                                });
                                return false;
                            }
                            
                            // Record found, show the modal
                            $('#editModal').modal('show', {
                                backdrop: 'static',
                                keyboard: false
                            });
                            handleModalShown(response.record.id, 'edit');

                            console.log('page number: ' + response.pageNumber);
                            // Change DataTables page to the calculated page number
                            api.page(response.pageNumber).draw(false);
                        } else {
                            // No record found
                            Swal.fire({
                                text: "No record found against your query.",
                                icon: "error",
                                showCancelButton: false,
                                confirmButtonText: "Ok",
                                confirmButtonColor: "#DF405C",
                            });
                        }
                    },
                    error: function (error) {
                        Swal.fire({
                            text: "Error searching for record",
                            icon: "error",
                            showCancelButton: false,
                            confirmButtonText: "Ok",
                            confirmButtonColor: "#DF405C",
                        });
                        console.error('Error searching for record:', error);
                    }
                });
            }
            
            // Use the existing search elements for search
            $('.searchContact').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchType').on('change', function () {
                api.clear().draw();
            });

            $('.searchRefno').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchWhatsapp').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchSource').on('change', function () {
                api.clear().draw();
            });

            $('.searchSubSource').on('change', function () {
                api.clear().draw();
            });

            $('.searchCreatedBy').on('change', function () {
                api.clear().draw();
            });

            $('.searchUpdatedBy').on('change', function () {
                api.clear().draw();
            });

            $('.searchDate').on('apply.daterangepicker', function (ev, picker) {
                api.clear().draw();
            });

            $('.searchCreateDate').on('apply.daterangepicker', function (ev, picker) {
                api.clear().draw();
            });

        }, 
    });

    function handleModalShown(itemId, action) {
        KTApp.showPageLoading();
        
        $('#editForm')[0].reset();
        $('#city_id').val(null).trigger('change');
        $('#country_id').val(null).trigger('change');
        $('#source_id').val(null).trigger('change');
        $('#sub_source_id').val(null).trigger('change');
        $('#contact_type').val(null).trigger('change');
        var notesDiv = $('.notesTable');
        notesDiv.html('');
        $('#modalRefNo').text('');

        if (myDropzone) {
            myDropzone.removeAllFiles();
        }
        
        var changeLogDiv = $('.changeLog');
        $('.documents_edit').html('');
        changeLogDiv.html('');

        var form = $('#editForm');
        var modalTitle = form.find('.modal-title');
        var modalAction = form.attr('action');
        
        if (action === 'create') {
            modalTitle.text('Create Contact');
            modalAction = '{{ route('contacts.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Contact');
            modalAction = '{{ route('contacts.update', ':itemId') }}'.replace(':itemId', itemId);
        }
        
        // Set ID in the hidden input
        $('#editId').val(itemId);

        form.attr('action', modalAction);
        
        var imageDiv = $('.userImage');
        imageDiv.css('background-image', 'url({{ asset("assets/media/svg/avatars/blank-dark.svg") }})');

        //getLocations();

        // Fetch data via AJAX
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('contacts.edit', ['contact' => ':itemId']) }}'.replace(':itemId', itemId),
                type: 'GET',
                success: function(data) {
                    var contact = data.contact;
                    imageDiv.css('background-image', 'url(' + contact.profile_image + ')');

                    // Basic Details
                    $('#modalRefNo').text(contact.refno);
                    $('#name').val(contact.name);
                    $('#title').val(contact.title).trigger('change');
                    $('#phone').val(contact.phone);
                    $('#email').val(contact.email);
                    $('#dob').val(contact.dob);
                    $('#country_id').val(contact.country_id).trigger('change');
                    $('#address').val(contact.address);
                    $('#source_id').val(contact.source_id).trigger('change');
                    $('#contact_type').val(contact.contact_type).trigger('change');
                    $('#company').val(contact.company);
                    $('#designation').val(contact.designation);
                    if(contact.city_id != null){
                        populateCities(contact.country_id, contact.city_id);
                    }
                    if(contact.sub_source_id != null){
                        populateSubSources('sub_source_id', 'modal', contact.source_id, contact.sub_source_id);
                    }

                    // Display Documents
                    displayDocuments(contact.documents);

                    displayNotes(contact.notes);
                    
                    // Populate change log
                    
                    var activities = contact.activities;
                    var tableRow = null;

                    activities.forEach(function (activity) {
                        var properties = activity.properties;

                        // Convert created_at to diffForHumans
                        var createdAt = moment(activity.created_at).fromNow();
                        var activityStatus = activity.description;

                        // Display the causer's name
                        var causerName = activity.causer ? activity.causer.name : 'Unknown User';

                        tableRow += '<tr>';
                        tableRow += '<td class="scrollable-cell"><pre>'+activityStatus+': ' + JSON.stringify(properties, null, 2) + '</pre></td>';
                        tableRow += '<td>' + causerName + '</td>';
                        tableRow += '<td>' + createdAt + '</td>';
                        tableRow += '</tr>';
                    });
                    changeLogDiv.html(tableRow);
                }
            });
        }

        // Hide loader after
        KTApp.hidePageLoading();
        //loadingEl.remove();

        function stringifyChanges(changes) {
            return Object.keys(changes).map(function (key) {
                var value = changes[key];
                var displayKey = formatKey(key);

                // Check if the value is an object and stringify it recursively
                if (value && typeof value === 'object') {
                    return displayKey + ': {' + stringifyChanges(value) + '}';
                }

                return displayKey + ': "' + formatValue(value) + '"';
            }).join(', ');
        }

        function formatKey(key) {
            // Convert "sales_percent" to "Sales Percent"
            return key.replace(/_/g, ' ').replace(/\b\w/g, function (match) {
                return match.toUpperCase();
            });
        }

        function formatValue(value) {
            // Example: Convert "ok@gmail.com" to "ok@gmail.com" (no change) or "0" to "0" (no change)
            if (value !== undefined && value !== null) {
                return value.toString();
            }
            return 'undefined';
        }


        // Display documents below the drop zone
        function displayDocuments(documents) {
            var documentsDiv = $('.documents_edit');
            documentsDiv.html('');

            documents.forEach(function (document, index) {
                var documentRow = '<div class="mb-2 bg-light-primary p-2 mb-3 rounded shadow-sm" id="document_file'+document.id+'">';
                documentRow += '<div class="d-flex mb-2">';
                documentRow += '<a href="' + document.file_url + '" target="_blank">' + document.file_name + '</a>';
                documentRow += '<button type="button" class="btn btn-danger btn-xs ms-auto" onclick="confirmDocumentRemoval(' + document.id + ')">Remove</button>';
                documentRow += '</div>';
                documentRow += '<input type="hidden" name="document_id[]" value="'+document.id+'"><input type="text" class="form-control form-control-sm" name="document_names[]" value="' + document.alt + '" placeholder="Document Reference Name" required>';
                documentRow += '</div>';
                documentsDiv.append(documentRow);
            });
        }

        // Confirm document removal with SweetAlert
        window.confirmDocumentRemoval = function(index) {
            Swal.fire({
                text: "Are you sure you want to remove this document?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, remove it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove document from display
                    //$('#document_file'+index+'').remove();
                    $('#document_file' + index).fadeOut(500, function () {
                        $(this).remove();
                    });
                }
            });
        }

        // Function to display notes
        function displayNotes(notes) {
            var notesDiv = $('.notesTable');
            notesDiv.html('');

            notes.forEach(function (note) {

                var user_photo;

                if (note.created_by_user.photo !== null) {
                    user_photo = '<?= asset('public/storage') ?>/' + note.created_by_user.photo;
                } else {
                    user_photo = '<?= asset('assets/media/svg/avatars/blank-dark.svg') ?>';
                }

                var createdAtDate = new Date(note.created_at);

                // Format date as "12 July 2022"
                var formattedDate = createdAtDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                // Format time as "4:34 PM"
                var formattedTime = createdAtDate.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true
                });

                // Combine formatted date and time
                var formattedDateTime = formattedDate + ' ' + formattedTime;
                var timeAgo = moment(createdAtDate).fromNow();

                var noteRow = $(
                    '<li class="timeline-item | extra-space noteRow" id="note_' + note.id + '">' +
                        '<span class="timeline-item-icon | filled-icon">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">' +
                                '<path fill="none" d="M0 0h24v24H0z" />' +
                                '<path fill="currentColor" d="M6.455 19L2 22.5V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6.455zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z" />' +
                            '</svg>' +
                        '</span>' +
                        '<div class="timeline-item-wrapper">' +
                            '<div class="timeline-item-description">' +
                                '<i class="avatar | small">' +
                                    '<img class="img-fluid" src="'+user_photo+'" />' +
                                '</i>' +
                                '<span><span class="fw-bold text-dark">' + note.created_by_user.name + '</span> <span style="font-size:13px;">commented on <time datetime="' + note.created_at + '">' + formattedDateTime + ' ('+ timeAgo +')</time></span></span>' +
                            '</div>' +
                            '<div class="comment">' +
                                '<textarea class="form-control noteText bg-light-primary border-0" name="note_values[]" rows="' + note.note.split('\n').length + '" readonly>' + note.note + '</textarea>' +
                                '<button class="btn btn-xs btn-light-danger removeButton" type="button" onclick="removeNote(' + note.id + ')"><i class="fa fa-trash"></i></button>' +
                            '</div>' +
                        '</div>' +
                    '</li>'
                );

                notesDiv.append(noteRow);

                // Add double-click event to remove readonly
                noteRow.find('.noteText').dblclick(function () {
                    $(this).prop('readonly', false);
                    $(this).removeClass('border-0');
                    $(this).removeClass('bg-light-primary');
                });

                // Add blur event to add readonly
                noteRow.find('.noteText').blur(function () {
                    $(this).addClass('border-0');
                    $(this).addClass('bg-light-primary');
                    $(this).prop('readonly', true);
                });
            });
        }

        // Function to remove a note
        window.removeNote = function(noteId) {
            Swal.fire({
                text: "Are you sure you want to remove this note?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, remove it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove note from display
                    $('#note_' + noteId).fadeOut(500, function () {
                        $(this).remove();
                    });
                }
            });
        }
    }

    $('.selectAll').on('change', function () {
        var checkboxes;
        checkboxes = dataTable.rows().nodes().to$().find('.item-checkbox');

        checkboxes.prop('checked', this.checked);
        dataTable.rows().select(this.checked);

        // Trigger a change event on the checkboxes to ensure proper handling of bulk actions
        updateBulkActionButtons();
    });

    dataTable.table().on('change', '.item-checkbox', function () {
        var allChecked = $('.item-checkbox:checked').length === $('.item-checkbox').length;
        $('.selectAll').prop('checked', allChecked);
        updateBulkActionButtons();
    });

    $('.item-checkbox').on('change', function () {
        alert('done');
        var checkbox = $(this);
        var isChecked = checkbox.is(':checked');

        if (isChecked) {
            updateBulkActionButtons();
        } else {
            // Check if all other checkboxes are unchecked
            var allUnchecked = $('.item-checkbox:checked').length === 0;

            if (allUnchecked) {
                //$('.bulkActions').addClass('d-none');
            } else {
                updateBulkActionButtons();
            }
        }
    });
        
        // end for tables

    function reloadDataTable() {
        dataTable.ajax.reload();
    }

    // Add the CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function populateSubSources(idName, type, source_id, selectedId = null) {
        var selectElement = $('#'+idName);
        if(type == 'modal'){
            selectElement.select2({
                dropdownParent: $("#editModal")
            });
        }
        else{
            selectElement.select2({});
        }
        selectElement.empty();
        
        if(type == 'search'){
            selectElement.html('<option value="" data-name="">All</option>');
        }
        else{
            selectElement.html('<option value="" data-name="">Select Sub Source</option>');
        }

        // Fetch communities via AJAX
        $.ajax({
            url: '{{ route('subSources.getList') }}',
            type: 'GET',
            data: { source_id: source_id},
            success: function(data) {
                console.log(type == 'search' ? 'id' : 'id');
                var sub_sources = data.sub_sources;

                if (!sub_sources || sub_sources.length === 0) {
                    console.error('No Sub Source found for the selected city.');
                    return;
                }

                // Populate communities select element
                if(selectedId == null){
                    $.each(sub_sources, function(index, sub_source) {
                        // Only append options on condition (removed)
                        var option = new Option(sub_source.name, sub_source.id, false, false);
                        $(option).attr('data-name', sub_source.name);
                        selectElement.append(option);
                    });
                }

                // Select the item if item id exists
                if (selectedId) {
                    selectElement.val(selectedId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sub sources:', error);
            }
        });
    }

    $(document).on('change', '#searchSource', function() {
        var selectedId = $(this).val();
        populateSubSources('searchSubSource', 'search', selectedId);
    });

    $(document).on('change', '#source_id', function() {
        var selectedId = $(this).val();
        populateSubSources('sub_source_id', 'modal', selectedId);
    });

    function populateCities(country_id, selectItem = null){
        var selectElement = $('#city_id').select2({
            dropdownParent: $("#editModal")
        });
        selectElement.empty();

        selectElement.html('<option value="" data-name="">Select City</option>');

        // Fetch communities via AJAX
        $.ajax({
            url: '{{ route('cities.getCities') }}',
            type: 'GET',
            data: { country_id: country_id },
            success: function(data) {
                var cities = data.cities;

                if (!cities || cities.length === 0) {
                    console.error('No Cities found for the selected city.');
                    return;
                }

                if(selectItem == null){
                    $.each(cities, function(index, city) {
                        // Only append options on condition (removed)
                        var option = new Option(city.name, city.id, false, false);
                        selectElement.append(option);
                    });
                }

                if (selectItem) {
                    selectElement.val(selectItem).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching cities:', error);
            }
        });
    }

    $(document).on('change', '#country_id', function() {
        var country_id = $(this).val();
        populateCities(country_id);
    });

    // Fetch data via AJAX and populate the modal
    $('#editModal').on('show.bs.modal', function (event) {

        var update_perm = $('#update_perm').val();
        if(update_perm == 'false'){
            Swal.fire({
                title: "Permission Denied",
                text: "You don't have the permission to update any contact.",
                icon: "error",
                showCancelButton: false,
                confirmButtonText: "Ok",
                confirmButtonColor: "#DF405C",
            });
            return false;
        }

        var button = $(event.relatedTarget);
        itemId = button.data('id');
        action = button.data('action');
        handleModalShown(itemId, action);

    });

    $('#editForm').submit(function(e) {
        e.preventDefault();
        var submitButton = $(this).find('button[type=submit]');
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

        var formData = new FormData($(this)[0]);

        // Append Dropzone files to formData
        myDropzone.files.forEach(function (file, index) {
            formData.append('file[' + index + ']', file);
        });

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                reloadDataTable();
                
                if (myDropzone) {
                    myDropzone.removeAllFiles();
                }

                $('#editModal').modal('hide');
                submitButton.prop('disabled', false).html('Save Contact');

                if (data.message) {
                    toastr.success(data.message);
                }
            },
            error: function(xhr, status, error) {
                submitButton.prop('disabled', false).html('Save Contact');
                // Clear previous validation errors
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                var response = xhr.responseJSON;

                if (response.errors) {
                    // Display validation errors using Toastr
                    toastr.error('Please fix the following errors:', 'Validation Error');

                    // Add Laravel validation error classes to relevant inputs
                    $.each(response.errors, function(key, value) {
                        var input = $('[name="' + key + '"]');
                        input.addClass('is-invalid');

                        // Assuming you have a parent div with the class 'form-group'
                        var parentDiv = input.closest('.form-group');

                        // Append the validation error message
                        parentDiv.append('<div class="invalid-feedback">' + value[0] + '</div>');
                    });
                } else {
                    console.error(xhr.responseText);
                }
            }
        });
    });


    function confirmDelete(deleteUrl) {
        Swal.fire({
            text: "Are you sure you want to delete this contact?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (data) {
                        toastr.success(data.message);
                        reloadDataTable();
                    },
                    error: function (xhr, status, error) {
                        // Handle errors if needed
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    }

    function updateBulkActionButtons() {
        var selectedItems = dataTable.rows({ selected: true }).data().toArray();
        var selectedItemsCount = selectedItems.length;

        // Toggle the class based on the number of selected items
        if (selectedItemsCount > 0) {
            $('.bulkActions').removeClass('d-none');
        } else {
            $('.bulkActions').addClass('d-none');
        }
    }

    $('#bulkDeleteBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('contacts.bulkDelete') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkRestoreBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('contacts.bulkRestore') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkActivateBtn').on('click', function () {
        var selectedItems;
        // Check if in list view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('contacts.bulkActivate') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkDeactivateBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('contacts.bulkDeactivate') }}', { item_ids: selectedItems });
        }
    });

    // Initial update on page load
    updateBulkActionButtons();

    // Event handler for checkbox change
    $('body').on('change', 'input[name="item_ids[]"]', function () {
        updateBulkActionButtons();
    });

    function performBulkAction(url, data) {
        console.log(url);
        console.log(data);
        //console.log(data);
        Swal.fire({
            text: "Are you sure you want to perform this action?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, do it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform AJAX request for bulk action
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (data) {
                        if(data.message){
                            toastr.success(data.message);
                            reloadDataTable();
                        }
                        else{
                            toastr.error(data.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            toastr.error(xhr.responseJSON.error, 'Error');
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            toastr.error(xhr.responseJSON.errors.join('<br>'), 'Validation Error');
                        } else {
                            toastr.error(xhr.responseText, 'Error');
                        }
                    }
                });
            }
        });
    }

    $('#exportBtn').on('click', function () {
        // Disable export button and show processing message
        $(this).prop('disabled', true);
        Swal.fire({
            title: 'Exporting...',
            html: 'Export processing. Please wait...',
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Get selected item IDs
        var selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        // Get filter values
        var filterValues = {};
        $('#filterHead .input').each(function() {
            var id = $(this).closest('th').attr('id');
            var value = $(this).val();

            // Handle date range inputs
            if (id === '9') {
                var startDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                var endDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                
                filterValues['startDate'] = startDate;
                filterValues['endDate'] = endDate;
            }
            else if (id === '8') {
                var startCreatedDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                var endCreatedDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                
                filterValues['startCreatedDate'] = startCreatedDate;
                filterValues['endCreatedDate'] = endCreatedDate;
            } else {
                switch(id) {
                    case '1':
                        //filterValues['source_name'] = value;
                        filterValues['contact_type'] = value;
                        break;
                    case '2':
                        //filterValues['source_name'] = value;
                        filterValues['contact_name'] = value;
                        break;
                    case '3':
                        //filterValues['source_name'] = value;
                        filterValues['refno'] = value;
                        break;

                    case '4':
                        //filterValues['source_name'] = value;
                        filterValues['source_name'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '5':
                        //filterValues['sub_source_name'] = value;
                        filterValues['sub_source_name'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '6':
                        //filterValues['created_by'] = value;
                        filterValues['created_by_name'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '7':
                        //filterValues['upadted_by'] = value;
                        filterValues['updated_by_name'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    default:
                        filterValues['search' + id] = value;
                        break;
                }
            }
        });

        //console.log(filterValues);
        //AJAX request to the controller
        $.ajax({
            url: '{{ route('contacts.export') }}',
            type: 'POST',
            data: {
                item_ids: selectedItems,
                filters: filterValues,
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (data) {
                //console.log(data);
                // Enable export button and close the processing message
                $('#exportBtn').prop('disabled', false);
                Swal.close();

                // Convert base64 to Blob
                var binaryData = atob(data.file);
                var arrayBuffer = new ArrayBuffer(binaryData.length);
                var byteArray = new Uint8Array(arrayBuffer);
                for (var i = 0; i < binaryData.length; i++) {
                    byteArray[i] = binaryData.charCodeAt(i);
                }
                var blob = new Blob([arrayBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });

                // Create a temporary link element and trigger the download
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = data.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function (xhr, status, error) {
                // Enable export button and close the processing message
                $('#exportBtn').prop('disabled', false);
                Swal.close();

                // Handle errors if needed
                toastr.error(xhr.responseText);
            }
        });
    });

 
    //});

    
    
</script>



<script>
    // Function to toggle disabled attribute on Save Note button
    function toggleSaveButton() {
        var noteText = $('#note').val();
        var saveButton = $('.noteAddBtn');

        if (noteText.trim() !== '') {
            saveButton.prop('disabled', false);
        } else {
            saveButton.prop('disabled', true);
        }
    }

    // Function to save a note
    function addNoteFunction() {
        var noteText = $('#note').val();
        var notesTable = $('.notesTable');

        if (noteText.trim() !== '') {
            // Create a unique ID for the note
            var noteId = 'note_' + Date.now();

            var userName = "{{ auth()->user()->name }}";
            var userPhoto = "{{ auth()->user()->profileImage() }}";

            // Get the current date and time
            var currentDate = new Date();

            // Format date as "12 July 2022"
            var formattedDate = currentDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Format time as "4:34 PM"
            var formattedTime = currentDate.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            });

            // Combine formatted date and time
            var formattedDateTime = formattedDate + ' ' + formattedTime;
            var timeAgo = moment(currentDate).fromNow();

            var newRow = $(
                '<li class="timeline-item | extra-space noteRow" id="note_' + noteId + '">' +
                    '<span class="timeline-item-icon | filled-icon">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">' +
                        '<path fill="none" d="M0 0h24v24H0z" />' +
                        '<path fill="currentColor" d="M6.455 19L2 22.5V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6.455zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z" />' +
                        '</svg>' +
                    '</span>' +
                    '<div class="timeline-item-wrapper">' +
                        '<div class="timeline-item-description">' +
                            '<i class="avatar | small">' +
                                '<img class="img-fluid" src="' + userPhoto + '" />' +
                            '</i>' +
                            '<span><span class="fw-bold text-dark">' + userName + '</span> <span style="font-size:13px;"> commented on <time datetime="formattedDate">'+formattedDateTime+' ('+ timeAgo +')</time></span></span>' +
                        '</div>' +
                        '<div class="comment">' +
                            '<textarea class="form-control noteText bg-light-primary border-0" name="note_values[]" rows="' + noteText.split('\n').length + '" readonly>' + noteText + '</textarea>' +
                            '<button class="btn btn-xs btn-light-danger removeButton" type="button" onclick="removeNote(\'' + noteId + '\')"><i class="fa fa-trash"></i></button>' +
                        '</div>' +
                    '</div>' +
                '</li>'
            );

            notesTable.prepend(newRow);

            // Clear the textarea and disable the button
            $('#note').val('');
            $('.noteAddBtn').prop('disabled', true);

            // Apply animations
            newRow.hide().fadeIn(500);
            attachNoteEvents(newRow);
        }
    }

    function attachNoteEvents(noteRow) {
        var noteText = noteRow.find('.noteText');

        // Double-click event to remove readonly
        noteText.on('dblclick', function () {
            $(this).prop('readonly', false);
            $(this).removeClass('border-0');
            $(this).removeClass('bg-light-primary');
        });

        // Blur event to add readonly and focus on interaction
        noteText.on('blur', function () {
            $(this).addClass('border-0');
            $(this).addClass('bg-light-primary');
            $(this).prop('readonly', true);
        });
    }

    // Function to remove a note
    function removeNote(noteId) {
        // Ask for confirmation using SweetAlert
        Swal.fire({
            text: "Are you sure you want to remove this note?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, remove it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove the note row with animation
                $('#' + noteId).fadeOut(500, function () {
                    $(this).remove();
                });
            }
        });
    }

    // Attach event listeners
    $(document).ready(function () {
        $('#note').on('input', toggleSaveButton);
    });
</script>
@endsection
