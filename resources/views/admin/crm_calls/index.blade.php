@extends('layouts.app')

@section('content')
@php
    $columns = [
        'agent' => ['index' => 1, 'visible' => true],
        'start_date' => ['index' => 2, 'visible' => true],
        'answer_date' => ['index' => 3, 'visible' => true],
        'direction' => ['index' => 4, 'visible' => true],
        'source' => ['index' => 5, 'visible' => true],
        'ip' => ['index' => 6, 'visible' => false],
        'destination' => ['index' => 7, 'visible' => true],
        'hang_side' => ['index' => 8, 'visible' => true],
        'reason' => ['index' => 9, 'visible' => true],
        'duration' => ['index' => 10, 'visible' => true],
        'codec' => ['index' => 10, 'visible' => false],
        'rtp_send' => ['index' => 11, 'visible' => false],
        'rtp_recv' => ['index' => 12, 'visible' => false],
        'loss_rate' => ['index' => 13, 'visible' => false],
        'BCCH' => ['index' => 14, 'visible' => false]
    ];
@endphp
<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            Calls
        </h2>

        <div class="card-toolbar">
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
                        <th id="1" class="{{ $columns['agent']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchAgent"  style="width:120px;"placeholder="Search by Agent"></th>
                        <th id="4" class="{{ $columns['start_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchStartDate" placeholder="Search by Date" /></th>
                        <th id="5" class="{{ $columns['answer_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchAnswerDate" placeholder="Search by Date" /></th>
                        <th id="2" class="{{ $columns['direction']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDirection" placeholder="Search by Amenity" /></th>
                        <th id="3" class="{{ $columns['source']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchSource" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['ip']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchIP" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['destination']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDestination" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['hang_side']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchHandSide" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['reason']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchReason" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['duration']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDuration" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['codec']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCodec" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['rtp_send']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchRTPSend" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['rtp_recv']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchRTPRecv" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['loss_rate']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchLossRate" placeholder="Search by Type " /></th>
                        <th id="3" class="{{ $columns['BCCH']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchBCCH" placeholder="Search by Type " /></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
        ajax: {
            url: '{{ route('calls.getCalls') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function (d) {
                // d.startDate = $('.searchStartDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                // d.endDate = $('.searchStartDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

                // d.startAnswerDate = $('.searchAnswerDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                // d.endAnswerDate = $('.searchAnswerDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                d.agent = $('.searchAgent').val();
            },
           // dataSrc: 'calls',
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
                data: null,
                render: function(data, type, row) {
                    return data.user ? data.user.name : '';
                },
                visible: columnVisibility['agent']['visible'],
            },
            {
                data: 'start_date',
                render: function(data, type, row) {
                    return moment.utc(data).format('MMMM D, YYYY') + ' (' + moment.utc(data).fromNow() + ')';
                },
                visible: columnVisibility['start_date']['visible'],
            },
            {
                data: 'answer_date',
                render: function(data, type, row) {
                    return data != null ? moment.utc(data).format('MMMM D, YYYY') + ' (' + moment.utc(data).fromNow() + ')' : '';
                },
                visible: columnVisibility['answer_date']['visible'],
            },
            
            {
                data: 'direction',
                visible: columnVisibility['direction']['visible'],
            },
            {
                data: 'source',
                visible: columnVisibility['source']['visible'],
            },
            
            {
                data: 'ip',
                visible: columnVisibility['ip']['visible'],
            },
            {
                data: 'destination',
                visible: columnVisibility['destination']['visible'],
            },
            {
                data: 'hang_side',
                visible: columnVisibility['hang_side']['visible'],
            },
            {
                data: 'reason',
                visible: columnVisibility['reason']['visible'],
            },
            {
                data: 'duration',
                visible: columnVisibility['duration']['visible'],
            },
            {
                data: 'codec',
                visible: columnVisibility['codec']['visible'],
            },
            {
                data: 'rtp_send',
                visible: columnVisibility['rtp_send']['visible'],
            },
            {
                data: 'rtp_recv',
                visible: columnVisibility['rtp_recv']['visible'],
            },
            {
                data: 'loss_rate',
                visible: columnVisibility['loss_rate']['visible'],
            },
            {
                data: 'BCCH',
                visible: columnVisibility['BCCH']['visible'],
            },
        ],
        initComplete: function () {
            var api = this.api();

            // Use the existing search elements for search
            $('.searchAgent').on('keyup', function () {
                api.clear().draw();
            });

            // $('.searchAmenity').on('change', function () {
            //     api.column(2).search($(this).val()).draw();
            // });

            // $('.searchType').on('change', function () {
            //     api.column(3).search($(this).val()).draw();
            // });

            // $('.searchDate').on('apply.daterangepicker', function (ev, picker) {
            //     var startDate = picker.startDate.format('YYYY-MM-DD');
            //     var endDate = picker.endDate.format('YYYY-MM-DD');

            //     // Trigger an AJAX request to your Laravel controller
            //     $.ajax({
            //         url: '{{ route('amenities.getAmenities') }}',
            //         type: 'GET',
            //         data: {
            //             startDate: startDate,
            //             endDate: endDate,
            //         },
            //         success: function (data) {
            //             // Update DataTable with filtered data
            //             dataTable.clear().rows.add(data.amenities).draw();
            //         },
            //         error: function (xhr, status, error) {
            //             console.error(xhr.responseText);
            //         }
            //     });
            // });

            // $('.searchCreateDate').on('apply.daterangepicker', function (ev, picker) {
            //     var startDate = picker.startDate.format('YYYY-MM-DD');
            //     var endDate = picker.endDate.format('YYYY-MM-DD');

            //     // Trigger an AJAX request to your Laravel controller
            //     $.ajax({
            //         url: '{{ route('amenities.getAmenities') }}',
            //         type: 'GET',
            //         data: {
            //             startCreatedDate: startDate,
            //             endCreatedDate: endDate,
            //         },
            //         success: function (data) {
            //             // Update DataTable with filtered data
            //             dataTable.clear().rows.add(data.amenities).draw();
            //         },
            //         error: function (xhr, status, error) {
            //             console.error(xhr.responseText);
            //         }
            //     });
            // });

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
    //});

    
    
</script>
@endsection
