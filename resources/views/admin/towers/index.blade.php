@extends('layouts.app')

@section('content')
@php
    $columns = [
        'country' => ['index' => 1, 'visible' => false],
        'city' => ['index' => 2, 'visible' => true],
        'community' => ['index' => 3, 'visible' => true],
        'sub_community' => ['index' => 4, 'visible' => true],
        'tower' => ['index' => 5, 'visible' => true],
        'sale' => ['index' => 6, 'visible' => true],
        'rent' => ['index' => 7, 'visible' => true],
        'archive' => ['index' => 8, 'visible' => true],
        'created_date' => ['index' => 9, 'visible' => false],
        'last_update' => ['index' => 10, 'visible' => false],
        'actions' => ['index' => 11, 'visible' => true],
    ];
@endphp
<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            {{ isset($_GET['status']) ? ucfirst($_GET['status']) : 'Active' }} Towers
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

            <a class="btn btn-flex btn-dark btn-sm mr-1" href="{{ route('towers.index') }}?status={{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'active' : 'inactive' }}">
                {{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'Active' : 'Inactive' }}
            </a>

            <a class="btn btn-flex btn-danger btn-sm mr-1" href="{{ route('towers.index') }}?status=deleted">
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
                    <th class="px-2 rounded-start text-center" style="width:35px;"><input type="checkbox" class="selectAll" id="selectAll"></th>
                    @foreach($columns as $columnName => $columnDetails)
                        <th id="{{ $columnDetails['index'] }}" class="{{ $loop->last ? ' px-2 rounded-end text-end' : '' }}" style="{{ $loop->last ? 'width:90px;' : '' }}">{{ $columnName }}</th>
                    @endforeach
                </thead>
                <thead>
                    <tr id="filterHead">
                        <th id="0"></th>
                        <th id="1" class="{{ $columns['country']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCountry" placeholder="Search by Country name"></th>
                        <th id="2" class="{{ $columns['city']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCity" placeholder="Search by City name"></th>
                        <th id="3" class="{{ $columns['community']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCommunity" placeholder="Search by Community Name" /></th>
                        <th id="4" class="{{ $columns['sub_community']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchSubCommunity" placeholder="Search by Sub Community Name" /></th>
                        <th id="5" class="{{ $columns['tower']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchTower" placeholder="Search by Tower Name" /></th>
                        <th id="6" class="{{ $columns['sale']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchSaleCount" placeholder="Search by Sale" /></th>
                        <th id="7" class="{{ $columns['rent']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchRentCount" placeholder="Search by Rent " /></th>
                        <th id="8" class="{{ $columns['archive']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchArchiveCount" placeholder="Search by Archived" /></th>
                        <th id="9" class="{{ $columns['created_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCreateDate" placeholder="Search by Date" /></th>
                        <th id="10" class="{{ $columns['last_update']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDate" placeholder="Search by Date" /></th>
                        <th id="11" class="{{ $columns['actions']['visible'] ? '' : 'd-none' }}"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade modalRight" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-grey">
            <div class="modal-header py-3 bg-white">
                <h5 class="modal-title" id="editModalLabel">Tower Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="{{ route('towers.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Basic Details</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <select name="city_id" id="city" class="form-control form-control-sm city selectTwoModal" required>
                                        <option value="">Select City</option>
                                        @if(count($cities) > 0)
                                            @foreach($cities as $key => $city)
                                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="community" class="form-label">Community <span class="text-danger">*</span></label>
                                    <select name="community_id" id="community" class="form-control form-control-sm community selectTwoModal" required>
                                        <option value="">Select Community</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3 form-group">
                                    <label for="sub_community" class="form-label">Sub Community <span class="text-danger">*</span></label>
                                    <select name="sub_community_id" id="sub_community" class="form-control form-control-sm sub_community selectTwoModal" required>
                                        <option value="">Select Sub Community</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mb-3 form-group">
                                    <label for="name" class="form-label">Tower Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Tower Name" required>
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
                    <button type="submit" class="btn btn-primary btn-sm">Save Tower</button>
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
@include('layouts.scripts')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
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
        paging: true,
        processing: true,
        serverSide: true,
        order: [[2, 'asc'], [3, 'asc'], [4, 'asc']],
        ajax: {
            url: '{{ route('towers.getTowers') }}' + (status ? '?status=' + status : ''),
            type: 'GET',
            data: function (d) {
                d.startDate = $('.searchDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endDate = $('.searchDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.startCreatedDate = $('.searchCreateDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endCreatedDate = $('.searchCreateDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.country_name = $('.searchCountry').val();
                d.city_name = $('.searchCity').val();
                d.community_name = $('.searchCommunity').val();
                d.sub_community_name = $('.searchSubCommunity').val();
                d.tower_name = $('.searchTower').val();
            },
            // dataSrc: 'towers',
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
                data: 'country.name',
                render: function (data) {
                    return data === 'United Arab Emirates' ? 'UAE' : data;
                },
                visible: columnVisibility['country']['visible'],
            },
            {
                data: 'city.name',
                visible: columnVisibility['city']['visible'],
            },
            { 
                data: 'community.name',
                visible: columnVisibility['community']['visible'],
            },
            { data: 'sub_community.name', visible: columnVisibility['sub_community']['visible'], },
            {
                data: 'name',
                visible: columnVisibility['tower']['visible'],
            },
            {
                data: 'sales_listing_count',
                visible: columnVisibility['sale']['visible'],
            },
            {
                data: 'rental_listing_count',
                visible: columnVisibility['rent']['visible'],
            },
            {
                data: 'archive_listing_count',
                visible: columnVisibility['archive']['visible'],
            },
            {
                data: 'created_at',
                render: function(data, type, row) {
                    // Format the date as needed
                    // return moment(data).format('MMMM D, YYYY [at] h:mm A') + ' (' + moment(data).fromNow() + ')';
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
        // initComplete: function () {
        //     var api = this.api();

        //     // Use the existing search elements for search
        //     $('.searchCountry').on('keyup', function () {
        //         api.column(1).search($(this).val()).draw();
        //     });

        //     $('.searchCity').on('change', function () {
        //         api.column(2).search($(this).val()).draw();
        //     });

        //     $('.searchCommunity').on('change', function () {
        //         api.column(3).search($(this).val()).draw();
        //     });

        //     $('.searchSubCommunity').on('change', function () {
        //         api.column(4).search($(this).val()).draw();
        //     });

        //     $('.searchTower').on('change', function () {
        //         api.column(5).search($(this).val()).draw();
        //     });


        //     $('.searchSaleCount').on('change', function () {
        //         api.column(6).search($(this).val()).draw();
        //     });

        //     $('.searchRentCount').on('change', function () {
        //         api.column(7).search($(this).val()).draw();
        //     });

        //     $('.searchArchiveCount').on('change', function () {
        //         api.column(8).search($(this).val()).draw();
        //     });

        //     // $('.searchDate').on('apply.daterangepicker', function (ev, picker) {
        //     //     var startDate = picker.startDate.format('YYYY-MM-DD');
        //     //     var endDate = picker.endDate.format('YYYY-MM-DD');

        //     //     // Trigger an AJAX request to your Laravel controller
        //     //     $.ajax({
        //     //         url: '{{ route('towers.getTowers') }}',
        //     //         type: 'GET',
        //     //         data: {
        //     //             startDate: startDate,
        //     //             endDate: endDate,
        //     //         },
        //     //         success: function (data) {
        //     //             // Update DataTable with filtered data
        //     //             dataTable.clear().rows.add(data.towers).draw();
        //     //         },
        //     //         error: function (xhr, status, error) {
        //     //             console.error(xhr.responseText);
        //     //         }
        //     //     });
        //     // });

        //     $('.searchDate').on('apply.daterangepicker', function (ev, picker) {
        //         var startDate = picker.startDate.format('YYYY-MM-DD');
        //         var endDate = picker.endDate.format('YYYY-MM-DD');

        //         // Trigger an AJAX request to your Laravel controller
        //         $.ajax({
        //             url: '{{ route('towers.getTowers') }}',
        //             type: 'GET',
        //             data: {
        //                 startDate: startDate,
        //                 endDate: endDate,
        //             },
        //             success: function (data) {
        //                 // Update DataTable with filtered data
        //                 dataTable.clear().rows.add(data.towers).draw();
        //             },
        //             error: function (xhr, status, error) {
        //                 console.error(xhr.responseText);
        //             }
        //         });
        //     });

        //     $('.searchCreateDate').on('apply.daterangepicker', function (ev, picker) {
        //         var startDate = picker.startDate.format('YYYY-MM-DD');
        //         var endDate = picker.endDate.format('YYYY-MM-DD');

        //         // Trigger an AJAX request to your Laravel controller
        //         $.ajax({
        //             url: '{{ route('towers.getTowers') }}',
        //             type: 'GET',
        //             data: {
        //                 startCreatedDate: startDate,
        //                 endCreatedDate: endDate,
        //             },
        //             success: function (data) {
        //                 // Update DataTable with filtered data
        //                 dataTable.clear().rows.add(data.towers).draw();
        //             },
        //             error: function (xhr, status, error) {
        //                 console.error(xhr.responseText);
        //             }
        //         });
        //     });

        // },

        initComplete: function () {
            var api = this.api();

            // Use the existing search elements for search
            $('.searchCountry').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchCity').on('change', function () {
                api.clear().draw();
            });

            $('.searchCommunity').on('change', function () {
                api.clear().draw();
            });

            $('.searchSubCommunity').on('change', function () {
                api.clear().draw();
            });

            $('.searchTower').on('change', function () {
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

    // function populateCommunities(cityId, selectedCommunityId = null) {
    //     var communitySelect = $('#community').select2();
    //     communitySelect.empty();
    //     $('#sub_community').empty();
        
    //     communitySelect.html('<option value="">Select Community</option>');

    //     // Fetch communities via AJAX
    //     $.ajax({
    //         url: '{{ route('communities.getList') }}',
    //         type: 'GET',
    //         data: { city_id: cityId },
    //         success: function(data) {
    //             var communities = data.communities;

    //             if (!communities || communities.length === 0) {
    //                 console.error('No communities found for the selected city.');
    //                 return;
    //             }

    //             // Populate communities select element
    //             if(selectedCommunityId == null){
    //                 $.each(communities, function(index, community) {
    //                     // Only append options if the community belongs to the selected city
    //                     if (community.city_id == cityId) {
    //                         var option = new Option(community.name, community.id, false, false);
    //                         communitySelect.append(option);
    //                     }
    //                 });
    //             }

    //             // Select the community if community_id exists
    //             if (selectedCommunityId) {
    //                 communitySelect.val(selectedCommunityId).trigger('change');
    //             }
    //         },
    //         error: function(xhr, status, error) {
    //             console.error('Error fetching communities:', error);
    //         }
    //     });
    // }

    function populateCommunities(cityId, selectedCommunityId = null) {
        var communitySelect = $('#community');
        communitySelect.empty();
        $('#sub_community').empty();

        communitySelect.html('<option value="">Select Community</option>');

        // Fetch communities via AJAX
        var communitiesAjax = $.ajax({
            url: '{{ route('communities.getList') }}',
            type: 'GET',
            data: { city_id: cityId },
            success: function(data) {
                var communities = data.communities;

                if (!communities || communities.length === 0) {
                    console.error('No communities found for the selected city.');
                    return;
                }

                // Populate communities select element
                if (selectedCommunityId == null) {
                    $.each(communities, function(index, community) {
                        // Only append options if the community belongs to the selected city
                        if (community.city_id == cityId) {
                            var option = new Option(community.name, community.id, false, false);
                            communitySelect.append(option);
                        }
                    });
                }

                // Select the community if community_id exists
                if (selectedCommunityId) {
                    communitySelect.val(selectedCommunityId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching communities:', error);
            }
        });

        // Return the AJAX object to allow chaining
        return communitiesAjax;
    }


    function populateSubCommunities(cityId, community_id, selectedSubCommunityId = null) {
        var sub_communitySelect = $('#sub_community');
        sub_communitySelect.empty();
        //console.log('City id: ' + cityId + ' | Community id: ' + community_id + ' | sub community: ' + selectedSubCommunityId);
        sub_communitySelect.html('<option value="">Select Sub Community</option>');

        // Fetch communities via AJAX
        $.ajax({
            url: '{{ route('subCommunities.getList') }}',
            type: 'GET',
            data: { city_id: cityId, community_id: community_id },
            success: function(data) {
                //console.log('subcommunity: '+ selectedSubCommunityId);
                var sub_communities = data.sub_communities;

                if (!sub_communities || sub_communities.length === 0) {
                    console.error('No Sub Communities found for the selected city and community.');
                    return;
                }

                // Populate communities select element
                if(selectedSubCommunityId == null){
                    $.each(sub_communities, function(index, sub_community) {
                        // Only append options if the community belongs to the selected city
                        if (sub_community.community_id == community_id) {
                            var option = new Option(sub_community.name, sub_community.id, false, false);
                            sub_communitySelect.append(option);
                        }
                    });
                }

                // Select the community if community_id exists
                if (selectedSubCommunityId) {
                    console.log('City id: ' + cityId + ' | Community id: ' + community_id + ' | sub community: ' + selectedSubCommunityId);
                    sub_communitySelect.val(selectedSubCommunityId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching communities:', error);
            }
        });
    }

    $(document).on('change', '#city', function() {
        var selectedCityId = $(this).val();
        populateCommunities(selectedCityId);
    });

    $(document).on('change', '#community', function() {
        var selectedCityId = $('#city').val();
        var selectedId = $(this).val();
        populateSubCommunities(selectedCityId, selectedId);
    });
    
    // Fetch data via AJAX and populate the modal
    $('#editModal').on('shown.bs.modal', function (event) {
        $('#editForm')[0].reset();
        var changeLogDiv = $('.changeLog');
        changeLogDiv.html('');
        var button = $(event.relatedTarget);
        var itemId = button.data('id');
        var action = button.data('action');
        var form = $('#editForm');
        // Set ID in the hidden input
        $('#editId').val(itemId);

        var modalTitle = form.find('.modal-title');
        var modalAction = form.attr('action');
        
        if (action === 'create') {
            modalTitle.text('Create Tower');
            modalAction = '{{ route('towers.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Tower');
            modalAction = '{{ route('towers.update', ':itemId') }}'.replace(':itemId', itemId);
        }

        form.attr('action', modalAction);

        // Fetch data via AJAX
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('towers.edit', ['tower' => ':itemId']) }}'.replace(':itemId', itemId),
                type: 'GET',
                success: function(data) {
                    var tower = data.tower;

                    // Basic Details
                    $('#name').val(tower.name);
                    $('#city').val(tower.city_id).trigger('change');

                    populateCommunities(tower.city_id, tower.community_id).done(function () {
                        populateSubCommunities(tower.city_id, tower.community_id, tower.sub_community_id);
                    });

                    // Populate change log
                    
                    var activities = tower.activities;
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

    $('#editForm').submit(function(e) {
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

    function confirmDelete(deleteUrl) {
        Swal.fire({
            text: "Are you sure you want to delete this tower?",
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
            performBulkAction('{{ route('towers.bulkDelete') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkRestoreBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('towers.bulkRestore') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkActivateBtn').on('click', function () {
        var selectedItems;
        // Check if in list view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('towers.bulkActivate') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkDeactivateBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('towers.bulkDeactivate') }}', { item_ids: selectedItems });
        }
    });

    // Initial update on page load
    updateBulkActionButtons();

    // Event handler for checkbox change
    $('body').on('change', 'input[name="item_ids[]"]', function () {
        updateBulkActionButtons();
    });

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
        $('#filterHead input').each(function() {
            var id = $(this).closest('th').attr('id');
            var value = $(this).val();

            // Handle date range inputs
            if (id === '10') {
                var startDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                var endDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                
                filterValues['startDate'] = startDate;
                filterValues['endDate'] = endDate;
            }
            else if (id === '9') {
                var startCreatedDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                var endCreatedDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                
                filterValues['startCreatedDate'] = startCreatedDate;
                filterValues['endCreatedDate'] = endCreatedDate;
            } else {
                switch(id) {
                    case '1':
                        filterValues['country_name'] = value;
                        break;
                    case '2':
                        filterValues['city_name'] = value;
                        break;
                    case '3':
                        filterValues['community_name'] = value;
                        break;
                    case '4':
                        filterValues['sub_community_name'] = value;
                        break;
                    case '5':
                        filterValues['tower_name'] = value;
                        break;
                    case '6':
                        filterValues['sale_count'] = value;
                        break;
                    case '7':
                        filterValues['rent_count'] = value;
                        break;
                    case '8':
                        filterValues['archive_count'] = value;
                        break;
                    // Add cases for other inputs as needed
                    default:
                        // Handle other cases or use the default naming convention
                        filterValues['search' + id] = value;
                        break;
                }
            }
        });

        // AJAX request to the controller
        $.ajax({
            url: '{{ route('towers.export') }}',
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
@endsection
