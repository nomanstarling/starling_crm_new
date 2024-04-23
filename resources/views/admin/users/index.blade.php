@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            {{ isset($_GET['status']) ? ucfirst($_GET['status']) : 'Active' }} Users
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
        </h2>

        <div class="card-toolbar">
            <button class="btn btn-flex btn-primary btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#editModal" data-action="create">
                <i class="ki-duotone ki-plus fs-2"></i>
                Add User
            </button>

            <a class="btn btn-flex btn-dark btn-sm mr-1" href="{{ route('users.index') }}?status={{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'active' : 'inactive' }}">
                {{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'Active' : 'Inactive' }} Users
            </a>

            <a class="btn btn-flex btn-danger btn-sm mr-1" href="{{ route('users.index') }}?status=deleted">
                Deleted Users
            </a>
            <button id="toggleViewBtn" class="btn btn-primary btn-icon btn-sm"><i class="fa fa-th-large" id="viewIcon"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="usersTableDiv" id="usersTableDiv">
            <table class="table w-100 table-hover table-row-dashed" id="usersTable">
                <thead class="text-start bg-dark text-white fw-bold fs-7 text-uppercase gs-0">
                    <th class="px-2 rounded-start text-center" style="width:35px;">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input type="checkbox" class="selectAllUsers form-check-input" id="selectAllUsers">
                        </div>
                    </th>
                    <th class="">User</th>
                    <th>Role</th>
                    <th>Gender</th>
                    <th>BRN</th>
                    <th>RERA No</th>
                    <th>Extention</th>
                    <th>Listings</th>
                    <th class="px-2 rounded-end text-end">Actions</th>
                </thead>
                <thead>
                    <tr>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm searchUser" placeholder="Type name, email, phone to search"></th>
                        <th>
                            <select name="searchRole" class="searchRole form-control form-control-sm" id="">
                                <option value="">All</option>
                                @if(count($roles) > 0)
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>
                        <th>
                            <select name="searchGender" class="searchGender form-control form-control-sm" id="">
                                <option value="">All</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </th>
                        <th><input type="text" class="form-control form-control-sm searchBRN" placeholder="Search BRN" /></th>
                        <th><input type="text" class="form-control form-control-sm searchRera" placeholder="Search RERA No" /></th>
                        <th><input type="text" class="form-control form-control-sm searchExtention" placeholder="Search Extention" /></th>
                        <th><input type="text" class="form-control form-control-sm searchListings" placeholder="Search Listings" /></th>
                        <th></th> <!-- Empty for Actions -->
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div id="usersGrid" class="d-none">
            <div class="row mb-3">
                <div class="col-md-12">
                    
                    <div class="form-check form-check-sm form-check-custom">
                        <label for="selectAllUsers">Select All Users</label>
                        <input type="checkbox" class="selectAllUsers form-check-input mx-2" name="selectAllUsers">
                    </div>
                </div>
            </div>
            <div class="row" id="usersGridContent">
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade modalRight" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-grey">
            <div class="modal-header py-3 bg-white">
                <h5 class="modal-title" id="editModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" action="{{ route('users.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="editUserId" name="user_id">
                    <div class="card">
                        <div class="card-header p-4 bg-dark d-flex justify-content-between align-items-center">
                            <h3 class="text-white mb-0"> <i class="fas fa-edit text-white mr-1"></i> Basic Details</h3>
                            <div>
                                <span class="badge badge-secondary mx-1">Created Date: <span class="created_date"></span></span>
                                <span class="badge badge-secondary mx-1">Last Update: <span class="updated_date"></span></span>
                            </div>
                        </div>

                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url({{ asset('assets/media/svg/avatars/blank-dark.svg') }})">
                                        <!--begin::Image preview wrapper-->
                                        <div class="image-input-wrapper w-125px h-125px userImage" style="background-image: url({{ asset('assets/media/svg/avatars/blank-dark.svg') }})"></div>
                                        <!--end::Image preview wrapper-->

                                        <!--begin::Edit button-->
                                        <label class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Change avatar">
                                            <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>

                                            <!--begin::Inputs-->
                                            <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                                            <input type="hidden" name="avatar_remove" />
                                            <!--end::Inputs-->
                                        </label>
                                        <!--end::Edit button-->

                                        <!--begin::Cancel button-->
                                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Cancel avatar">
                                            <i class="ki-outline ki-cross fs-3"></i>
                                        </span>
                                        <!--end::Cancel button-->

                                        <!--begin::Remove button-->
                                        <span class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" data-bs-dismiss="click" title="Remove avatar">
                                            <i class="ki-outline ki-cross fs-3"></i>
                                        </span>
                                        <!--end::Remove button-->
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6 mb-3 form-group">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Full Name" required>
                                        </div>

                                        <div class="col-md-6 mb-3 form-group">
                                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label><br>
                                            <input type="tel" class="form-control form-control-sm phone" id="phone" name="phone" placeholder="Phone" required>
                                        </div>

                                        <div class="col-md-6 mb-3 form-group">
                                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control form-control-sm" id="email" name="email" placeholder="Email" required>
                                        </div>

                                        <div class="col-md-6 mb-3 form-group">
                                            <label for="designation" class="form-label">Designation</label>
                                            <input type="text" class="form-control form-control-sm" id="designation" name="designation" placeholder="Designation">
                                        </div>
                                    </div>
                                </div>

                                <div class="separator my-5"></div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" id="gender" class="form-control form-control-sm" placeholder="Select Gender" required>
                                        <option value="">Select gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control form-control-sm" placeholder="Select status" required>
                                        <option value="">Select status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3 form-group">
                                    <label for="rera_no" class="form-label">Rera No#</label>
                                    <input type="text" class="form-control form-control-sm" id="rera_no" name="rera_no" placeholder="Rera No#">
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="brn" class="form-label">BRN <span class="text-muted fs-9">(Used for Portals)</span></label>
                                    <input type="text" class="form-control form-control-sm" id="brn" name="brn" placeholder="BRN">
                                </div>

                                <div class="col-md-12 mb-3 form-group">
                                    <label for="extention" class="form-label">Phone Extension</label>
                                    <input type="text" class="form-control form-control-sm" id="extention" name="extention" placeholder="Phone Extention">
                                </div>

                                <div class="col-md-12 mb-3 form-group mt-4">
                                    <div class="form-check form-check-solid form-switch form-check-custom fv-row">
                                        <label for="is_teamleader" class="form-label my-0 fw-bold">Team Leader? </label>
                                        <input class="form-check-input w-65px h-30px mx-3" type="checkbox" id="is_teamleader" name="is_teamleader" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Login Details</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="user_name" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="user_name" name="user_name" placeholder="Username" required>
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="editUserEmail" class="form-label">User Role</label>
                                    <select name="role" id="role" class="form-control form-control-sm selectTwoModal" placeholder="Select Role">
                                        <option value="">Select Role</option>
                                        @if(count($roles) > 0)
                                            @foreach($roles as $key => $role)
                                                <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->permissions_count }} Permissions)</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-12 mb-3 form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control form-control-sm" id="password" name="password" placeholder="Password">
                                    <p class="text-muted mt-3">Leave password field blank if don't want to change the password</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Commission Details</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">

                                <div class="col-md-4 mb-3 form-group">
                                    <label for="rental_percent" class="form-label">Rental Percentage</label>
                                    <input type="number" class="form-control form-control-sm" id="rental_percent" name="rental_percent" placeholder="Rental Percentage">
                                </div>

                                <div class="col-md-4 mb-3 form-group">
                                    <label for="sales_percent" class="form-label">Sale Percentage</label>
                                    <input type="number" class="form-control form-control-sm" id="sales_percent" name="sales_percent" placeholder="Sale Percentage">
                                </div>

                                <div class="col-md-4 mb-3 form-group">
                                    <label for="yearly_target" class="form-label">Yearly Target</label>
                                    <input type="number" class="form-control form-control-sm" id="yearly_target" name="yearly_target" placeholder="Yearly Target">
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> KPI Details</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">

                                <div class="col-md-4 mb-3 form-group">
                                    <label for="calls_goal_month" class="form-label">Calls /month</label>
                                    <input type="number" class="form-control form-control-sm" id="calls_goal_month" name="calls_goal_month" placeholder="Calls Goal /month">
                                </div>

                                <div class="col-md-4 mb-3 form-group">
                                    <label for="off_market_listing_goal_month" class="form-label">Off-Market Listings /m</label>
                                    <input type="number" class="form-control form-control-sm" id="off_market_listing_goal_month" name="off_market_listing_goal_month" placeholder="Off-Market Listings /m">
                                </div>

                                <div class="col-md-4 mb-3 form-group">
                                    <label for="published_listing_goal_month" class="form-label">Published Listings /m</label>
                                    <input type="number" class="form-control form-control-sm" id="published_listing_goal_month" name="published_listing_goal_month" placeholder="Published Listings /m">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Social Details</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="instagram" class="form-label">Instagram</label>
                                    <input type="text" class="form-control form-control-sm" id="instagram" name="instagram" placeholder="eg: https://instagram.com">
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="facebook" class="form-label">Facebook</label>
                                    <input type="text" class="form-control form-control-sm" id="facebook" name="facebook" placeholder="eg: https://facebook.com">
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="linkedin" class="form-label">LinkedIn</label>
                                    <input type="text" class="form-control form-control-sm" id="linkedin" name="linkedin" placeholder="eg: https://linkedin.com">
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="whatsapp" class="form-label">WhatsApp</label>
                                    <input type="text" class="form-control form-control-sm phone" id="whatsapp" name="whatsapp" placeholder="eg: 971">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Other Contact Details</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="phone_secondary" class="form-label">Phone 2</label>
                                    <input type="text" class="form-control form-control-sm phone" id="phone_secondary" name="phone_secondary" placeholder="eg: ">
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="email_secondary" class="form-label">Email 2</label>
                                    <input type="text" class="form-control form-control-sm" id="email_secondary" name="email_secondary" placeholder="eg: name@website.com">
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
                    <button type="submit" class="btn btn-primary btn-sm">Save User</button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    var usersGridView = $('#usersGrid');
    var usersTableView = $('#usersTableDiv');
    var urlParams = new URLSearchParams(window.location.search);
    var status = urlParams.get('status');
    //$(document).ready(function () {
    var usersTable = $("#usersTable").DataTable({
    //var usersTable = new DataTable('#usersTable', {
        responsive: true,
        select: {
            style: 'multi',
            selector: 'td:first-child input[type="checkbox"]',
            info: false,
            search: false,
        },
        
        ajax: {
            url: '{{ route('users.getUsers') }}' + (status ? '?status=' + status : ''),
            type: 'GET',
            dataSrc: function (json) {
                console.log(json.recordsTotal);
                return json.data; // Extract the data array from the response
            },
            serverSide: true, // Enable server-side processing
            processing: true, // Display processing indicator
            //paging: true, // Enable pagination
            //pageLength: 6, // Number of records per page
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    // Checkbox column
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input type="checkbox" class="user-checkbox form-check-input" value="' + row.id + '"></div>';
                },
                orderable: false,
                searchable: false,
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Customize the content as needed
                    return '<div class="d-flex align-items-center">' +
                        '<div class="symbol symbol-50px symbol-md-70px"><img src="' + row.profile_image_url + '" alt="User Image" class="img-fluid rounded"> </div>' +
                        '<div class="ms-3">' +
                        '<div class="fw-bold">' + row.name + '</div>' +
                        '<div> <a class="link" href="mailto:'+ row.email +'">' + row.email + '</a></div>' +
                        '<div> <a class="link" href="tel:'+ row.phone +'">' + row.phone + '</a></div>' +
                        '<div class="mt-1"> <span class="badge badge-dark"> '+ row.last_login +'</span> <span class="badge badge-dark mx-2">'+row.calls_count+' Calls</span></div>' +
                        '</div>' +
                        '</div>';
                },
            },
            { data: 'role' },
            { data: 'gender' },
            { data: 'brn' },
            { data: 'rera_no' },
            { data: 'extention' },
            { data: 'listings' },
            {
                data: 'id',
                render: function (data) {
                    return '<div class="text-end">' +
                        '<button class="btn btn-sm btn-icon mr-1 btn-dark btn-active-primary" data-href="{{ route('impersonate') }}/'+data+'" data-user-id="' + data + '" onclick="return confirmImpersonate(\'{{ route('impersonate') }}/'+data+'\')" type="button"><i class="fa fa-sign-in"></i></button>' +
                        '<button class="btn btn-sm btn-icon mr-1 btn-primary btn-active-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-action="edit" data-user-id="' + data + '"><i class="fa fa-pencil"></i></button>' +
                        '</div>';
                }
            },
        ],
        initComplete: function () {
            var api = this.api();
            console.log(api.page.info());
            console.log('Records Total:', api.page.info().recordsTotal);
            console.log('Records Display:', api.page.info().recordsDisplay);

            // Use the existing search elements for search
            $('.searchUser').on('keyup', function () {
                api.column(1).search($(this).val()).draw();
            });

            $('.searchGender').on('change', function () {
                api.column(3).search($(this).val()).draw();
            });

            $('.searchRole').on('change', function () {
                api.column(2).search($(this).val()).draw();
            });

            $('.searchBRN').on('keyup', function () {
                api.column(4).search($(this).val()).draw();
            });

            $('.searchRera').on('keyup', function () {
                api.column(5).search($(this).val()).draw();
            });

            $('.searchExtention').on('keyup', function () {
                api.column(6).search($(this).val()).draw();
            });

            $('.searchListings').on('keyup', function () {
                api.column(7).search($(this).val()).draw();
            });

        },
    });

    function confirmImpersonate(impersonateUrl) {
        Swal.fire({
            text: "Are you sure you want to impersonate this user?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, login this user!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = impersonateUrl;
            }
        });
        return false;
    }



        // necessary functions for the table:

        // Handle "Select All" checkbox
        // $('#selectAllUsers').on('change', function () {
        //     var selected = this.checked;
        //     $('.user-checkbox').prop('checked', selected);
        //     usersTable.rows().select(selected);
        //     updateBulkActionButtons();
        // });

        $('.selectAllUsers').on('change', function () {
            var checkboxes;
            // Check if in list view
            if (usersGridView.hasClass('d-none')) {
                checkboxes = usersTable.rows().nodes().to$().find('.user-checkbox');
            } else {
                // In DataTable view
                checkboxes = usersGridView.find('.user-checkbox');
            }

            checkboxes.prop('checked', this.checked);
            usersTable.rows().select(this.checked);

            // Trigger a change event on the checkboxes to ensure proper handling of bulk actions
            //checkboxes.trigger('change');
            updateBulkActionButtons();
        });

        // Handle individual checkbox change
        $('.user-checkbox').on('change', function () {
            var allChecked = $('.user-checkbox:checked').length === $('.user-checkbox').length;
            $('#selectAllUsers').prop('checked', allChecked);
            updateBulkActionButtons();
        });

        $('#toggleViewBtn').on('click', function () {
            $('.usersTableDiv, #usersGrid').toggleClass('d-none');
            toggleView();
            // Add logic to switch the view and populate grid if it's being shown
            if (!$('#usersGrid').hasClass('d-none')) {
                populateGridView(usersTable.rows().data().toArray());
            }
        });

        function toggleView() {
            var icon = document.getElementById('viewIcon');

            // Toggle between list and grid icons
            if (icon.classList.contains('fa-list')) {
                icon.classList.remove('fa-list');
                icon.classList.add('fa-th-large');
                usersTable.rows().deselect(); // Deselect all rows when switching to grid view
                // Update bulk action buttons for grid view
                updateBulkActionButtons();
            } else {
                icon.classList.remove('fa-th-large');
                icon.classList.add('fa-list');
                // Update bulk action buttons for list view
                updateBulkActionButtons();
            }
        }

        // end for tables

        // Function to populate the grid view with user cards
        function populateGridView(users) {
            var usersGridContent = $('#usersGridContent');
            usersGridContent.empty(); // Clear existing content

            users.forEach(function (user) {
                var userCard = '<div class="col-md-3"><div class="card mb-4 shadow">' +
                    '<div class="card-body px-4 pb-1 position-relative">' +
                        '<div class="position-absolute top-0 start-0 form-check form-check-sm form-check-custom form-check-solid">' +
                        '   <input type="checkbox" class="user-checkbox mt-3 ml-3 form-check-input" value="' + user.id + '">' +
                        '</div>' +
                        '<div class="position-absolute top-0 end-0">' +
                            '<button class="btn btn-sm btn-icon mr-1 btn-dark btn-active-primary" data-href="{{ route('impersonate') }}/'+user.id+'" data-user-id="' + user.id + '" onclick="return confirmImpersonate(\'{{ route('impersonate') }}/'+user.id+'\')" type="button"><i class="fa fa-sign-in"></i></button>' +
                            '<button class="btn btn-sm btn-icon mr-1 btn-light-primary btn-active-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-action="edit" data-user-id="' + user.id + '"><i class="fa fa-pencil"></i></button>' +
                        '</div>' +
                        '<div class="d-flex flex-column text-center mb-4 px-4">' +
                        '   <div class="symbol symbol-60px symbol-lg-100px mb-2">' +
                        '       <img src="' + user.profile_image_url + '" class="card-img-top" alt="User Image">' +
                        '   </div>' +
                        '   <div class="text-center">' +
                        '       <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-4">' + user.name + '</a>' +
                        '       <span class="text-muted d-block fw-semibold">' + user.email + '</span>' +
                        '   </div>' +
                        '</div>' +
                        '<div class="row px-9 mb-4">' +
                        '   <div class="col-md-6 text-center">' +
                        '       <div class="text-gray-800 fw-bold fs-3">' +
                        '           <span class="m-0 counted" data-kt-countup="true" data-kt-countup-value="' + user.listings + '" data-kt-initialized="1">' + user.listings + '</span>' +
                        '       </div>' +
                        '       <span class="text-gray-500 fs-8 d-block fw-bold">Listings</span>' +
                        '   </div>' +
                        '   <div class="col-md-6 text-center">' +
                        '       <div class="text-gray-800 fw-bold fs-3">' +
                        '           <span class="m-0 counted" data-kt-countup="true" data-kt-countup-value="0" data-kt-initialized="1">0</span>' +
                        '       </div>' +
                        '       <span class="text-gray-500 fs-8 d-block fw-bold">Leads</span>' +
                        '   </div>' +
                        '</div>' +
                        '<div class="separator my-5"> </div>'+
                        '<div class="">'+
                            '<div class="mb-4">' +
                            '   <a href="tel:' + user.phone + '" class="text-gray-500 fs-8 d-block fw-bold"><i class="fas fa-phone mr-1"></i> Phone: ' + user.phone + '</a>' +
                            '</div>' +
                            '<div class="mb-4">' +
                            '   <span class="text-gray-500 fs-8 d-block fw-bold"><i class="fas fa-user-tie mr-1"></i> Role: ' + user.role + '</span>' +
                            '</div>' +
                            '<div class="mb-4">' +
                            '   <span class="text-gray-500 fs-8 d-block fw-bold"><i class="fas fa-venus-mars mr-1"></i> Gender: ' + user.gender + '</span>' +
                            '</div>' +
                            '<div class="mb-4">' +
                            '   <span class="text-gray-500 fs-8 d-block fw-bold"><i class="fas fa-phone-alt mr-1"></i> Office Extention: ' + user.extention + '</span>' +
                            '</div>' +
                            '<div class="mb-4">' +
                            '   <span class="badge badge-dark fs-8 d-block fw-bold"><i class="fas fa-clock mr-1 text-white"></i> ' + user.last_login + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div></div>';

                usersGridContent.append(userCard);
            });

            // Add event handler for checkbox change
            usersGridContent.find('.user-checkbox').on('change', function () {
                updateBulkActionButtons();
            });
        }

        // Toggle button for switching views
        // $('#toggleViewBtn').on('click', function () {
        //     //usersGridView.toggleClass('d-none');
        //     usersTableView.toggleClass('d-none');
        // });

        function reloadDataTable() {
            usersTable.ajax.reload();
        }
    
        // Add the CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Fetch user data via AJAX and populate the modal
        $('#editModal').on('show.bs.modal', function (event) {
            $('#editUserForm')[0].reset();
            $('#role').trigger('change');
            $('.userImage').css('background-image', 'url({{ asset("assets/media/svg/avatars/blank-dark.svg") }})');

            var changeLogDiv = $('.changeLog');
            changeLogDiv.html('');
            
            var button = $(event.relatedTarget);
            //console.log(button.data('user-id'));
            var userId = button.data('user-id');
            var action = button.data('action');
            var form = $('#editUserForm');
            // Set user ID in the hidden input
            $('#editUserId').val(userId);

            var modalTitle = form.find('.modal-title');
            var modalAction = form.attr('action');
            
            if (action === 'create') {
                modalTitle.text('Create Role');
                modalAction = '{{ route('users.store') }}';
            } else if (action === 'edit') {
                modalTitle.text('Edit Role');
                modalAction = '{{ route('users.update', ':userId') }}'.replace(':userId', userId);
            }

            form.attr('action', modalAction);

            // Fetch user data via AJAX
            if (action === 'edit') {
                $.ajax({
                    url: '{{ route('users.edit', ['user' => ':userId']) }}'.replace(':userId', userId),
                    type: 'GET',
                    success: function(data) {
                        var user = data.user;

                        // Basic Details
                        $('#name').val(user.name);
                        $('#phone').val(user.phone);
                        $('#email').val(user.email);
                        $('#designation').val(user.designation);

                        $('.created_date').text(user.created_human);
                        $('.updated_date').text(user.updated_human);

                        // Login Details
                        $('#user_name').val(user.user_name);
                        $('#password').val(''); // You may not want to show the password for security reasons
                        if (user.roles.length > 0) {
                            $('#role').val(user.roles[0].id); // Assuming a user has only one role, adjust accordingly
                            $('#role').trigger('change');
                        }
                        
                        // Commission Details
                        $('#rental_percent').val(user.rental_percent);
                        $('#sales_percent').val(user.sales_percent);
                        $('#yearly_target').val(user.yearly_target);

                        // KPI Details
                        $('#calls_goal_month').val(user.calls_goal_month);
                        $('#off_market_listing_goal_month').val(user.off_market_listing_goal_month);
                        $('#published_listing_goal_month').val(user.published_listing_goal_month);

                        // Social Details
                        $('#instagram').val(user.instagram);
                        $('#facebook').val(user.facebook);
                        $('#linkedin').val(user.linkedin);
                        $('#whatsapp').val(user.whatsapp);

                        // Other Contact Details
                        $('#phone_secondary').val(user.phone_secondary);
                        $('#email_secondary').val(user.email_secondary);

                        // Set the selected gender and status
                        $('#gender').val(user.gender);
                        $('#status').val(user.status);

                        // Additional Details
                        $('#rera_no').val(user.rera_no);
                        $('#brn').val(user.brn);
                        $('#extention').val(user.extention);

                        if(user.is_teamleader == true){
                            $('#is_teamleader').prop('checked', true);
                        }

                        var userImageDiv = $('.userImage');
                        userImageDiv.css('background-image', 'url(' + user.profile_image + ')');

                        // Populate change log
                        
                        var activities = user.activities;
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
            
        });

        $('#editUserForm').submit(function(e) {
            e.preventDefault();

            var formData = new FormData($(this)[0]);

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(data) {
                    reloadDataTable();
                    $('#editModal').modal('hide');
                    // $('body').removeClass('modal-open');
                    // $('.modal-backdrop').remove();
                    if (data.message) {
                        toastr.success(data.message);
                    }
                },
                error: function(xhr, status, error) {
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
        
    //});

    function confirmDelete(deleteUrl) {
        Swal.fire({
            text: "Are you sure you want to delete this role?",
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
        // Check if in list view
        if (!usersGridView.hasClass('d-none')) {
            var selectedUsers = usersGridView.find('.user-checkbox:checked');
            var selectedUserCount = selectedUsers.length;

            // Toggle the class based on the number of selected users
            if (selectedUserCount > 0) {
                $('.bulkActions').removeClass('d-none');
            } else {
                $('.bulkActions').addClass('d-none');
            }
        } else {
            // In DataTable view
            var selectedUsers = usersTable.rows({ selected: true }).data().toArray();
            var selectedUserCount = selectedUsers.length;

            // Toggle the class based on the number of selected users
            if (selectedUserCount > 0) {
                $('.bulkActions').removeClass('d-none');
            } else {
                $('.bulkActions').addClass('d-none');
            }
        }
    }


    $('#bulkDeleteBtn').on('click', function () {
        var selectedUsers;
        // Check if in list view
        if (!usersGridView.hasClass('d-none')) {
            selectedUsers = usersGridView.find('.user-checkbox:checked').map(function() {
                return $(this).val();
            }).toArray();
        } else {
            // In DataTable view
            selectedUsers = usersTable.rows({ selected: true }).data().toArray().map(function(user) {
                return user.id;
            });
        }

        if (selectedUsers.length > 0) {
            performBulkAction('{{ route('users.bulkDelete') }}', { user_ids: selectedUsers });
        }
    });

    $('#bulkRestoreBtn').on('click', function () {
        var selectedUsers;
        // Check if in list view
        if (!usersGridView.hasClass('d-none')) {
            selectedUsers = usersGridView.find('.user-checkbox:checked').map(function() {
                return $(this).val();
            }).toArray();
        } else {
            // In DataTable view
            selectedUsers = usersTable.rows({ selected: true }).data().toArray().map(function(user) {
                return user.id;
            });
        }

        if (selectedUsers.length > 0) {
            performBulkAction('{{ route('users.bulkRestore') }}', { user_ids: selectedUsers });
        }
    });

    $('#bulkActivateBtn').on('click', function () {
        var selectedUsers;
        // Check if in list view
        if (!usersGridView.hasClass('d-none')) {
            selectedUsers = usersGridView.find('.user-checkbox:checked').map(function() {
                return $(this).val();
            }).toArray();
        } else {
            // In DataTable view
            selectedUsers = usersTable.rows({ selected: true }).data().toArray().map(function(user) {
                return user.id;
            });
        }

        if (selectedUsers.length > 0) {
            performBulkAction('{{ route('users.bulkActivate') }}', { user_ids: selectedUsers });
        }
    });

    $('#bulkDeactivateBtn').on('click', function () {
        var selectedUsers;
        // Check if in list view
        if (!usersGridView.hasClass('d-none')) {
            selectedUsers = usersGridView.find('.user-checkbox:checked').map(function() {
                return $(this).val();
            }).toArray();
        } else {
            // In DataTable view
            selectedUsers = usersTable.rows({ selected: true }).data().toArray().map(function(user) {
                return user.id;
            });
        }

        if (selectedUsers.length > 0) {
            performBulkAction('{{ route('users.bulkDeactivate') }}', { user_ids: selectedUsers });
        }
    });


    // Event handler for checkbox change
    $('body').on('change', 'input[name="user_ids[]"]', function () {
        updateBulkActionButtons();
    });

    usersTable.table().on('change', '.user-checkbox', function () {
        var allChecked = $('.user-checkbox:checked').length === $('.user-checkbox').length;
        $('.selectAllUsers').prop('checked', allChecked);
        updateBulkActionButtons();
    });

    // Initial update on page load
    updateBulkActionButtons();

    function performBulkAction(url, data) {
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
                        // toastr.success(data.message);
                        // reloadDataTable();
                        if(data.message){
                            toastr.success(data.message);
                            reloadDataTable();
                        }
                        else{
                            toastr.error(data.error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    }

    // $('#toggleViewBtn').on('click', function () {
    //     usersTableView.toggleClass('d-none');
    //     usersGridView.toggleClass('d-none');

    //     // Additional: Initialize grid view when switching to it
    //     if (!usersTableView.hasClass('d-none')) {
    //         initializeGridView();
    //     }
    // });
    
</script>
@endsection
