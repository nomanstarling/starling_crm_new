@extends('layouts.app')

@section('content')
@php
    $columns = [
        'occupancy' => ['index' => 1, 'visible' => true],
        'sale' => ['index' => 2, 'visible' => true],
        'rent' => ['index' => 3, 'visible' => true],
        'archive' => ['index' => 4, 'visible' => true],
        'created_date' => ['index' => 5, 'visible' => true],
        'last_update' => ['index' => 6, 'visible' => true],
        'actions' => ['index' => 7, 'visible' => true],
    ];
@endphp
<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            {{ isset($_GET['status']) ? ucfirst($_GET['status']) : 'Active' }} Listing Occupancies
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

            <a class="btn btn-flex btn-dark btn-sm mr-1" href="{{ route('occupancies.index') }}?status={{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'active' : 'inactive' }}">
                {{ isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'Active' : 'Inactive' }}
            </a>

            <a class="btn btn-flex btn-danger btn-sm mr-1" href="{{ route('occupancies.index') }}?status=deleted">
                Deleted
            </a>

            <button class="btn btn-flex btn-dark btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#columnsModal">
                <i class="fa fa-eye"></i>
                Columns
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
                        <th id="1" class="{{ $columns['occupancy']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchOccupancy" placeholder="Search by Occupancy name"></th>
                        <th id="2" class="{{ $columns['sale']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchSaleCount" placeholder="Search by Sale" /></th>
                        <th id="3" class="{{ $columns['rent']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchRentCount" placeholder="Search by Rent " /></th>
                        <th id="4" class="{{ $columns['archive']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchArchiveCount" placeholder="Search by Archived" /></th>
                        <th id="5" class="{{ $columns['created_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCreateDate" placeholder="Search by Date" /></th>
                        <th id="6" class="{{ $columns['last_update']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDate" placeholder="Search by Date" /></th>
                        <th id="7" class="{{ $columns['actions']['visible'] ? '' : 'd-none' }}"></th>
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
                <h5 class="modal-title" id="editModalLabel">Project Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm" action="{{ route('occupancies.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">

                    <div class="card mt-4">
                        <div class="card-header p-4 bg-dark">
                            <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Basic Details</h3>
                        </div>
                        <div class="card-body py-4 px-5">
                            <div class="row mt-4">
                                <div class="col-md-12 mb-3 form-group">
                                    <label for="name" class="form-label">Occupancy Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Occupancy Name" required>
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
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
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
        order: [[1, 'asc'], [2, 'asc']],
        ajax: {
            url: '{{ route('occupancies.getOccupancies') }}' + (status ? '?status=' + status : ''),
            type: 'GET',
            data: function (d) {
                d.startDate = $('.searchDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endDate = $('.searchDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                d.startCreatedDate = $('.searchCreateDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                d.endCreatedDate = $('.searchCreateDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
            },
            dataSrc: 'listing_occupancies',
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
                visible: columnVisibility['occupancy']['visible'],
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
        initComplete: function () {
            var api = this.api();

            // Use the existing search elements for search
            $('.searchOccupancy').on('keyup', function () {
                api.column(1).search($(this).val()).draw();
            });

            $('.searchSaleCount').on('change', function () {
                api.column(2).search($(this).val()).draw();
            });

            $('.searchRentCount').on('change', function () {
                api.column(3).search($(this).val()).draw();
            });

            $('.searchArchiveCount').on('change', function () {
                api.column(4).search($(this).val()).draw();
            });

            $('.searchDate').on('apply.daterangepicker', function (ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');

                // Trigger an AJAX request to your Laravel controller
                $.ajax({
                    url: '{{ route('occupancies.getOccupancies') }}',
                    type: 'GET',
                    data: {
                        startDate: startDate,
                        endDate: endDate,
                    },
                    success: function (data) {
                        // Update DataTable with filtered data
                        dataTable.clear().rows.add(data.listing_occupancies).draw();
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });

            $('.searchCreateDate').on('apply.daterangepicker', function (ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');

                // Trigger an AJAX request to your Laravel controller
                $.ajax({
                    url: '{{ route('occupancies.getOccupancies') }}',
                    type: 'GET',
                    data: {
                        startCreatedDate: startDate,
                        endCreatedDate: endDate,
                    },
                    success: function (data) {
                        // Update DataTable with filtered data
                        dataTable.clear().rows.add(data.listing_occupancies).draw();
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
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
            modalTitle.text('Create Occupancy');
            modalAction = '{{ route('occupancies.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Occupancy');
            modalAction = '{{ route('occupancies.update', ':itemId') }}'.replace(':itemId', itemId);
        }

        form.attr('action', modalAction);

        // Fetch data via AJAX
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('occupancies.edit', ['occupancy' => ':itemId']) }}'.replace(':itemId', itemId),
                type: 'GET',
                success: function(data) {
                    var occupancy = data.occupancy;

                    // Basic Details
                    $('#name').val(occupancy.name);

                    // Populate change log
                    
                    var activities = occupancy.activities;
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
            text: "Are you sure you want to delete this occupancy?",
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
            performBulkAction('{{ route('occupancies.bulkDelete') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkRestoreBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('occupancies.bulkRestore') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkActivateBtn').on('click', function () {
        var selectedItems;
        // Check if in list view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('occupancies.bulkActivate') }}', { item_ids: selectedItems });
        }
    });

    $('#bulkDeactivateBtn').on('click', function () {
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            performBulkAction('{{ route('occupancies.bulkDeactivate') }}', { item_ids: selectedItems });
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
        
    //});

    
    
</script>
@endsection
