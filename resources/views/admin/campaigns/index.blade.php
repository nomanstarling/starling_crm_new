@extends('layouts.app')

@section('content')
@php
    $columns = [
        'campaign' => ['index' => 1, 'visible' => true],
        'community' => ['index' => 2, 'visible' => true],
        'sub_community' => ['index' => 3, 'visible' => true],
        'tower' => ['index' => 4, 'visible' => true],
        'source' => ['index' => 5, 'visible' => true],
        'users' => ['index' => 6, 'visible' => true],
        'created_date' => ['index' => 7, 'visible' => true],
        'last_update' => ['index' => 8, 'visible' => true],
        'actions' => ['index' => 9, 'visible' => true],
    ];
@endphp

<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            {{ isset($_GET['status']) ? ucfirst($_GET['status']) : 'Active' }} Campaigns
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

            <a class="btn btn-flex btn-dark btn-sm mr-1" href="{{ route('campaigns.index') }}?status={{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'active' : 'inactive' }}">
                {{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'Active' : 'Inactive' }}
            </a>

            <a class="btn btn-flex btn-danger btn-sm mr-1" href="{{ route('campaigns.index') }}?status=deleted">
                Deleted
            </a>

            <button class="btn btn-flex btn-dark btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#columnsModal">
                <i class="fa fa-eye"></i>
                Columns
            </button>

            <!-- <button class="btn btn-flex btn-dark btn-sm mr-1" id="exportBtn" type="button">
                <i class="fa fa-file"></i>
                Export
            </button> -->
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
                        
                        <th id="1" class="{{ $columns['campaign']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCampaign input" placeholder="Search by campaign name"></th>

                        <th id="2" class="{{ $columns['community']['visible'] ? '' : 'd-none' }}">
                            <select name="searchCommunity" id="searchCommunity" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchCommunity input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="3" class="{{ $columns['sub_community']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSubCommunity" id="searchSubCommunity" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchSubCommunity input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="4" class="{{ $columns['tower']['visible'] ? '' : 'd-none' }}">
                            <select name="searchTower" id="searchTower" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchTower input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="5" class="{{ $columns['source']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSource" id="searchSource" data-dropdown-css-class="w-200px" class="form-control form-control-sm selectTwo searchSource input">
                                <option value="" data-name="">All</option>
                                @if(count($sources))
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}" data-name="{{ $source->name }}">{{ $source->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="6" class="{{ $columns['users']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchUsers input" placeholder="Search by total users" /></th>
                        <th id="7" class="{{ $columns['created_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCreateDate input" placeholder="Search by Date input" /></th>
                        <th id="8" class="{{ $columns['last_update']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDate input" placeholder="Search by Date input" /></th>
                        <th id="9"></th>
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
                <h5 class="modal-title" id="editModalLabel">Campaign Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="{{ route('campaigns.store') }}" method="post" enctype="multipart/form-data">
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
                                    <label for="name" class="form-label">Campaign Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm form-control-solid border" id="name" name="name" placeholder="Campaign Name" required>
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label for="target_name" class="form-label">Matched Campaign Name (utm_source) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm form-control-solid border" id="target_name" name="target_name" placeholder="Matched Campaign Name (utm_source)" required>
                                </div>

                                <div class="col-lg-6 mb-3">
                                    <label for="source_id" class="form-label">Source</label>
                                    <select name="source_id" id="source_id" class="form-control form-control-sm country selectTwoModal form-control-solid border">
                                        <option value="">Select Source</option>
                                        @if(count($sources))
                                            @foreach($sources as $source)
                                                <option value="{{ $source->id }}" data-name="{{ $source->name }}">{{ $source->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label for="community_id" class="form-label">Community</label>
                                    <select name="community_id" id="community_id" class="form-control form-control-sm selectTwoModal community_id form-control-solid border">
                                        <option value="" data-name="">Select Community</option>
                                    </select>
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label for="sub_community_id" class="form-label">Sub Community</label>
                                    <select name="sub_community_id" id="sub_community_id" class="form-control form-control-sm selectTwoModal sub_community_id form-control-solid border">
                                        <option value="" data-name="">Select Sub Community</option>
                                    </select>
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label for="tower_id" class="form-label">Tower</label>
                                    <select name="tower_id" id="tower_id" class="form-control form-control-sm selectTwoModal tower_id form-control-solid border">
                                        <option value="" data-name="">Select Tower</option>
                                    </select>
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label for="tower_id" class="form-label">Automatically reassign leads</label>
                                    <div class="form-check">
                                        <input type="checkbox" id="auto_assign" class="form-check-input" name="auto_assign" value="1">
                                        <label for="auto_assign" class="form-check-label">
                                            Yes
                                        </label>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3 form-group">
                                    <label for="tower_id" class="form-label">Reassign leads after X minutes with no contact</label>
                                    <input type="text" class="form-control form-control-sm form-control-solid border" name="auto_assign_after" id="auto_assign_after" placeholder="eg: 60">
                                </div>

                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12 d-flex justify-content-between">
                                    <h3>Select the agents below:</h3>
                                    <button class="btn btn-primary mb-3 btn-xs selectAllButton" type="button">Select All</button>
                                </div>
                                <div>
                                    <input type="text" class="form-control mb-3 form-control-sm" id="userFilter" placeholder="Filter users">
                                    <div id="totalSelectedUsers" class="fw-bold">Total Agents: <span id="selectedUsersCount">0</span></div>
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
                    <button type="submit" class="btn btn-primary btn-sm">Save Campaign</button>
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
            url: '{{ route('campaigns.getCampaigns') }}' + (status ? '?status=' + status : ''),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function (d) {
                d.startDate = $('.searchDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endDate = $('.searchDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.startCreatedDate = $('.searchCreateDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endCreatedDate = $('.searchCreateDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.campaign = $('.searchCampaign').val();
            },
            //dataSrc: 'campaigns',
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
                visible: columnVisibility['campaign']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    return data.community ? data.community.name : '';
                },
                visible: columnVisibility['community']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    return data.sub_community ? data.sub_community.name : '';
                },
                visible: columnVisibility['sub_community']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    return data.tower ? data.tower.name : '';
                },
                visible: columnVisibility['tower']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    return data.source ? data.source.name : '';
                },
                visible: columnVisibility['source']['visible'],
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
            $('.searchCampaign').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchCommunity').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchSubCommunity').on('keyup', function () {
                api.clear().draw();
            });
            $('.searchTower').on('keyup', function () {
                api.clear().draw();
            });
            $('.searchSource').on('keyup', function () {
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
            modalTitle.text('Create Campaign');
            modalAction = '{{ route('campaigns.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Campaign');
            modalAction = '{{ route('campaigns.update', ':itemId') }}'.replace(':itemId', itemId);
        }
        
        // Set ID in the hidden input
        $('#editId').val(itemId);

        form.attr('action', modalAction);
        
        // Fetch data via AJAX
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('campaigns.edit', ['campaign' => ':itemId']) }}'.replace(':itemId', itemId),
                type: 'GET',
                success: function(data) {
                    var campaign = data.campaign;

                    // Retrieve the user ID based on the assignment_pointer
                    var agent_id;
                    var agents = campaign.users.map(user => user.id);
                    
                    if (campaign.assignment_pointer >= 0 && campaign.assignment_pointer < agents.length) {
                        agent_id = agents[campaign.assignment_pointer];
                    } else {
                        // If the assignment pointer is invalid, reset it to the first agent
                        agent_id = agents[0];
                    }

                    loadAllUsers(agents, agent_id);

                    // Basic Details
                    $('#name').val(campaign.name);
                    $('#target_name').val(campaign.target_name);
                    $('#source_id').val(campaign.source_id).trigger('change');

                    $('#auto_assign_after').val(campaign.auto_assign_after);

                    if (campaign.auto_assign == true) {
                        $('#auto_assign').prop('checked', true);
                    }

                    populateCommunities(7, 'community_id', campaign.community_id)
                    .then(function () {
                        return populateSubCommunities(7, 'sub_community_id', campaign.community_id, campaign.sub_community_id);
                    })
                    .then(function () {
                        return populateTowers(7, 'tower_id', campaign.community_id, campaign.sub_community_id, campaign.tower_id ? campaign.tower_id : 'two');
                    })
                    .catch(function(error) {
                        console.error('An error occurred:', error);
                    });

                    //$('#team_leader').val(team.team_leader).trigger('change');
                    //loadUsers('team_leader', campaign.team_leader);
                    
                    
                    var activities = campaign.activities;
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
            //loadUsers('team_leader');
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
            var userName = $(this).siblings('label').find('.userCard').text();
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

    function loadAllUsers(selectedUserIds, agent_id = null) {
        var usersDiv = $('.allUsersDiv');
        usersDiv.html('');

        $.ajax({
            url: '{{ route('users.getList') }}',
            type: 'GET',
            success: function (data) {
                var users = data.users;

                $.each(users, function (index, user) {
                    var isChecked = selectedUserIds && selectedUserIds.includes(user.id);
                    
                    var classToAdd = 'btn-active-light-primary';
                    var badge = '';

                    if(agent_id != null && user.id == agent_id){
                        badge = '<span class="position-absolute top-0 start-0 translate-middle badge badge-success">Next</span>';
                        var classToAdd = 'btn-active-light-success';
                    }

                    var userCol = '<div class="col-md-2">' +
                        '<input type="checkbox" class="btn-check" name="agents[]" value="' + user.id + '" id="user' + user.id + '" ' + (isChecked ? 'checked' : '') + '/>' +
                        '<label class="btn btn-outline position-relative btn-outline-dashed ' + classToAdd + ' p-2 mb-5" for="user' + user.id + '">' +
                        '<div class="d-flex align-items-center overflow-hidden">'+
                            badge +
                        '<div class="symbol symbol-30px symbol-md-40px">' +
                            '<img src="' + user.profile_image_url + '" alt="image">' +
                        '</div>' +

                        '<span class="d-block fw-semibold text-start mx-3">' +
                        '<span class="text-gray-900 fw-bold d-block fs-9 userCard">' + user.name + '</span>' +
                        '<span class="text-muted fw-semibold fs-9 d-block">' + user.email + '</span>' +
                        '<span class="text-muted fw-semibold fs-9 d-block">' + user.designation + '</span>' +
                        '</span>' +
                        '</div>' +
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

    // function loadUsers(selector, leader_id) {
    //     var selectElement = $('#' + selector).select2({
    //         dropdownParent: $("#editModal"),
    //         placeholder: 'Select Leader',
    //         allowClear: true
    //     });

    //     selectElement.empty();
    //     selectElement.html('<option value="">Select Leader</option>');

    //     $.ajax({
    //         url: '{{ route('users.getList') }}',
    //         type: 'GET',
    //         success: function (data) {
    //             var users = data.users;

    //             $.each(users, function (index, user) {
    //                 var isSelected = leader_id && user.id == leader_id;
    //                 var option = new Option(user.name, user.id, isSelected, isSelected);
    //                 $(option).attr('data-name', user.name);
    //                 selectElement.append(option);
    //             });
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Error fetching users:', error);
    //             reject(error);
    //         }
    //     });
    // }


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
                submitButton.prop('disabled', false).html('Save Campaign');

                if (data.message) {
                    toastr.success(data.message);
                }
            },
            error: function(xhr, status, error) {
                submitButton.prop('disabled', false).html('Save Campaign');
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
            text: "Are you sure you want to delete this Campaign?",
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
            performBulkAction('{{ route('campaigns.bulkDelete') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkRestoreBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('campaigns.bulkRestore') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkActivateBtn').on('click', function () {
        var selectedItems;
        // Check if in list view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('campaigns.bulkActivate') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkDeactivateBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('campaigns.bulkDeactivate') }}', { item_ids: selectedItems });
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

    function populateCommunities(cityId, selector, selectedCommunityId = null) {
        return new Promise(function(resolve, reject) {
            
            
            if(selector == 'community_id'){
                var communitySelect = $('#' + selector).select2({dropdownParent: $("#editModal")});
                communitySelect.empty();
                communitySelect.html('<option value="" data-name="">Select Community</option>');
            }
            else{
                var communitySelect = $('#' + selector).select2();
                communitySelect.empty();
                communitySelect.html('<option value="" data-name="">All</option>');
            }

            // Fetch communities via AJAX
            $.ajax({
                url: '{{ route('communities.getList') }}',
                type: 'GET',
                data: { city_id: cityId },
                success: function(data) {
                    var communities = data.communities;

                    if (!communities || communities.length === 0) {
                        console.error('No communities found for the selected city.');
                        reject('No communities found for the selected city.');
                        return;
                    }

                    // Populate communities select element
                    //if(selectedCommunityId == null){
                        $.each(communities, function(index, community) {
                            // Only append options if the community belongs to the selected city
                            if (community.city_id == cityId) {
                                var option = new Option(community.name, community.id, false, false);
                                $(option).attr('data-name', community.name);
                                communitySelect.append(option);
                            }
                        });
                    //}

                    // Select the community if community_id exists
                    if (selectedCommunityId) {
                        communitySelect.val(selectedCommunityId).trigger('change');
                    }

                    // Resolve the promise when done
                    resolve();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching communities:', error);
                    reject(error);
                }
            });
        });
    }

    function populateSubCommunities(cityId, selector, community_id, selectedSubCommunityId = null) {
        return new Promise(function(resolve, reject) {
            if(selector == 'sub_community_id'){
                var sub_communitySelect = $('#'+selector).select2({dropdownParent: $("#editModal")});
            }
            else{
                var sub_communitySelect = $('#'+selector).select2();
            }
            
            sub_communitySelect.empty();
            if(selector == 'sub_community_id'){
                sub_communitySelect.html('<option value="" data-name="">Select Sub Community</option>');
            }
            else{
                sub_communitySelect.html('<option value="" data-name="">All</option>');
            }

            // Fetch sub-communities via AJAX
            $.ajax({
                url: '{{ route('subCommunities.getList') }}',
                type: 'GET',
                data: { city_id: cityId, community_id: community_id },
                success: function(data) {
                    //console.log('subcommunity: '+ selectedSubCommunityId);
                    var sub_communities = data.sub_communities;

                    if (!sub_communities || sub_communities.length === 0) {
                        console.error('No Sub Communities found for the selected city and community.');
                        reject('No Sub Communities found for the selected city and community.');
                        return;
                    }

                    // Populate communities select element
                    if(selectedSubCommunityId == null){
                        $.each(sub_communities, function(index, sub_community) {
                            // Only append options if the community belongs to the selected city
                            if (sub_community.community_id == community_id) {
                                var option = new Option(sub_community.name, sub_community.id, false, false);
                                $(option).attr('data-name', sub_community.name);
                                sub_communitySelect.append(option);
                            }
                        });
                    }

                    // Select the community if community_id exists
                    if (selectedSubCommunityId) {
                        sub_communitySelect.val(selectedSubCommunityId).trigger('change');
                    }

                    // Resolve the promise when done
                    resolve();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching sub-communities:', error);
                    reject(error);
                }
            });
        });
    }

    function populateTowers(cityId, selector, community_id, sub_community_id, selected_id = null) {
        
        return new Promise(function(resolve, reject) {
            if(selector == 'tower_id'){
                var selectElement = $('#'+selector).select2({dropdownParent: $("#editModal")});
            }
            else{
                var selectElement = $('#'+selector).select2();
            }

            selectElement.empty();

            if(selector == 'tower_id'){
                selectElement.html('<option value="" data-name="">Select Tower</option>');
            }
            else{
                selectElement.html('<option value="" data-name="">All</option>');
            }

            // Fetch towers via AJAX
            $.ajax({
                url: '{{ route('towers.getList') }}',
                type: 'GET',
                data: { city_id: cityId, community_id: community_id, sub_community_id: sub_community_id },
                success: function(data) {
                    //console.log('subcommunity: '+ selected_id);
                    var towers = data.towers;
                    //console.log(towers);

                    if (!towers || towers.length === 0) {
                        //console.error('No towers found for the selected city, community, and sub community.');
                        //reject('No towers found for the selected city, community, and sub community.');
                        return;
                    }

                    // Populate communities select element
                    if(selected_id == null){
                        $.each(towers, function(index, tower) {
                            // Only append options if the community belongs to the selected city
                            if (tower.sub_community_id == sub_community_id) {
                                var option = new Option(tower.name, tower.id, false, false);
                                $(option).attr('data-name', tower.name);
                                selectElement.append(option);
                            }
                        });
                    }

                    // Select the community if community_id exists
                    if (selected_id) {
                        selectElement.val(selected_id).trigger('change');
                    }

                    // Resolve the promise when done
                    resolve();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching towers:', error);
                    reject(error);
                }
            });
        });
    }


    populateCommunities(7, 'community_id');
    populateCommunities(7, 'searchCommunity');

    // $(document).on('change', '#community_id', function() {
    //     var selectedCityId = $('#city_id').val();
    //     var selectedId = $(this).val();
    //     populateSubCommunities(selectedCityId, 'sub_community_id', selectedId);
    // });

    $(document).on('change', '#searchCommunity', function() {
        var selectedCityId = 7;
        var selectedId = $(this).val();
        populateSubCommunities(selectedCityId, 'searchSubCommunity', selectedId);
    });

    $(document).on('change', '#searchSubCommunity', function() {
        //alert('ok');
        var selectedCityId = 7;
        var selectedId = $(this).val();
        var searchCommunity = $('#searchCommunity').val();
        
        populateTowers(selectedCityId, 'searchTower', searchCommunity, selectedId);
    });

    $(document).on('change', '#community_id', function() {
        var selectedCityId = 7;
        var selectedId = $(this).val();
        populateSubCommunities(selectedCityId, 'sub_community_id', selectedId);
    });

    $(document).on('change', '#sub_community_id', function() {
        //alert('ok');
        var selectedCityId = 7;
        var selectedId = $(this).val();
        var searchCommunity = $('#community_id').val();
        
        populateTowers(selectedCityId, 'tower_id', searchCommunity, selectedId);
    });

    
    
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
