@extends('layouts.app')

@section('content')
@php
    $columns = [
        'team_name' => ['index' => 1, 'visible' => true],
        'team_leader' => ['index' => 2, 'visible' => true],
        'users' => ['index' => 3, 'visible' => true],
        'created_date' => ['index' => 4, 'visible' => true],
        'last_update' => ['index' => 5, 'visible' => true],
        'actions' => ['index' => 6, 'visible' => true],
    ];
@endphp

<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            {{ isset($_GET['status']) ? ucfirst($_GET['status']) : 'Active' }} Teams
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
                <i class="ki-duotone ki-plus fs-3"></i>
                Add
            </button>

            <a class="btn btn-flex btn-dark btn-sm mr-1" href="{{ route('teams.index') }}?status={{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'active' : 'inactive' }}">
                {{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'Active' : 'Inactive' }}
            </a>

            <a class="btn btn-flex btn-danger btn-sm mr-1" href="{{ route('teams.index') }}?status=deleted">
                Deleted
            </a>

            <button class="btn btn-flex btn-dark btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#columnsModal">
                <i class="fa fa-eye"></i>
                Columns
            </button>

            <button class="btn btn-flex btn-dark btn-sm mr-1" id="exportBtn" type="button">
                <i class="fa fa-file"></i>
                Export
            </button>
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
                        
                        <th id="1" class="{{ $columns['team_name']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchTeam input" placeholder="Search by Team name"></th>
                        <th id="2" class="{{ $columns['team_leader']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchTeamLeader input" placeholder="Search by Team Leader" />
                        </th>
                        <th id="3" class="{{ $columns['users']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchUsers input" placeholder="Search by total users" /></th>
                        <th id="4" class="{{ $columns['created_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCreateDate input" placeholder="Search by Date input" /></th>
                        <th id="5" class="{{ $columns['last_update']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDate input" placeholder="Search by Date input" /></th>
                        <th id="6"></th>
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
                <h5 class="modal-title" id="editModalLabel">Team Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="{{ route('teams.store') }}" method="post" enctype="multipart/form-data">
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
                        </div>
                        <div class="card-body py-4 px-5">

                            <div class="row mt-4">
                                <div class="col-lg-6 mb-3 form-group">
                                    <label for="name" class="form-label">Team Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Team Name" required>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="team_leader" class="form-label">Team Leader <span class="text-danger">*</span></label>
                                    <select name="team_leader" id="team_leader" class="form-control form-control-sm country selectTwoModal" required>
                                        <option value="">Select Leader</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12 d-flex justify-content-between">
                                    <h3>Select the team members below:</h3>
                                    <button class="btn btn-primary mb-3 btn-xs selectAllButton" type="button">Select All</button>
                                </div>
                                <div>
                                    <input type="text" class="form-control mb-3 form-control-sm" id="userFilter" placeholder="Filter users">
                                    <div id="totalSelectedUsers" class="fw-bold">Total Team Members: <span id="selectedUsersCount">0</span></div>
                                </div>
                                <div>
                                    <div class="separator my-4"></div>
                                </div>
                            </div>

                            <div class="row allUsersDiv">
                                
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
                    <button type="submit" class="btn btn-primary btn-sm">Save Team</button>
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
    const refnoParam = urlParams.get('refno');

    initializeDateRange('searchDate', '{{ $firstDate }}');
    initializeDateRange('searchCreateDate', '{{ $firstDate }}');
    var columnVisibility = {!! json_encode($columns) !!};

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
            url: '{{ route('teams.getTeams') }}' + (status ? '?status=' + status : ''),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function (d) {
                d.startDate = $('.searchDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endDate = $('.searchDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.startCreatedDate = $('.searchCreateDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endCreatedDate = $('.searchCreateDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.team = $('.searchTeam').val();
                d.team_leader = $('.searchTeamLeader').val();
            },
            //dataSrc: 'owners',
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
                data: 'name',
                visible: columnVisibility['team_name']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    return data.team_leader ? data.team_leader.name : '';
                },
                visible: columnVisibility['team_leader']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    return '<span class="badge badge-primary">'+ data.total_members +'</span>';
                },
                visible: columnVisibility['users']['visible'],
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
            //     $.ajax({
            //         url: '{{ route('owners.searchRefno') }}',
            //         method: 'POST',
            //         data: {
            //             refno: refnoParam,
            //             length: api.page.len(),
            //         },
            //         success: function (response) {
            //             if (response && response.record) {
            //                 // Record found, show the modal
            //                 $('#editModal').modal('show', {
            //                     backdrop: 'static',
            //                     keyboard: false
            //                 });
            //                 handleModalShown(response.record.id, 'edit');

            //                 console.log('page number: ' + response.pageNumber);
            //                 // Change DataTables page to the calculated page number
            //                 api.page(response.pageNumber).draw(false);
            //             } else {
            //                 // No record found
            //                 Swal.fire({
            //                     text: "No record found against your query.",
            //                     icon: "error",
            //                     showCancelButton: false,
            //                     confirmButtonText: "Ok",
            //                     confirmButtonColor: "#DF405C",
            //                 });
            //             }
            //         },
            //         error: function (error) {
            //             Swal.fire({
            //                 text: "Error searching for record",
            //                 icon: "error",
            //                 showCancelButton: false,
            //                 confirmButtonText: "Ok",
            //                 confirmButtonColor: "#DF405C",
            //             });
            //             console.error('Error searching for record:', error);
            //         }
            //     });
            // }
            
            // Use the existing search elements for search
            $('.searchTeam').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchTeamLeader').on('keyup', function () {
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
        
        //loadAllUsers();
        $('#editForm')[0].reset();
        
        var changeLogDiv = $('.changeLog');
        changeLogDiv.html('');

        var form = $('#editForm');
        var modalTitle = form.find('.modal-title');
        var modalAction = form.attr('action');
        
        if (action === 'create') {
            modalTitle.text('Create Team');
            modalAction = '{{ route('teams.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Team');
            modalAction = '{{ route('teams.update', ':itemId') }}'.replace(':itemId', itemId);
        }
        
        // Set ID in the hidden input
        $('#editId').val(itemId);

        form.attr('action', modalAction);
        
        // Fetch data via AJAX
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('teams.edit', ['team' => ':itemId']) }}'.replace(':itemId', itemId),
                type: 'GET',
                success: function(data) {
                    var team = data.team;

                    // Basic Details
                    $('#name').val(team.name);
                    //$('#team_leader').val(team.team_leader).trigger('change');
                    loadUsers('team_leader', team.team_leader);
                    loadAllUsers(team.users.map(user => user.id));
                    
                    var activities = team.activities;
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
        else{
            loadUsers('team_leader');
            loadAllUsers();
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
    }

    // Add this outside the document ready function to store the filtered users globally
    var filteredUsers = [];

    $(document).ready(function () {
        // Attach keyup event handler to the filter input
        $('#userFilter').keyup(function () {
            filterUsers($(this).val());
        });
    });

    function filterUsers(filter) {
        // Filter users based on the input value
        filteredUsers = [];

        // Iterate over all users and add matching ones to the filtered array
        $('input[name="agents[]"]').each(function () {
            var userName = $(this).siblings('label').find('.fs-8').text();
            if (userName.toLowerCase().includes(filter.toLowerCase())) {
                filteredUsers.push(this);
            }
        });

        // Re-render the users based on the filtered array
        renderFilteredUsers();
    }

    function renderFilteredUsers() {
        // Hide all users
        $('input[name="agents[]"]').closest('.col-md-2').hide();

        // Show only the filtered users
        $(filteredUsers).closest('.col-md-2').show();
    }

    checkSelectAllButton();
    
    $('.selectAllButton').click(function () {
        var newState = ($(this).text() === 'Select All') ? true : false;
        $('input[name="agents[]"]').prop('checked', newState);
        checkSelectAllButton();
    });

    $(document).on('change', 'input[name="agents[]"]', function () {
        checkSelectAllButton();
    });

    function checkSelectAllButton() {
        var allChecked = $('input[name="agents[]"]').length === $('input[name="agents[]"]:checked').length;
        var selectedCheckboxes = $('input[name="agents[]"]:checked');

        $('.selectAllButton').text(allChecked ? 'Unselect All' : 'Select All');
        $('#selectedUsersCount').text(selectedCheckboxes.length);
    }

    // function loadAllUsers(){
    //     usersDiv = $('.allUsersDiv');
    //     usersDiv.html('');

    //     $.ajax({
    //         url: '{{ route('users.getList') }}',
    //         type: 'GET',
    //         success: function(data) {
    //             var users = data.users;

    //             $.each(users, function(index, user) {
    //                 var userCol = '<div class="col-md-2">'+
    //                     '<input type="checkbox" class="btn-check" name="agents[]" value="'+user.id+'" id="user'+user.id+'"/>'+
    //                     '<label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-3 d-flex align-items-center mb-5" for="user'+user.id+'">'+
    //                         '<div class="symbol symbol-30px symbol-md-40px">'+
    //                             '<img src="'+user.profile_image_url+'" alt="image">'+
    //                         '</div>'+

    //                         '<span class="d-block fw-semibold text-start mx-3">'+
    //                             '<span class="text-gray-900 fw-bold d-block fs-8">'+user.name+'</span>'+
    //                             '<span class="text-muted fw-semibold fs-9 d-block">'+user.email+'</span>'+
    //                             '<span class="text-muted fw-semibold fs-9 d-block">'+user.designation+'</span>'+
    //                         '</span>'+
    //                     '</label>'+
    //                 '</div>';
    //                 usersDiv.append(userCol);
    //             });

    //             // Call checkSelectAllButton after loading users
    //             checkSelectAllButton();
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error fetching users:', error);
    //             reject(error);
    //         }
    //     });
    // }

    function loadAllUsers(selectedUserIds) {
        var usersDiv = $('.allUsersDiv');
        usersDiv.html('');

        $.ajax({
            url: '{{ route('users.getList') }}',
            type: 'GET',
            success: function (data) {
                var users = data.users;

                $.each(users, function (index, user) {
                    var isChecked = selectedUserIds && selectedUserIds.includes(user.id);

                    var userCol = '<div class="col-md-2">' +
                        '<input type="checkbox" class="btn-check" name="agents[]" value="' + user.id + '" id="user' + user.id + '" ' + (isChecked ? 'checked' : '') + '/>' +
                        '<label class="btn btn-outline btn-outline-dashed btn-active-light-primary p-3 d-flex align-items-center mb-5" for="user' + user.id + '">' +
                        '<div class="symbol symbol-30px symbol-md-40px">' +
                        '<img src="' + user.profile_image_url + '" alt="image">' +
                        '</div>' +

                        '<span class="d-block fw-semibold text-start mx-3">' +
                        '<span class="text-gray-900 fw-bold d-block fs-8">' + user.name + '</span>' +
                        '<span class="text-muted fw-semibold fs-9 d-block">' + user.email + '</span>' +
                        '<span class="text-muted fw-semibold fs-9 d-block">' + user.designation + '</span>' +
                        '</span>' +
                        '</label>' +
                    '</div>';
                    usersDiv.append(userCol);
                });

                // Call checkSelectAllButton after loading users
                checkSelectAllButton();
            },
            error: function (xhr, status, error) {
                console.error('Error fetching users:', error);
                reject(error);
            }
        });
    }

    // function loadUsers(selector){

    //     var selectElement = $('#'+selector).select2({
    //         dropdownParent: $("#editModal"),
    //         placeholder: 'Select Leader',
    //         allowClear: true
    //     });
        
    //     selectElement.empty();
    //     selectElement.html('<option value="">Select Leader</option>');

    //     $.ajax({
    //         url: '{{ route('users.getList') }}',
    //         type: 'GET',
    //         success: function(data) {
    //             var users = data.users;

    //             $.each(users, function(index, user) {
    //             var option = new Option(user.name, user.id, false, false);
    //             $(option).attr('data-name', user.name);
    //             selectElement.append(option);
    //         });
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error fetching users:', error);
    //             reject(error);
    //         }
    //     });
    // }

    function loadUsers(selector, leader_id) {
        var selectElement = $('#' + selector).select2({
            dropdownParent: $("#editModal"),
            placeholder: 'Select Leader',
            allowClear: true
        });

        selectElement.empty();
        selectElement.html('<option value="">Select Leader</option>');

        $.ajax({
            url: '{{ route('users.getList') }}',
            type: 'GET',
            success: function (data) {
                var users = data.users;

                $.each(users, function (index, user) {
                    var isSelected = leader_id && user.id == leader_id;
                    var option = new Option(user.name, user.id, isSelected, isSelected);
                    $(option).attr('data-name', user.name);
                    selectElement.append(option);
                });
            },
            error: function (xhr, status, error) {
                console.error('Error fetching users:', error);
                reject(error);
            }
        });
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

    // Fetch data via AJAX and populate the modal
    $('#editModal').on('show.bs.modal', function (event) {
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


        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                reloadDataTable();

                $('#editModal').modal('hide');
                submitButton.prop('disabled', false).html('Save Team');

                if (data.message) {
                    toastr.success(data.message);
                }
            },
            error: function(xhr, status, error) {
                submitButton.prop('disabled', false).html('Save Team');
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
            text: "Are you sure you want to delete this Team?",
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
            performBulkAction('{{ route('teams.bulkDelete') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkRestoreBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('teams.bulkRestore') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkActivateBtn').on('click', function () {
        var selectedItems;
        // Check if in list view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('teams.bulkActivate') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkDeactivateBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('teams.bulkDeactivate') }}', { item_ids: selectedItems });
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

    // $('#exportBtn').on('click', function () {
    //     // Disable export button and show processing message
    //     $(this).prop('disabled', true);
    //     Swal.fire({
    //         title: 'Exporting...',
    //         html: 'Export processing. Please wait...',
    //         allowEscapeKey: false,
    //         allowOutsideClick: false,
    //         didOpen: () => {
    //             Swal.showLoading();
    //         }
    //     });

    //     // Get selected item IDs
    //     var selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
    //         return data.id;
    //     });

    //     // Get filter values
    //     var filterValues = {};
    //     $('#filterHead .input').each(function() {
    //         var id = $(this).closest('th').attr('id');
    //         var value = $(this).val();

    //         // Handle date range inputs
    //         if (id === '9') {
    //             var startDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
    //             var endDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                
    //             filterValues['startDate'] = startDate;
    //             filterValues['endDate'] = endDate;
    //         }
    //         else if (id === '8') {
    //             var startCreatedDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
    //             var endCreatedDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                
    //             filterValues['startCreatedDate'] = startCreatedDate;
    //             filterValues['endCreatedDate'] = endCreatedDate;
    //         } else {
    //             switch(id) {
    //                 case '1':
    //                     //filterValues['source_name'] = value;
    //                     filterValues['owner_name'] = value;
    //                     break;
    //                 case '2':
    //                     //filterValues['source_name'] = value;
    //                     filterValues['refno'] = value;
    //                     break;
    //                 case '3':
    //                     //filterValues['source_name'] = value;
    //                     filterValues['whatsapp'] = value;
    //                     break;

    //                 case '4':
    //                     //filterValues['source_name'] = value;
    //                     filterValues['source_name'] = $(this).text() == 'All' ? '' : $(this).text();
    //                     break;
    //                 case '5':
    //                     //filterValues['sub_source_name'] = value;
    //                     filterValues['sub_source_name'] = $(this).text() == 'All' ? '' : $(this).text();
    //                     break;
    //                 case '6':
    //                     //filterValues['created_by'] = value;
    //                     filterValues['created_by_name'] = $(this).text() == 'All' ? '' : $(this).text();
    //                     break;
    //                 case '7':
    //                     //filterValues['upadted_by'] = value;
    //                     filterValues['updated_by_name'] = $(this).text() == 'All' ? '' : $(this).text();
    //                     break;
    //                 default:
    //                     filterValues['search' + id] = value;
    //                     break;
    //             }
    //         }
    //     });

    //     //console.log(filterValues);
    //     //AJAX request to the controller
    //     $.ajax({
    //         url: '{{ route('owners.export') }}',
    //         type: 'POST',
    //         data: {
    //             item_ids: selectedItems,
    //             filters: filterValues,
    //         },
    //         headers: {
    //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //         },
    //         success: function (data) {
    //             //console.log(data);
    //             // Enable export button and close the processing message
    //             $('#exportBtn').prop('disabled', false);
    //             Swal.close();

    //             // Convert base64 to Blob
    //             var binaryData = atob(data.file);
    //             var arrayBuffer = new ArrayBuffer(binaryData.length);
    //             var byteArray = new Uint8Array(arrayBuffer);
    //             for (var i = 0; i < binaryData.length; i++) {
    //                 byteArray[i] = binaryData.charCodeAt(i);
    //             }
    //             var blob = new Blob([arrayBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });

    //             // Create a temporary link element and trigger the download
    //             var link = document.createElement('a');
    //             link.href = window.URL.createObjectURL(blob);
    //             link.download = data.filename;
    //             document.body.appendChild(link);
    //             link.click();
    //             document.body.removeChild(link);
    //         },
    //         error: function (xhr, status, error) {
    //             // Enable export button and close the processing message
    //             $('#exportBtn').prop('disabled', false);
    //             Swal.close();

    //             // Handle errors if needed
    //             toastr.error(xhr.responseText);
    //         }
    //     });
    // });

 
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
