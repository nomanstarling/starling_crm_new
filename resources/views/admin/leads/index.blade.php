@extends('layouts.app')

@section('content')
@php
    use Carbon\Carbon;
    $carbonDateTime = Carbon::now()->format('Y-m-d H:i:s');
@endphp
@php
    $columns = [
        'refno' => ['index' => 1, 'visible' => true, 'width' => '90px'],
        'status' => ['index' => 2, 'visible' => true, 'width' => '90px'],
        'sub_status' => ['index' => 3, 'visible' => false, 'width' => '90px'],

        'stage' => ['index' => 4, 'visible' => true, 'width' => '90px'],

        'last_update' => ['index' => 5, 'visible' => true, 'width' => '90px'],
        'enquiry_date' => ['index' => 6, 'visible' => true, 'width' => '90px'],
        'added_on' => ['index' => 7, 'visible' => false, 'width' => '90px'],
        
        'client_details' => ['index' => 8, 'visible' => true, 'width' => '90px'],
        'property_details' => ['index' => 9, 'visible' => true, 'width' => '90px'],
        'campaign' => ['index' => 10, 'visible' => true, 'width' => '90px'],

        'lead_agent' => ['index' => 11, 'visible' => true, 'width' => '90px'],
        'assigned_on' => ['index' => 12, 'visible' => true, 'width' => '90px'],
        'accepted_on' => ['index' => 13, 'visible' => true, 'width' => '90px'],

        'source' => ['index' => 14, 'visible' => true, 'width' => '90px'],
        'sub_source' => ['index' => 15, 'visible' => true, 'width' => '90px'],
        
        'created_by' => ['index' => 16, 'visible' => false, 'width' => '90px'],
        'updated_by' => ['index' => 17, 'visible' => false, 'width' => '90px'],

        'actions' => ['index' => 18, 'visible' => true, 'width' => '90px'], 
    ];
@endphp
<style>
    .dropzone-queue{
        min-height: 60px !important;
    }
    table.dataTable thead tr>.dtfc-fixed-left, table.dataTable thead tr>.dtfc-fixed-right{
        background:#009EF7 !important;
        border-radius: 0px !important;
    }
    .image_dropzone{
        background-color: #f1faff !important;
        border-radius: 0.475rem !important;
        border: 1px dashed #f1faff !important;
        min-height: auto !important;
        padding: 1.5rem 1.75rem !important;
    }
    .notesDiv{
        max-height:500px !important;
        overflow:scroll;
        padding-left:20px;
    }
    .changeLogDiv{
        max-height:500px !important;
        overflow:scroll;
    }
    .ck-editor__editable_inline {
        height: 120px !important;
    }
</style>
<input type="hidden" name="lead_update_perm" id="lead_update_perm" value="{{ auth()->user()->can('leads_update') ? 'true' : 'false' }}">
<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            @if(isset($_GET['archived']))
                Deleted Leads
            @else
                {{ isset($_GET['type']) && $_GET['type'] != null ? ucfirst($_GET['type']) : 'All' }} Leads
            @endif
        </h2>

        <div class="card-toolbar">

            <div class="bulkActions d-none mr-1">
                <div>
                    <button type="button" class="btn btn-active-color-white btn-active-primary btn-primary rounded btn-color-white py-2 px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate" data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start">
                        <span class="d-none d-md-inline"> <i class="fa fa-tasks" style="margin-right:10px;"></i>Bulk Actions</span> 
                        <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i> 
                    </button>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px" data-kt-menu="true">
                        @if(isset($_GET['archived']))
                            @can('leads_delete')
                                <div class="menu-item mx-3 my-3">
                                    <a class="menu-link px-3 fs-7 gap-3" id="bulkRestoreBtn" href="">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                        Restore
                                    </a>
                                </div>
                            @endcan
                        @else
                            @can('leads_assign')
                                <div class="menu-item mx-3 mt-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3" id="bulkAssign" type="button">
                                        <i class="fa fa-user"></i>
                                        Assign to Agent
                                    </a>
                                </div>
                            @endcan

                            @can('leads_status_change')
                                <div class="menu-item mx-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3 bulkStatusChangeBtn" id="bulkStatusChangeBtn" type="button">
                                        <i class="fa fa-check"></i>
                                        Change Status 
                                    </a>
                                </div>
                            @endcan

                            <!-- <div class="menu-item mx-3">
                                <a class="menu-link px-3 fs-7 gap-3" id="bulkDeleteBtn" href="">
                                    <i class="fa fa-trash"></i>
                                    Delete
                                </a>
                            </div> -->

                        @endif
                    </div>
                </div>
                
            </div>
            @if(!isset($_GET['archived']))
                @can('leads_create')
                    <button class="btn btn-flex btn-primary btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#editModal" data-action="create">
                        <i class="ki-duotone ki-plus fs-3"></i>
                        Add
                    </button>
                @endcan
            @endif

            <button class="btn btn-flex btn-dark btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#columnsModal">
                <i class="fa fa-eye"></i>
                Columns
            </button>
            @if(!isset($_GET['archived']))
                @can('leads_export')
                    <button class="btn btn-flex btn-dark btn-sm mr-1" id="exportBtn" type="button">
                        <i class="fa fa-file"></i>
                        Export
                    </button>
                @endcan
            @endif
        </div>
    </div>

    <style>
        #dataTable{
        }
        #dataTable tbody td {
            white-space: -o-pre-wrap; 
            word-wrap: break-word;
            white-space: pre-wrap; 
            white-space: -moz-pre-wrap; 
            white-space: -pre-wrap;
        }
        table thead th input, table thead th .selectTwo{
            font-size:10px !important;
        }
        table tbody tr td{
            font-weight: 600 !important;
            font-size:10px !important;
        }
        .searchStatus{
            width:110px !important;
        }
        .searchSubStatus{
            width:80px !important;
        }
        .searchStage{
            width:70px !important;
        }

        .searchLeadAgent{
            width:120px !important;
        }
        
        .searchCreatedBy{
            width:100px !important;
        }
        .searchUpdatedBy{
            width:100px !important;
        }
        .searchCreateDate{
            width:150px !important;
        }
        .searchDate{
            width:100px !important;
        }
        .searchEnqDate{
            width:100px !important;
        }

        .searchAssignedOn{
            width:100px !important;
        }
        .searchAcceptedOn{
            width:100px !important;
        }
    </style>
    <div class="card-body">
        <div class="tableDiv" id="tableDiv">
            <table class="table w-100 table-hover table-row-dashed" id="dataTable">
                <thead class="text-start bg-dark text-white fw-bold fs-7 text-uppercase gs-0">
                    <th id="0" class="px-2 rounded-start text-center" style="width:35px;">
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input type="checkbox" class="selectAll form-check-input" id="selectAll">
                        </div>
                    </th>
                    @foreach($columns as $columnName => $columnDetails)
                        @php
                            $formattedColumnName = str_replace('_', ' ', $columnName);
                        @endphp
                        <th id="{{ $columnDetails['index'] }}" class="{{ $loop->last ? ' px-2 rounded-end text-end' : '' }}" style="width: {{ $columnDetails['width'] }} !important;">{{ $formattedColumnName }}</th>
                    @endforeach
                </thead>
                <thead>
                    <tr id="filterHead">
                        <th id="0"></th>
                        <th id="1" class="{{ $columns['refno']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm form-control-solid border searchRefno input" placeholder="Search by refno" style="width:100px !important;">
                        </th>
                        <th id="2" class="{{ $columns['status']['visible'] ? '' : 'd-none' }}">
                            <select name="searchStatus" id="searchStatus" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchStatus input">
                                <option value="" data-name="">All</option>
                                @if(count($statuses))
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" data-name="{{ $status->name }}">{{ $status->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>
                        <th id="3" class="{{ $columns['sub_status']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSubStatus" id="searchSubStatus" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchSubStatus input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="4" class="{{ $columns['stage']['visible'] ? '' : 'd-none' }}">
                            <select name="searchStage" id="searchStage" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchStage input">
                                <option value="" data-name="">All</option>
                                <option value="Cold" data-name="Cold">Cold</option>
                                <option value="Warm" data-name="Warm">Warm</option>
                                <option value="Hot" data-name="Hot">Hot</option>
                            </select>
                        </th>

                        <th id="5" class="{{ $columns['last_update']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm form-control-solid border searchDate input" placeholder="Search by Date input" /></th>
                        <th id="6" class="{{ $columns['enquiry_date']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm form-control-solid border searchEnqDate input" placeholder="Search by Date input" /></th>
                        <th id="7" class="{{ $columns['added_on']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm form-control-solid border searchCreateDate input" placeholder="Search by Date input" /></th>

                        <th id="8" class="{{ $columns['client_details']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchClientDetails input form-control-solid border" placeholder="Search by client name, email, phone" style="width:120px !important;" />
                        </th>

                        <th id="9" class="{{ $columns['property_details']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchPropertyDetails input form-control-solid border" placeholder="Search by property details" style="width:130px !important;" />
                        </th>

                        <th id="10" class="{{ $columns['campaign']['visible'] ? '' : 'd-none' }}">
                            <select name="searchCampaign" id="searchCampaign" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchCampaign input">
                                <option value="" data-name="">All</option>
                                @if(count($campaigns))
                                    @foreach($campaigns as $campaign)
                                        <option value="{{ $campaign->id }}" data-name="{{ $campaign->name }}">{{ $campaign->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="11" class="{{ $columns['lead_agent']['visible'] ? '' : 'd-none' }}">
                            <select name="searchLeadAgent" data-dropdown-css-class="w-200px" id="searchLeadAgent" class="form-select form-select-sm form-select-solid border selectTwo searchLeadAgent input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="12" class="{{ $columns['assigned_on']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm form-control-solid border searchAssignedOn input" placeholder="Search by Date input" /></th>
                        <th id="13" class="{{ $columns['accepted_on']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm form-control-solid border searchAcceptedOn input" placeholder="Search by Date input" /></th>
                        
                        <th id="14" class="{{ $columns['source']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSource" data-dropdown-css-class="w-200px" id="searchSource" class="form-select form-select-sm form-select-solid border selectTwo searchSource input">
                                <option value="" data-name="">All</option>
                                @if(count($sources))
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}" data-name="{{ $source->name }}">{{ $source->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="15" class="{{ $columns['sub_source']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSubSource" data-dropdown-css-class="w-200px" id="searchSubSource" class="form-select form-select-sm form-select-solid border selectTwo searchSubSource input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="16" class="{{ $columns['created_by']['visible'] ? '' : 'd-none' }}">
                            <select name="searchCreatedBy" data-dropdown-css-class="w-200px" id="searchCreatedBy" class="form-select form-select-sm form-select-solid border selectTwo searchCreatedBy input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>
                        <th id="17" class="{{ $columns['updated_by']['visible'] ? '' : 'd-none' }}">
                            <select name="searchUpdatedBy" data-dropdown-css-class="w-200px" id="searchUpdatedBy" class="form-select form-select-sm form-select-solid border selectTwo searchUpdatedBy input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="18"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<form id="editForm" action="{{ route('leads.store') }}" method="post" enctype="multipart/form-data">
    <div class="modal fade modalRight w-99" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            @csrf
            <div class="modal-content bg-grey">
                <div class="modal-header py-3 bg-primary rounded-0">
                    <h5 class="modal-title text-white" id="editModalLabel">Lead Details</h5>
                    <div class="d-flex">
                        <span class="badge badge-dark mx-3">Ref No#: &nbsp <span id="modalRefNo"></span></span>
                    </div>
                </div>
                    <div class="modal-body">
                        <div class="page-loader flex-column bg-dark bg-opacity-25">
                            <span class="spinner-border text-primary" role="status"></span>
                            <span class="text-gray-800 fs-6 fw-semibold mt-5">Loading...</span>
                        </div>
                        <input type="hidden" id="editId" name="id">

                        <div class="row">
                            <div class="col-md-6">

                                <div class="card mt-4">
                                    <div class="card-body py-4 px-5">
                                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                                            <li class="nav-item">
                                                <a class="nav-link text-dark fw-bold active" data-bs-toggle="tab" href="#tabContactDetails"> <i class="fas fa-edit"></i> Contact Details</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link text-dark fw-bold" data-bs-toggle="tab" href="#tabPropertyDetails"> <i class="fa fa-home"></i> Property Details</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="myTabContent">
                                            <div class="tab-pane fade show active" id="tabContactDetails" role="tabpanel">
                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <label for="contact_search" class="form-label">Search Contact </label>
                                                        <div class="input-group input-group-sm flex-nowrap">
                                                            <input type="text" id="contact_search" class="form-control form-control-sm form-control-solid border" placeholder="Type refno, email, phone to search" autocomplete="off">
                                                        </div>
                                                        
                                                        <input type="hidden" id="contact_id" name="contact_id" autocomplete="off">
                                                    </div>
                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="contact_type" class="form-label">Contact Type</label>
                                                        <select name="contact_type" id="contact_type" class="form-select form-select-sm form-select-solid border selectTwoModal contact_type form-control-solid border">
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

                                                    <div class="col-lg-12 mb-3 form-group">
                                                        <label for="name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text p-0 border-0" id="basic-addon1">
                                                                <select name="title" id="title" class="form-select form-select-sm form-select-solid border title form-control-solid border" style="border-top-right-radius:0px; border-bottom-right-radius:0px;">
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
                                                            <input type="text" class="form-control form-control-sm form-control-solid border" id="name" name="name" placeholder="Contact Name" required>
                                                        </div>

                                                    </div>

                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm phone form-control-solid border" id="phone" name="phone" placeholder="Phone" required>
                                                    </div>

                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="email" class="form-label">Email</label>
                                                        <input type="email" class="form-control form-control-sm form-control-solid border" id="email" name="email" placeholder="Email">
                                                    </div>

                                                    <div class="col-md-12 mb-3">
                                                        <div class="separator separator-dashed my-3"></div>
                                                    </div>

                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="status_id" class="form-label">Lead Status <span class="required"></span></label>
                                                        <select name="status_id" id="status_id" class="form-select form-select-sm form-select-solid border selectTwoModal form-control-solid border" required>
                                                            <option value="">Select Status</option>
                                                            @if(count($statuses) > 0)
                                                                @foreach($statuses as $status)
                                                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="sub_status_id" class="form-label">Lead Sub Status</label>
                                                        <select name="sub_status_id" id="sub_status_id" class="form-select form-select-sm form-select-solid border selectTwoModal form-control-solid border">
                                                            <option value="">Select Sub Status</option>
                                                            @if(count($statuses) > 0)
                                                                @foreach($statuses as $status)
                                                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="lead_stage" class="form-label">Stage <span class="required"></span></label>
                                                        <select name="lead_stage" id="lead_stage" class="form-select form-select-sm form-select-solid border selectTwoModal form-control-solid border" required>
                                                            <option value="" data-name="">Select Stage</option>
                                                            <option value="Cold" data-name="Cold">Cold</option>
                                                            <option value="Warm" data-name="Warm">Warm</option>
                                                            <option value="Hot" data-name="Hot">Hot</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="agent_id" class="form-label">Lead Agent <span class="required"></span></label>
                                                        <select name="agent_id" id="agent_id" class="form-select form-select-sm form-select-solid border selectTwoModal form-control-solid border" required>
                                                            <option value="">Select Agent</option>
                                                            @if(count($users) > 0)
                                                                @foreach($users as $user)
                                                                    <option value="{{ $user->id }}" data-kt-select2-user="{{ $user->profileImage() }}">{{ $user->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="source_id" class="form-label">Source </label>
                                                        <select name="source_id" id="source_id" class="form-select form-select-sm form-select-solid border selectTwoModal source_id form-control-solid border">
                                                            
                                                            <option value="">Select Source</option>
                                                            @if(count($sources))
                                                                @foreach($sources as $source)
                                                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <div class="col-lg-6 mb-3 form-group">
                                                        <label for="sub_source_id" class="form-label">Sub Source </label>
                                                        <select name="sub_source_id" id="sub_source_id" class="form-select form-select-sm form-select-solid border selectTwoModal sub_source_id form-control-solid border">
                                                            <option value="">Select Sub Source</option>
                                                            
                                                        </select>
                                                    </div>

                                                    <div class="col-lg-12 mb-3 form-group">
                                                        <label for="campaign_id" class="form-label">Campaign </label>
                                                        <select name="campaign_id" id="campaign_id" class="form-select form-select-sm form-select-solid border selectTwoModal campaign_id form-control-solid border">
                                                            <option value="" data-name="">Select Campaign</option>
                                                            @if(count($campaigns))
                                                                @foreach($campaigns as $campaign)
                                                                    <option value="{{ $campaign->id }}" data-name="{{ $campaign->name }}">{{ $campaign->name }}</option>
                                                                @endforeach
                                                            @endif
                                                            
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="tabPropertyDetails" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-12 mb-3 form-group">
                                                        <label for="property_search" class="form-label">Search Property </label>
                                                        <div class="input-group input-group-sm flex-nowrap">
                                                            <input type="text" id="property_search" class="form-control form-control-sm form-control-solid border" placeholder="Type refno to search" autocomplete="off">
                                                        </div>
                                                        
                                                        <input type="hidden" id="listing_id" name="listing_id" autocomplete="off">
                                                        
                                                        <!-- Display selected owner details -->
                                                        <div class="mt-4 border rounded p-3 shadow-sm">
                                                            <i class="fa fa-id-card"></i> Refno#: <span class="mx-3" id="listing_refno"></span>
                                                            <br>
                                                            <div class="separator separator-dashed my-3"></div>

                                                            <i class="fa fa-home"></i> For: <span class="mx-3" id="listing_for"></span>
                                                            
                                                            <br>
                                                            <div class="separator separator-dashed my-3"></div>
                                                            <i class="fa fa-map-marker"></i> Community: <span class="mx-3" id="listing_community"></span>
                                                            <br>
                                                            <div class="separator separator-dashed my-3"></div>
                                                            <i class="fa fa-map-marker"></i> Sub Community: <span class="mx-3" id="listing_sub_community"></span>

                                                            <br>
                                                            <div class="separator separator-dashed my-3"></div>
                                                            <i class="fa fa-map-marker"></i> Tower: <span class="mx-3" id="listing_tower"></span>

                                                            <br>
                                                            <div class="separator separator-dashed my-3"></div>
                                                            <i class="fa fa-bed"></i> Beds: <span class="mx-3" id="listing_beds"></span>

                                                            <br>
                                                            <div class="separator separator-dashed my-3"></div>
                                                            <i class="fa fa-bath"></i> Baths: <span class="mx-3" id="listing_baths"></span>

                                                            <br>
                                                            <div class="separator separator-dashed my-3"></div>
                                                            Price: AED <span class="mx-3" id="listing_price"></span>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mt-4 h-100">
                                    <div class="card-header p-3 bg-dark d-flex align-items-between">
                                        <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i>Lead Activity</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">
                                        <div class="notesDiv">
                                            
                                            <!-- <div class="timeline-icon">
                                                <i class="ki-duotone ki-message-text-2 fs-2 text-gray-500">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span><span class="path3"></span>
                                                </i>
                                            </div> -->
                                            <div class="mt-1">
                                                <ol class="timeline">
                                                    <li class="timeline-item">
                                                        <span class="timeline-item-icon | avatar-icon">
                                                            <i class="avatar">
                                                                <img class="img-fluid" src="{{ auth()->user()->profileImage() }}" />
                                                            </i>
                                                        </span>
                                                        <div class="new-comment rounded">
                                                            <textarea class="form-control bg-grey note border-0 shadow-sm" id="note" name="note" rows="3" data-kt-element="input" placeholder="Type comment"></textarea>
                                                            <div class="d-flex flex-stack">
                                                                <div class="d-flex mt-3 w-100 py-0">
                                                                    <select name="eventType" id="eventType" class="btn btn-light-primary py-0 me-1 fs-8 text-start px-1">
                                                                        <option value="note">Note</option>
                                                                        <option value="reminder">Reminder</option>
                                                                        <option value="meeting">Meeting</option>
                                                                        <option value="viewing">Viewing</option>
                                                                    </select>
                                                                    <input type="text" name="event_date" disabled id="event_date" class="singleDateTime btn btn-light-primary py-0 me-1 fs-8 text-start px-1" style="height:25px !important;" placeholder="Select Date" />
                                                                </div>

                                                                <button type="button" class="btn btn-primary btn-xs noteAddBtn mt-3" disabled onclick="addNoteFunction()">Save</button>
                                                            </div>
                                                            <div class="separator mt-2"></div>
                                                        </div>
                                                    </li>
                                                    <div class="notesTable">
                                                        
                                                    </div>
                                                    
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="col-md-4">
                                <div class="card mt-4 h-100">
                                    <div class="card-header p-3 bg-dark d-flex align-items-between">
                                        <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i>Notes</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">
                                        <div class="notesDiv">
                                            <div class="mt-1">
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
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>

                        <div class="row mt-4">

                            <div class="col-md-12">
                                <div class="card mt-4">
                                    <div class="card-body py-4 px-5">
                                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                                            <li class="nav-item">
                                                <a class="nav-link text-dark fw-bold active" data-bs-toggle="tab" href="#tabClientRequirements"> <i class="fa fa-file-text"></i> Client Requirements</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link text-dark fw-bold" data-bs-toggle="tab" href="#tabMatchProperties"><i class="fas fa-tasks"></i> Match Properties</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link text-dark fw-bold" data-bs-toggle="tab" href="#tabDocuments"><i class="fas fa-edit"></i> Documents</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="myTabContent">
                                            <div class="tab-pane fade show active" id="tabClientRequirements" role="tabpanel">

                                                <div class="row">
                                                    <div class="col-md-4 mb-3 form-group">
                                                        <label for="community_id" class="form-label">Community</label>
                                                        <select name="community_id" id="community_id" class="form-select form-select-sm form-select-solid border selectTwoModal community_id form-control-solid border">
                                                            <option value="" data-name="">Select Community</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4 mb-3 form-group">
                                                        <label for="sub_community_id" class="form-label">Sub Community</label>
                                                        <select name="sub_community_id" id="sub_community_id" class="form-select form-select-sm form-select-solid border selectTwoModal sub_community_id form-control-solid border">
                                                            <option value="" data-name="">Select Sub Community</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4 mb-3 form-group">
                                                        <label for="tower_id" class="form-label">Tower</label>
                                                        <select name="tower_id" id="tower_id" class="form-select form-select-sm form-select-solid border selectTwoModal tower_id form-control-solid border">
                                                            <option value="" data-name="">Select Tower</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4 mb-3 form-group">
                                                        <label for="budget" class="form-label">Budget Range</label>

                                                        <div class="input-group input-group-sm flex-nowrap">
                                                            <span class="input-group-text p-0 border px-2">
                                                                AED
                                                            </span>
                                                            <input type="text" class="form-control form-control-sm form-control-solid border" id="budget" name="budget" placeholder="budget">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 mb-3 form-group">
                                                        <label for="beds" class="form-label">Bedrooms</label>
                                                        <div class="position-relative border rounded" id="bedroomDialer" data-kt-dialer="true" data-kt-dialer-min="-1" data-kt-dialer-max="10" data-kt-dialer-step="1" data-kt-dialer-suffix="" data-kt-dialer-decimals="0">
                                                            <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 start-0" data-kt-dialer-control="decrease">
                                                                <i class="ki-duotone ki-minus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </button>
                                                            
                                                            <input type="text" class="form-control form-control-solid form-control-sm border-0 ps-12" data-kt-dialer-control="input" placeholder="Beds" name="beds" id="beds"/>

                                                            <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 end-0" data-kt-dialer-control="increase">
                                                                <i class="ki-duotone ki-plus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4 mb-3 form-group">
                                                        <label for="move_in_date" class="form-label">Move-in date</label>
                                                        <input type="text" name="move_in_date" id="move_in_date" class="form-control form-control-sm singleDate form-control-solid border" placeholder="Select Date" />
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="cheque" class="form-label">Cheques</label>
                                                        <select name="cheque" id="cheque" class="form-select form-select-sm form-select-solid border selectTwoModal cheque">
                                                            <option value="">Select Cheque</option>
                                                            @for ($i = 1; $i <= 30; $i++)
                                                                <option value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="furnish" class="form-label">Furnished?</label>

                                                        <select name="furnish" id="furnish" class="form-select form-select-sm form-select-solid border selectTwoModal furnish">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Furnished" data-name="Furnished">Furnished</option>
                                                            <option value="Unfurnished" data-name="Unfurnished">Unfurnished</option>
                                                            <option value="Partly Furnished" data-name="Partly Furnished">Partly Furnished</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="upgraded" class="form-label">Upgraded/Normal</label>

                                                        <select name="upgraded" id="upgraded" class="form-select form-select-sm form-select-solid border selectTwoModal upgraded">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Upgraded" data-name="Upgraded">Upgraded</option>
                                                            <option value="Normal" data-name="Normal">Normal</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="landscape" class="form-label">Landscaped/Pool</label>

                                                        <select name="landscape" id="landscape" class="form-select form-select-sm form-select-solid border selectTwoModal landscape">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Landscape" data-name="Landscape">Landscape</option>
                                                            <option value="Pool" data-name="Pool">Pool</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="bathroom" class="form-label">Bathrooms</label>
                                                        <select name="bathroom" id="bathroom" class="form-select form-select-sm form-select-solid border selectTwoModal bathroom">
                                                            <option value="">Select Bathroom</option>
                                                            @for ($i = 1; $i <= 10; $i++)
                                                                <option value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="kitchen" class="form-label">Kitchen</label>

                                                        <select name="kitchen" id="kitchen" class="form-select form-select-sm form-select-solid border selectTwoModal kitchen">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Open" data-name="Open">Open</option>
                                                            <option value="Closed" data-name="Closed">Closed</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="schools" class="form-label">Kids (Schools)</label>

                                                        <select name="schools" id="schools" class="form-select form-select-sm form-select-solid border selectTwoModal schools">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Yes" data-name="Yes">Yes</option>
                                                            <option value="No" data-name="No">No</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="pets" class="form-label">Pets</label>

                                                        <select name="pets" id="pets" class="form-select form-select-sm form-select-solid border selectTwoModal pets">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Yes" data-name="Yes">Yes</option>
                                                            <option value="No" data-name="No">No</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="current_home" class="form-label">Current Home</label>

                                                        <select name="current_home" id="current_home" class="form-select form-select-sm form-select-solid border selectTwoModal current_home">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Renting" data-name="Renting">Renting</option>
                                                            <option value="Owner" data-name="Owner">Owner</option>
                                                            <option value="Hotel" data-name="Hotel">Hotel</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="parking" class="form-label">Parking</label>
                                                        <select name="parking" id="parking" class="form-select form-select-sm form-select-solid border selectTwoModal parking">
                                                            <option value="">Select Parking</option>
                                                            @for ($i = 1; $i <= 15; $i++)
                                                                <option value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="work_place" class="form-label">Workplace</label>
                                                        <input type="text" name="work_place" id="work_place" class="form-control form-control-sm form-control-solid border" placeholder="Workplace">
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="view" class="form-label">View</label>
                                                        <input type="text" name="view" id="view" class="form-control form-control-sm form-control-solid border" placeholder="View">
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="floor" class="form-label">Floor</label>
                                                        <input type="text" name="floor" id="floor" class="form-control form-control-sm form-control-solid border" placeholder="Floor">
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="bua" class="form-label">Built-Up Area</label>
                                                        <input type="text" name="bua" id="bua" class="form-control form-control-sm form-control-solid border" placeholder="Built-Up Area">
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="plot_size" class="form-label">Plot Size</label>
                                                        <input type="text" name="plot_size" id="plot_size" class="form-control form-control-sm form-control-solid border" placeholder="Plot Size">
                                                    </div>

                                                    <div class="col-md-2 mb-3 form-group">
                                                        <label for="new_to_dubai" class="form-label">New to Dubai?</label>

                                                        <select name="new_to_dubai" id="new_to_dubai" class="form-select form-select-sm form-select-solid border selectTwoModal new_to_dubai">
                                                            <option value="" data-name="">Select Option</option>
                                                            <option value="Yes" data-name="Yes">Yes</option>
                                                            <option value="No" data-name="No">No</option>
                                                        </select>
                                                        <p class="text-muted fs-8 mt-1">Requirements: Visa, EID, Cheque Book</p>
                                                    </div>
                                                    
                                                </div>

                                                
                                                
                                            </div>
                                            <div class="tab-pane fade" id="tabMatchProperties" role="tabpanel">
                                                
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
                            </div>

                            <div class="col-md-12 mt-4">
                                <div class="card">
                                    <div class="card-header p-4 bg-dark">
                                        <h3 class="text-white"> <i class="fas fa-clock text-white mr-1"></i> Change Log</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">
                                        <table class="table">
                                            <thead class="text-start bg-secondary text-dark fw-bold fs-7 text-uppercase gs-0">
                                                <th class="px-2 py-1 rounded-start">Notes</th>
                                                <th class="py-1">By</th>
                                                <th class="px-2 py-1 rounded-end">Date</th>
                                            </thead>
                                            <tbody class="changeLog">
                                            
                                            </tbody>
                                        </table>
                                        
                                    </div>
                                </div>
                            </div>

                            
                        </div>
                    </div>
                    <div class="modal-footer bg-white py-2">
                        <button type="submit" class="btn btn-primary btn-sm">Save Lead</button>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    </div>
            </div>
        </div>
    </div>
</form>

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
                        @if($columnName != 'refno2')
                            <div class="form-check col-md-4 mb-3">
                                <input type="checkbox" class="form-check-input toggle-column" id="toggle{{ ucfirst($columnName) }}" data-column="{{ $loop->index + 1 }}" {{ $visibility['visible'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="toggle{{ ucfirst($columnName) }}">{{ ucfirst($columnName) }}</label>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark btn-sm" id="resetButton">Reset</button>
                <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>

<script src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/fixedColumns.dataTables.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.0/js/dataTables.fixedColumns.js"></script>

@include('layouts.scripts')

<script>

    // Function to set default visibility rules and update local storage
    function resetColumnVisibility() {
        var defaultColumns = {
            'owner': false,
            'status': true,
            'refno': true,
            'for': true,
            'type': true,
            'unit_no': true,
            'community': true,
            'sub_community': true,
            'tower': true,
            'portal': true,
            'beds': true,
            'baths': true,
            'price': true,
            'bua': true,
            'rera_permit': true,
            'furnished': false,
            'category': false,
            'marketing_agent': false,
            'listing_agent': true,
            'created_by': false,
            'updated_by': false,
            'added_on': false,
            'last_update': true,
            'published_on': true,
            'project_status': false,
            'plot_area': false,
            'exclusive': false,
            'hot': false,
            'occupancy': false,
            'cheques': false,
            'developer': false,
            'actions': true
        };

        //localStorage.setItem('listingColumnVisibility', JSON.stringify(defaultColumns));
        //('#columnsModal').model('hide');
        $('#columnsModal').modal('hide');
        toastr.success('Columns reset successfull');
    }

    // Example of resetting on button click
    $('#resetButton').on('click', function () {
        resetColumnVisibility();
        // Optionally, you can call updateColumnVisibility() to apply these changes immediately
        // updateColumnVisibility();
    });

    $('.limitToDigits').on('input', function () {
        // Remove non-digit characters
        $(this).val($(this).val().replace(/\D/g, ''));
    });

    // function formatPriceInput() {
    //     var numericValue = $('#price').val().replace(/[^0-9]/g, '');
    //     var formattedValue = numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    //     $('#price').val(formattedValue);
    // }
    // formatPriceInput();
    // $('#price').on('input', formatPriceInput);

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

@php
    if (!empty($team_users) && !in_array(auth()->user()->id, $team_users)) {
        $team_users[] = auth()->user()->id;
    }
    $teamUserIds = !empty($team_users) ? $team_users : null;
@endphp
<script>

    function updateColumnVisibilitySetting(columnName, isVisible) {
        //var savedColumnVisibility = JSON.parse(localStorage.getItem('listingColumnVisibility')) || {};
       // savedColumnVisibility[columnName] = isVisible;
        //localStorage.setItem('listingColumnVisibility', JSON.stringify(savedColumnVisibility));
    }

    //console.log(JSON.parse(localStorage.getItem('listingColumnVisibility')));

    // columns visib start
    function updateColumnVisibility() {
        $('.toggle-column').each(function () {
            var columnName = $(this).data('column');
            var isVisible = $(this).prop('checked');
            dataTable.column(columnName).visible(isVisible);
            $('#filterHead th[id="' + columnName + '"]').toggleClass('d-none', !isVisible);

            updateColumnVisibilitySetting(columnName, isVisible ? true : false)
        });
    }

    // Event listener for changes in column visibility checkboxes
    $('.toggle-column').on('change', function () {
        updateColumnVisibility();
    });

    initializeDateRange('singleDate', null, 'single');
    initializeDateRange('singleDateTime', null, 'singleTime');

    initializeDateRangeTwo('searchDate', '{{ $firstDate }}');
    initializeDateRangeTwo('searchEnqDate', '{{ $firstDate }}');
    initializeDateRangeTwo('searchCreateDate', '{{ $firstDate }}');
    //initializeDateRange('searchPublishedDate', '{{ $firstDate }}');

    initializeDateRangeTwo('searchAssignedOn', '{{ $firstDate }}');
    initializeDateRangeTwo('searchAcceptedOn', '{{ $firstDate }}');
    var columnVisibility = {!! json_encode($columns) !!};

    //columns visib end

    var urlParams = new URLSearchParams(window.location.search);
    var lead_type = urlParams.get('type');
    var archived = urlParams.get('archived');
    var leads = urlParams.get('leads');
    const refnoParam = urlParams.get('refno');

    // var ajaxURL = '{{ route('leads.getLeads') }}';

    // if (archived) {
    //     ajaxURL += '?archivedd=yes';
    // }

    // if (leadType) {
    //     ajaxURL += '?type=' + leadType;
    // }

    // if (lead_type) {
    //     ajaxURL += (archived ? '&' : '?') + 'type=' + lead_type;
    // }

    var ajaxURL = '{{ route('leads.getLeads') }}';
    var queryParams = [];

    if (archived) {
        queryParams.push('archived=yes');
    }

    // if (leads) {
    //     queryParams.push('leads=' + leads);
    // }

    if (lead_type) {
        queryParams.push('type=' + lead_type);
    }

    if (queryParams.length > 0) {
        ajaxURL += '?' + queryParams.join('&');
    }

    var userRole = '{{ Auth::user()->getRoleNames()->first() }}';
    var loggedInUserId = '{{ Auth::user()->id }}';
    var is_team_leader = '{{ Auth::user()->is_teamleader }}';
    var teamUserIds = @json($teamUserIds ?? null);

    var dataTable = new DataTable('#dataTable', {
        select: {
            style: 'multi',
            selector: 'td:first-child input[type="checkbox"]',
            info: false,
            search: false,
        },
        fixedColumns: {
            leftColumns: 1,  // Number of columns to be fixed on the left
            rightColumns: 1  // Number of columns to be fixed on the right
        },

        //responsive: true,
        serverSide: true,
        processing: true,
        paging: true,
        //order: [[2, 'asc'], [3, 'asc']],
        ajax: {
            url: ajaxURL,
            type: 'POST', 
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function (d) {
                if($('.searchDate').val() !== ''){
                    d.startDate = $('.searchDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    d.endDate = $('.searchDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    d.startDate = null;
                    d.endDate = null;
                }

                if($('.searchCreateDate').val() !== ''){
                    d.startCreatedDate = $('.searchCreateDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    d.endCreatedDate = $('.searchCreateDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    d.startCreatedDate = null;
                    d.endCreatedDate = null;
                }

                if($('.searchEnqDate').val() !== ''){
                    d.startEnqDate = $('.searchEnqDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    d.endEnqDate = $('.searchEnqDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    d.startEnqDate = null;
                    d.endEnqDate = null;
                }

                if($('.searchAssignedOn').val() !== ''){
                    d.startAssignedDate = $('.searchAssignedOn').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    d.endAssignedDate = $('.searchAssignedOn').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    d.startAssignedDate = null;
                    d.endAssignedDate = null;
                }

                if($('.searchAcceptedOn').val() !== ''){
                    d.startAcceptedDate = $('.searchAcceptedOn').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    d.endAcceptedDate = $('.searchAcceptedOn').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    d.startAcceptedDate = null;
                    d.endAcceptedDate = null;
                }

                d.refno = $('.searchRefno').val();

                d.status = $('.searchStatus :selected').text() == 'All' ? '' : $('.searchStatus :selected').text();
                d.sub_status = $('.searchSubStatus :selected').text() == 'All' ? '' : $('.searchSubStatus :selected').text();

                d.stage = $('.searchStage :selected').text() == 'All' ? '' : $('.searchStage :selected').text();
                d.client_details = $('.searchClientDetails').val();
                d.property = $('.searchPropertyDetails').val();
                
                d.campaign = $('.searchCampaign :selected').text() == 'All' ? '' : $('.searchCampaign :selected').text();

                d.source = $('.searchSource :selected').text() == 'All' ? '' : $('.searchSource :selected').text();
                d.sub_source = $('.searchSubSource :selected').text() == 'All' ? '' : $('.searchSubSource :selected').text();
                
                d.lead_agent = $('.searchLeadAgent :selected').text() == 'All' ? '' : $('.searchLeadAgent :selected').text();
                d.created_by = $('.searchCreatedBy :selected').text() == 'All' ? '' : $('.searchCreatedBy :selected').text();
                d.updated_by = $('.searchUpdatedBy :selected').text() == 'All' ? '' : $('.searchUpdatedBy :selected').text();
                
            },
            // dataSrc: 'leads',
        },
        rowCallback: function(row, data) {
            console.log(data);
            if (data.deleted_at !== null) {
                $(row).addClass('bg-light-danger');
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    // Checkbox column
                    //return '<div class="text-center px-4"><input type="checkbox" class="item-checkbox" value="' + row.id + '"></div>';
                    return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input type="checkbox" class="item-checkbox form-check-input" value="' + row.id + '"></div>';
                },
                orderable: false,
                searchable: false,
            },
            
            {
                data: null,
                render: function (data, type, row) {
                    var isDeleted = row.deleted_at !== null;

                    return '<a class="" href="" type="button" ' +
                        (isDeleted ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#editModal" data-agentid="' + row.agent_id + '" data-action="edit" data-id="' + data.id + '"') +
                        '>'+ data.refno +'</button>';
                },
                visible: columnVisibility['refno']['visible'],
            },

            {
                data: null,
                render: function (data, type, row) {
                    // Checkbox column
                    var statusName = row.status ? row.status.name : null;
                    if(statusName != null){
                        if(row.status.badge != null){
                            return '<span class="badge badge-primary" style="background: '+ row.status.badge +';">' + statusName + '</span>';
                        }
                        else{
                            return statusName;
                        }
                    }
                },
                visible: columnVisibility['status']['visible'],
            },

            {
                data: null,
                render: function(data, type, row) {
                    var statusName = row.sub_status ? row.sub_status.name : null;
                    if(statusName != null){
                        if(row.sub_status.badge != null){
                            return '<span class="badge badge-primary" style="background: '+ row.sub_status.badge +';">' + statusName + '</span>';
                        }
                        else{
                            return statusName;
                        }
                    }
                    return null;
                    
                },
                visible: columnVisibility['sub_status']['visible'],
            },
            
            {
                data: 'lead_stage',
                render: function(data, type, row) {
                    return data;
                },
                visible: columnVisibility['stage']['visible'],
            },

            {
                data: 'updated_at',
                render: function(data, type, row) {
                    return moment.utc(data).fromNow();
                },
                visible: columnVisibility['last_update']['visible'],
            },

            {
                data: 'enquiry_date',
                render: function(data, type, row) {
                    return data ? moment.utc(data).fromNow() : '';
                },
                visible: columnVisibility['enquiry_date']['visible'],
            },

            {
                data: 'created_at',
                render: function(data, type, row) {
                    return moment.utc(data).fromNow();
                },
                visible: columnVisibility['added_on']['visible'],
            },

            {
                data: null,
                render: function(data, type, row) {
                    if (row.contact) {
                        // Initialize the content string
                        let content = '<p class="d-flex"> <i class="fa fa-user mr-1 fs-8"></i>' + row.contact.name + ' </p>';

                        // Check if email exists
                        if (row.contact.email) {
                            content += '<p><a href="mailto:' + row.contact.email + '" target="_blank" class="text-primary"><i class="fa fa-envelope fs-8 d-inline mr-1"></i>' + row.contact.email + '</a></p>';
                        }

                        // Check if phone exists
                        if (row.contact.phone) {
                            content += '<span class="d-flex"><a href="tel:' + row.contact.phone + '" target="_blank"><i class="fa fa-phone mr-1 text-success"></i></a> <a target="_blank" href="https://wa.me/' + row.contact.phone + '"><i class="fa-brands fa-whatsapp text-success mr-1 fs-5"></i></a><a target="_blank" class="text-primary" href="tel:' + row.contact.phone + '">' + row.contact.phone + '</a></span>';
                        }

                        return content;
                    } else {
                        return null;
                    }
                },
                visible: columnVisibility['client_details']['visible'],
            },

            {
                data: null,
                render: function(data, type, row) {
                    //console.log(row);
                    return '<span class="fw-bolder">RefNo#:</span> ' + (row.property ? row.property.refno : '') + '<br>' +
                            'Budget: ' + (row.lead_details && row.lead_details.budget != null ? row.lead_details.budget : '');
                },
                visible: columnVisibility['property_details']['visible'],
            },

            {
                data: null,
                render: function(data, type, row) {
                    return row.campaign ? row.campaign.name : null;
                },
                visible: columnVisibility['campaign']['visible'],
            },
            
            {
                data: null,
                render: function (data, type, row) {
                    // Check if marketing_agent data exists
                    if (row.lead_agent) {
                        // Customize the content as needed
                        return '<div class="d-flex align-items-center">' +
                            //'<div class=""><img src="' + row.lead_agent_image + '" alt="' + row.lead_agent.name + '" class="rounded-circle w-25px h-25px"> </div>' +
                            '<div class="ms-3">' +
                            '<div class="fw-bold fs-8">' + row.lead_agent.name + '</div>' +
                            '</div>' +
                            '</div>';
                    } else {
                        // If marketing_agent doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['lead_agent']['visible'],
            },
            {
                data: 'assigned_date',
                render: function(data, type, row) {
                    return data ? moment.utc(data).fromNow() : '<span class="badge badge-primary">Unassigned</span>';
                },
                visible: columnVisibility['assigned_on']['visible'],
            },
            {
                data: null,
                render: function(data, type, row) {
                    if(data.assigned_date != null && data.accepted_date == null){
                        return '<button class="btn btn-success fs-9 px-1 py-1 btn-flex" style="border-radius:2px;" id="acceptLead" type="button" data-id="'+data.id+'"><i class="ki-duotone ki-plus fs-7 p-0"></i> Accept </button>';
                    }
                    else if(data.accepted_date != null){
                        return data.accepted_date ? moment.utc(data.accepted_date).fromNow() : '';
                    }
                    else{
                        return 'Not ready to accept.';
                    }
                },
                visible: columnVisibility['accepted_on']['visible'],
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.source ? row.source.name : null;
                },
                visible: columnVisibility['source']['visible'],
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.sub_source ? row.sub_source.name : null;
                },
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
                data: 'id',
                render: function (data, type, row) {
                    var isDeleted = row.deleted_at !== null;

                    return '<div class="text-end">' +
                        '<button class="btn btn-sm btn-icon mr-1 btn-primary btn-active-primary" ' +
                        (isDeleted ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#editModal" data-action="edit" data-id="' + data + '"') +
                        '><i class="fa fa-pencil"></i></button>' +
                    '</div>';
                },

                visible: columnVisibility['actions']['visible'],
            }
        ],
        initComplete: function () {
            
            var api = this.api();

            // Your existing code to handle refnoParam
            if (refnoParam) {
                $.ajax({
                    url: '{{ route('leads.searchRefno') }}',
                    method: 'POST',
                    data: {
                        refno: refnoParam,
                        length: api.page.len(),
                    },
                    success: function (response) {
                        if (response && response.record) {
                            if (userRole != 'Super Admin' && is_team_leader == true) {
                                if (teamUserIds && !teamUserIds.includes(response.record.agent_id)) {
                                    Swal.fire({
                                        text: "You are not allowed to update this lead.",
                                        icon: "error",
                                        showCancelButton: false,
                                        confirmButtonText: "Ok",
                                        confirmButtonColor: "#DF405C",
                                    });
                                    return false;
                                }
                            }

                            var lead_update_perm = $('#lead_update_perm').val();
                            if(lead_update_perm == 'false'){
                                Swal.fire({
                                    title: "Permission Denied",
                                    text: "You don't have the permission to update any lead.",
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

                            //console.log('page number: ' + response.pageNumber);
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

            $('.searchRefno').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchStatus').on('change', function () {
                api.clear().draw();
            });

            $('.searchSubStatus').on('change', function () {
                api.clear().draw();
            });

            $('.searchStage').on('change', function () {
                api.clear().draw();
            });

            $('.searchClientDetails').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchPropertyDetails').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchCampaign').on('change', function () {
                api.clear().draw();
            });

            $('.searchSource').on('change', function () {
                api.clear().draw();
            });

            $('.searchSubSource').on('change', function () {
                api.clear().draw();
            });

            $('.searchLeadAgent').on('change', function () {
                api.clear().draw();
            });

            $('.searchCreatedBy').on('change', function () {
                api.clear().draw();
            });

            $('.searchUpdatedBy').on('change', function () {
                api.clear().draw();
            });

            $('.searchDate').on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel == 'Reset') {
                    $(this).val('');
                }
                api.clear().draw();
            });
            $('.searchDate').on('cancel.daterangepicker', function (ev, picker) {
                api.clear().draw();
            });

            $('.searchCreateDate').on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel == 'Reset') {
                    $(this).val('');
                }
                api.clear().draw();
            });
            $('.searchCreateDate').on('cancel.daterangepicker', function (ev, picker) {
                api.clear().draw();
            });

            $('.searchEnqDate').on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel == 'Reset') {
                    $(this).val('');
                }
                api.clear().draw();
            });
            $('.searchEnqDate').on('cancel.daterangepicker', function (ev, picker) {
                api.clear().draw();
            });

            $('.searchAssignedOn').on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel == 'Reset') {
                    $(this).val('');
                }
                api.clear().draw();
            });
            $('.searchAssignedOn').on('cancel.daterangepicker', function (ev, picker) {
                api.clear().draw();
            });

            $('.searchAcceptedOn').on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel == 'Reset') {
                    $(this).val('');
                }
                api.clear().draw();
            });
            $('.searchAcceptedOn').on('cancel.daterangepicker', function (ev, picker) {
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
        // alert('done');
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
        // var agentId = button.data('agentid');

        // if (userRole != 'Super Admin' && is_team_leader == true) {
        //     if (teamUserIds && !teamUserIds.includes(agentId)) {
        //         Swal.fire({
        //             text: "You are not allowed to update this lead.",
        //             icon: "error",
        //             showCancelButton: false,
        //             confirmButtonText: "Ok",
        //             confirmButtonColor: "#DF405C",
        //         });
        //         return false;
        //     }
        // }

        var lead_update_perm = $('#lead_update_perm').val();
        if(lead_update_perm == 'false'){
            Swal.fire({
                title: "Permission Denied",
                text: "You don't have the permission to update any lead.",
                icon: "error",
                showCancelButton: false,
                confirmButtonText: "Ok",
                confirmButtonColor: "#DF405C",
            });
            return false;
        }

        handleModalShown(itemId, action);
    });

    $('#editForm').submit(function(e) {
        e.preventDefault();
        var submitButton = $(this).find('button[type=submit]');

        var leadAgentValue = $(this).find('#agent_id').val();
        if (userRole != 'Super Admin' && loggedInUserId != leadAgentValue) {
            Swal.fire({
                text: "You are not allowed to update this lead.",
                icon: "error",
                showCancelButton: false,
                confirmButtonText: "Ok",
                confirmButtonColor: "#DF405C",
            });
            return false;
        }

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
                //console.log(data);
                submitButton.prop('disabled', false).html('Save Lead');

                if ('error' in data) {
                    // Handle the error, you might want to show an alert or log it
                    console.error('Update failed:', data.error);
                    
                    Swal.fire({
                        title: 'Error...',
                        html: data.error,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                    });
                } else {
                    reloadDataTable();
                
                    if (myDropzone) {
                        myDropzone.removeAllFiles();
                    }

                    $('#editModal').modal('hide');

                    if (data.message) {
                        toastr.success(data.message);
                    }
                }
                
            },
            error: function(xhr, status, error) {
                submitButton.prop('disabled', false).html('Save Lead');
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
                    Swal.fire({
                        title: 'Error...',
                        html: xhr.responseText,
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                    });
                    console.error(xhr.responseText);
                }
            }
        });
    });

    function confirmDelete(deleteUrl) {
        Swal.fire({
            text: "Are you sure you want to delete this Lead?",
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

    $('#bulkDeleteBtn').on('click', function (e) {
        e.preventDefault();

        //disabled temporary
        return;

        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            Swal.fire({
                text: "Are you sure you want to archive these leads? Write the reason below.",
                icon: "warning",
                input: 'text',
                showCancelButton: true,
                confirmButtonText: "Yes, do it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    var reason = result.value;
                    // Perform AJAX request for bulk action
                    performBulkAction('{{ route('leads.bulkDelete') }}', { item_ids: selectedItems, reason: reason });     
                }
            });
        }
    });

    function performBulkActionAssign(url, data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response) {
                    //console.log(response);
                    resolve(response); // Resolve the promise with the response data
                    reloadDataTable();
                },
                error: function (xhr, status, error) {
                    console.log(error);
                    console.error(xhr.responseText);  // Log the response text to the console
                    reject(error); // Reject the promise with the error message
                }
            });
        });
    }

    $('#bulkAssign').on('click', function (e) {
        e.preventDefault();
        var selectedItems;
        var selectedItemsRefno;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        selectedItemsRefno = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.refno;
        });
        if (selectedItems.length > 0) {
            Swal.fire({
                title: 'Please select the agent below to assign the selected leads.',
                html:
                    '<select id="agentForm" class="form-select form-select-sm form-select-solid border mb-3" placeholder="Select Agent"><option value="">Select Agent</option</select>' +
                    '<p class="mt-3 mb-1 fw-bold text-start">Write the reason below if applicable:</p>' +
                    '<input type="text" id="reasonForm" class="form-control form-control-sm mb-2" placeholder="Enter the reason">' +
                    '<p class="mt-3 mb-1 fw-bold text-start">Selected Leads:</p>' +
                    '<div class="d-flex w-100 text-gray-600" id="selectedItemsList"></div>',
                showCancelButton: true,
                confirmButtonText: "Assign",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#589CF0",
                cancelButtonColor: "#6c757d",
                width: '40%',
                heightAuto: false,
                customClass: {
                    popup: 'sendEmailSwal'
                },
                preConfirm: function () {
                    const agentValue = $('#agentForm').val();  // Use jQuery to get the Select2 value
                    //console.log(agentValue);

                    if (!agentValue) {
                        Swal.showValidationMessage('Agent is required');
                    } else {
                        return {
                            reason: $('#reasonForm').val(),  // Use jQuery to get the value of another element
                            agent: agentValue
                        };
                    }
                },
                didOpen: function () {

                    var selectElement = $('#agentForm').select2({
                        dropdownParent: $(".swal2-container"),
                        placeholder: 'Select Agent',
                        allowClear: true
                    });
                    
                    selectElement.empty();
                    selectElement.html('<option value="">Select Agent</option>');

                    $.ajax({
                        url: '{{ route('users.getList') }}',
                        type: 'GET',
                        success: function(data) {
                            var users = data.users;

                            $.each(users, function(index, user) {
                            var option = new Option(user.name, user.id, false, false);
                            $(option).attr('data-name', user.name);
                            selectElement.append(option);
                        });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching users:', error);
                            reject(error);
                        }
                    });
                }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading text and spinner in the popup
                        Swal.fire({
                            title: 'Processing...',
                            html: 'Assigning the leads. Please wait...',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            backdrop: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showLoaderOnConfirm: true,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        performBulkActionAssign('{{ route('leads.bulkAssign') }}', { item_ids: selectedItems, formValues: result.value }).then((response) => {
                            // Check the response and update the popup accordingly
                            //console.log(response);
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success',
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.error,
                                    icon: 'error',
                                });
                            }
                        })
                    }
                });

                // Update the list of selected items in the popup
                var selectedItemsList = document.getElementById('selectedItemsList');
                selectedItemsList.innerHTML = selectedItemsRefno.map(function(refno) {
                return '<button type="button" class="btn btn-secondary btn-sm"><span class="ki-duotone ki-home me-2"></span>' + refno + '</button>';
            }).join('');
        }
    });

    // $('.bulkStatusChangeBtn').on('click', function (e) {
    //     e.preventDefault();
    //     var selectedItems;
    //     var selectedItemsRefno;
    //     // In DataTable view
    //     selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
    //         return data.id;
    //     });

    //     selectedItemsRefno = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
    //         return data.refno;
    //     });

    //     if (selectedItems.length > 0) {
    //         Swal.fire({
    //             text: "Are you sure you want to change the status of these leads? Write the reason below.",
    //             icon: "warning",

    //             html:
    //                 //'<select id="statusSwal" class="form-control form-control-sm mb-3" placeholder="Select Status"><option value="">Select Status</option</select>' +
    //                 '<select id="statusSwal" class="form-control form-control-sm mb-3" placeholder="Select Status"><option value="">Select Status</option></select>' +
    //                 '<select id="subStatusSwal" class="form-control form-control-sm mb-3" placeholder="Select Sub Status"><option value="">Select Sub Status</option></select>' +
    //                 '<p class="mt-3 mb-1 fw-bold text-start">Write the reason below if applicable:</p>' +
    //                 '<input type="text" id="reasonForm" class="form-control form-control-sm mb-2" placeholder="Enter the reason">' +
    //                 '<p class="mt-3 mb-1 fw-bold text-start">Selected Leads:</p>' +
    //                 '<div class="d-flex w-100 text-gray-600" id="selectedItemsList"></div>',
                
    //             showCancelButton: true,
    //             confirmButtonText: "Yes, do it!",
    //             cancelButtonText: "Cancel",
    //             confirmButtonColor: "#dc3545",
    //             cancelButtonColor: "#6c757d",
    //             width: '40%',
    //             heightAuto: false,
    //             customClass: {
    //                 popup: 'sendEmailSwal'
    //             },
    //             preConfirm: function () {
    //                 const statusVal = $('#statusSwal').val();
    //                 const subStatusVal = $('#subStatusSwal').val();
    //                 //console.log(agentValue);

    //                 if (!statusVal) {
    //                     Swal.showValidationMessage('Status is required');
    //                 } else {
    //                     return {
    //                         reason: $('#reasonForm').val(),  // Use jQuery to get the value of another element
    //                         status_id: statusVal,
    //                         sub_status_id: subStatusVal
    //                     };
    //                 }
    //             },
    //             didOpen: function () {

    //                 var selectStatus = $('#statusSwal');
    //                 var selectSubStatus = $('#subStatusSwal');

    //                 selectStatus.select2({
    //                     dropdownParent: $(".swal2-container"),
    //                     placeholder: 'Select Status',
    //                     allowClear: true
    //                 });

    //                 selectSubStatus.select2({
    //                     dropdownParent: $(".swal2-container"),
    //                     placeholder: 'Select Sub Status',
    //                     allowClear: true
    //                 });

    //                 // Populate status select element
    //                 $.ajax({
    //                     url: '{{ route('crmStatuses.getLeadStatuses') }}',
    //                     type: 'GET',
    //                     success: function(data) {
    //                         var statuses = data.statuses;

    //                         if (!statuses || statuses.length === 0) {
    //                             console.error('No Statuses found.');
    //                             return;
    //                         }

    //                         $.each(statuses, function(index, status) {
    //                             var option = new Option(status.name, status.id, false, false);
    //                             $(option).attr('data-name', status.name);
    //                             selectStatus.append(option);
    //                         });

    //                         // Trigger change event to populate sub-status select element based on default status
    //                         selectStatus.trigger('change');
    //                     },
    //                     error: function(xhr, status, error) {
    //                         console.error('Error fetching statuses:', error);
    //                     }
    //                 });

    //                 // Populate sub-status select element based on selected status
    //                 selectStatus.on('change', function () {
    //                     var selectedStatusId = $(this).val();

    //                     selectSubStatus.empty();
    //                     selectSubStatus.html('<option value="">Select Sub Status</option>');

    //                     // Fetch sub-statuses via AJAX
    //                     $.ajax({
    //                         url: '{{ route('crmSubStatuses.getList') }}',
    //                         type: 'GET',
    //                         data: { status_id: selectedStatusId },
    //                         success: function(data) {
    //                             var sub_statuses = data.sub_statuses;

    //                             if (!sub_statuses || sub_statuses.length === 0) {
    //                                 console.error('No Sub Status found for the selected status.');
    //                                 return;
    //                             }

    //                             $.each(sub_statuses, function(index, sub_status) {
    //                                 var option = new Option(sub_status.name, sub_status.id, false, false);
    //                                 $(option).attr('data-name', sub_status.name);
    //                                 selectSubStatus.append(option);
    //                             });
    //                         },
    //                         error: function(xhr, status, error) {
    //                             console.error('Error fetching sub statuses:', error);
    //                         }
    //                     });
    //                 });
    //             }
    //             }).then((result) => {
    //                 var reason = result.value;
                    
    //                 if (result.isConfirmed) {
    //                     // Show loading text and spinner in the popup
    //                     Swal.fire({
    //                         title: 'Processing...',
    //                         html: 'Assigning the leads. Please wait...',
    //                         allowEscapeKey: false,
    //                         allowOutsideClick: false,
    //                         backdrop: true,
    //                         allowOutsideClick: false,
    //                         allowEscapeKey: false,
    //                         showLoaderOnConfirm: true,
    //                         didOpen: () => {
    //                             Swal.showLoading();
    //                         }
    //                     });
    //                     performBulkAction('{{ route('leads.bulkStatusChange') }}', { item_ids: selectedItems, formValues: result.value}).then((response) => {
    //                     //performBulkActionAssign('{{ route('leads.bulkAssign') }}', { item_ids: selectedItems, formValues: result.value }).then((response) => {
    //                         // Check the response and update the popup accordingly
    //                         //console.log(response);
    //                         if (response.success) {
    //                             Swal.fire({
    //                                 title: 'Success',
    //                                 text: response.message,
    //                                 icon: 'success',
    //                             });
    //                         } else {
    //                             Swal.fire({
    //                                 title: 'Error',
    //                                 text: response.error,
    //                                 icon: 'error',
    //                             });
    //                         }
    //                     })
    //                 }
    //             });

    //                 // Update the list of selected items in the popup
    //                 var selectedItemsList = document.getElementById('selectedItemsList');
    //                 selectedItemsList.innerHTML = selectedItemsRefno.map(function(refno) {
    //                 return '<button type="button" class="btn btn-secondary btn-sm"><span class="ki-duotone ki-home me-2"></span>' + refno + '</button>';
    //         }).join('');
            
    //     }
    // });

    $('.bulkStatusChangeBtn').on('click', function (e) {
        e.preventDefault();
        var selectedItems;
        var selectedItemsRefno;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        selectedItemsRefno = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.refno;
        });

        if (selectedItems.length > 0) {
            Swal.fire({
                text: "Are you sure you want to change the status of these leads? Write the reason below.",
                icon: "warning",
                html:
                    '<select id="statusSwal" class="form-select form-select-sm form-select-solid border mb-3" placeholder="Select Status"><option value="">Select Status</option></select>' +
                    '<select id="subStatusSwal" class="form-select form-select-sm form-select-solid border mb-3" placeholder="Select Sub Status"><option value="">Select Sub Status</option></select>' +
                    '<p class="mt-3 mb-1 fw-bold text-start">Write the reason below if applicable:</p>' +
                    '<input type="text" id="reasonForm" class="form-control form-control-sm mb-2" placeholder="Enter the reason">' +
                    '<p class="mt-3 mb-1 fw-bold text-start">Selected Leads:</p>' +
                    '<div class="d-flex w-100 text-gray-600" id="selectedItemsList"></div>',
                
                showCancelButton: true,
                confirmButtonText: "Yes, do it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                width: '40%',
                heightAuto: false,
                customClass: {
                    popup: 'sendEmailSwal'
                },
                preConfirm: function () {
                    const statusVal = $('#statusSwal').val();
                    const subStatusVal = $('#subStatusSwal').val();

                    if (!statusVal) {
                        Swal.showValidationMessage('Status is required');
                    } else {
                        return {
                            reason: $('#reasonForm').val(),
                            status_id: statusVal,
                            sub_status_id: subStatusVal
                        };
                    }
                },
                didOpen: function () {
                    var selectStatus = $('#statusSwal');
                    var selectSubStatus = $('#subStatusSwal');

                    // Initialize select2 for both status and sub-status select elements
                    selectStatus.select2({
                        dropdownParent: $(".swal2-container"),
                        placeholder: 'Select Status',
                        allowClear: true
                    });

                    selectSubStatus.select2({
                        dropdownParent: $(".swal2-container"),
                        placeholder: 'Select Sub Status',
                        allowClear: true
                    });

                    // Populate status select element
                    $.ajax({
                        url: '{{ route('crmStatuses.getLeadStatuses') }}',
                        type: 'GET',
                        success: function(data) {
                            var statuses = data.statuses;

                            if (!statuses || statuses.length === 0) {
                                console.error('No Statuses found.');
                                return;
                            }

                            $.each(statuses, function(index, status) {
                                var option = new Option(status.name, status.id, false, false);
                                $(option).attr('data-name', status.name);
                                selectStatus.append(option);
                            });

                            // Trigger change event to populate sub-status select element based on default status
                            selectStatus.trigger('change');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching statuses:', error);
                        }
                    });

                    // Populate sub-status select element based on selected status
                    selectStatus.on('change', function () {
                        var selectedStatusId = $(this).val();

                        selectSubStatus.empty();
                        selectSubStatus.html('<option value="">Select Sub Status</option>');

                        // Fetch sub-statuses via AJAX
                        $.ajax({
                            url: '{{ route('crmSubStatuses.getList') }}',
                            type: 'GET',
                            data: { status_id: selectedStatusId },
                            success: function(data) {
                                var sub_statuses = data.sub_statuses;

                                if (!sub_statuses || sub_statuses.length === 0) {
                                    console.error('No Sub Status found for the selected status.');
                                    return;
                                }

                                $.each(sub_statuses, function(index, sub_status) {
                                    var option = new Option(sub_status.name, sub_status.id, false, false);
                                    $(option).attr('data-name', sub_status.name);
                                    selectSubStatus.append(option);
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching sub statuses:', error);
                            }
                        });
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Assigning the leads. Please wait...',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        backdrop: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        //showLoaderOnConfirm: true,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    performBulkAction('{{ route('leads.bulkStatusChange') }}', { item_ids: selectedItems, formValues: result.value}).then((response) => {
                        if (response.success) {
                            toastr.success(response.success);
                            reloadDataTable();
                            Swal.fire({
                                title: 'Success',
                                text: response.success,
                                icon: 'success',
                            });
                        } else {
                            toastr.success(response.success);
                            reloadDataTable();
                            Swal.fire({
                                title: 'Error',
                                text: response.error,
                                icon: 'error',
                            });
                        }
                    });
                }
            });

            // Update the list of selected items in the popup
            var selectedItemsList = document.getElementById('selectedItemsList');
            selectedItemsList.innerHTML = selectedItemsRefno.map(function(refno) {
                return '<button type="button" class="btn btn-secondary btn-sm"><span class="ki-duotone ki-home me-2"></span>' + refno + '</button>';
            }).join('');
        }
    });


    $('#bulkRestoreBtn').on('click', function (e) {
        e.preventDefault();
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            Swal.fire({
                text: "Are you sure you want to restore these leads?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, do it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform AJAX request for bulk action
                    performBulkAction('{{ route('leads.bulkRestore') }}', { item_ids: selectedItems });
                }
            });
        }
    });

    // Initial update on page load
    updateBulkActionButtons();

    // Event handler for checkbox change
    $('body').on('change', 'input[name="item_ids[]"]', function () {
        updateBulkActionButtons();
    });

    // function performBulkAction(url, data) {
    //     $.ajax({
    //         url: url,
    //         type: 'POST',
    //         data: data,
    //         headers: {
    //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //         },
    //         success: function (data) {
    //             if(data.message){
    //                 toastr.success(data.message);
    //                 reloadDataTable();
    //             }
    //             else{
    //                 toastr.error(data.error);
    //             }
    //         },
    //         error: function (xhr, status, error) {
    //             console.log(xhr.responseText);
    //             if (xhr.responseJSON && xhr.responseJSON.error) {
    //                 toastr.error(xhr.responseJSON.error, 'Error');
    //             } else if (xhr.responseJSON && xhr.responseJSON.errors) {
    //                 toastr.error(xhr.responseJSON.errors.join('<br>'), 'Validation Error');
    //             } else {
    //                 toastr.error(xhr.responseText, 'Error');
    //             }
    //         }
    //     });
    //     //console.log(data);
        
    // }

    function performBulkAction(url, data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
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
            if (id === '5') {
                if($(this).val() !== ''){
                    var startDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    var endDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                    
                    filterValues['startDate'] = startDate;
                    filterValues['endDate'] = endDate;
                }
                else{
                    filterValues['startDate'] = null;
                    filterValues['endDate'] = null;
                }
            }
            else if (id === '7') {

                if($(this).val() !== ''){
                    var startCreatedDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    var endCreatedDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                    
                    filterValues['startCreatedDate'] = startCreatedDate;
                    filterValues['endCreatedDate'] = endCreatedDate;
                }
                else{
                    filterValues['startCreatedDate'] = null;
                    filterValues['endCreatedDate'] = null;
                }
            }
            else if (id === '6') {
                if($(this).val() !== ''){
                    var startEnqDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    var endEnqDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                    
                    filterValues['startEnqDate'] = startEnqDate;
                    filterValues['endEnqDate'] = endEnqDate;
                }
                else{
                    filterValues['startEnqDate'] = null;
                    filterValues['endEnqDate'] = null;
                }
            }
            else if (id === '12') {
                if($(this).val() !== ''){
                    var startAssignedDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    var endAssignedDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                    
                    filterValues['startAssignedDate'] = startAssignedDate;
                    filterValues['endAssignedDate'] = endEnqDate;
                }
                else{
                    filterValues['startAssignedDate'] = null;
                    filterValues['endAssignedDate'] = null;
                }
            }
            else if (id === '13') {
                if($(this).val() !== ''){
                    var startAcceptedDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    var endAcceptedDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                    
                    filterValues['startAcceptedDate'] = startAcceptedDate;
                    filterValues['endAcceptedDate'] = endEnqDate;
                }
                else{
                    filterValues['startAcceptedDate'] = null;
                    filterValues['endAcceptedDate'] = null;
                }
            }
            else {
                switch(id) {
                    case '1':
                        filterValues['refno'] = value;
                        break;
                    case '2':
                        filterValues['status'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '3':
                        filterValues['sub_status'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '4':
                        filterValues['stage'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '8':
                        filterValues['client_details'] = value;
                        break;
                    case '9':
                        filterValues['property'] = value;
                        break;
                    case '10':
                        filterValues['campaign'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '11':
                        filterValues['lead_agent'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '14':
                        filterValues['source'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '15':
                        filterValues['sub_source'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '16':
                        filterValues['created_by'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '17':
                        filterValues['updated_by'] = $(this).text() == 'All' ? '' : $(this).text();
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
            url: '{{ route('leads.export') }}',
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

    function handleModalShown(itemId, action) {
        $('#editForm')[0].reset();

        $('.selectTwoModal').each(function () {
            $(this).val(null).trigger('change');
        });
        $('#listing_id').val(null).trigger('change');
        //$('.ownerSelectModal').val(null).trigger('change');

        $('#status').val($('#status option:contains("Prospect")').val()).trigger('change');
        
        var notesDiv = $('.notesTable');
        notesDiv.html('');
        $('#modalRefNo').text('');

        if (myDropzone) {
            myDropzone.removeAllFiles();
        }
        
        var changeLogDiv = $('.changeLog');
        $('.documents_edit').html('');
        $('.media_edit').html('');
        changeLogDiv.html('');

        var form = $('#editForm');
        var modalTitle = form.find('.modal-title');
        var modalAction = form.attr('action');
        
        if (action === 'create') {
            modalTitle.text('Create Lead');
            modalAction = '{{ route('leads.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Lead');
            modalAction = '{{ route('leads.update', ':itemId') }}'.replace(':itemId', itemId);
        }
        
        // Set ID in the hidden input
        $('#editId').val(itemId);

        form.attr('action', modalAction);
        
        // Fetch data via AJAX
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('leads.edit', ['lead' => ':itemId']) }}'.replace(':itemId', itemId),
                type: 'GET',
                success: function(data) {
                    var lead = data.lead;

                    $('#contact_search').prop('disabled', true);

                    $('#contact_id').val(lead.contact_id).trigger('change');
                    $('#listing_id').val(lead.listing_id).trigger('change');

                    if(lead.status_id && lead.status_id != null){
                        $('#status_id').val(lead.status_id).trigger('change');
                    }

                    if(lead.sub_status_id != null){
                        populateSubStatuses('sub_status_id', 'modal', lead.status_id, lead.sub_status_id);
                    }

                    // Basic Details
                    $('#modalRefNo').text(lead.refno);

                    $('#lead_stage').val(lead.lead_stage).trigger('change');
                    $('#agent_id').val(lead.agent_id).trigger('change');

                    $('#source_id').val(lead.source_id).trigger('change');

                    if(lead.sub_source_id != null){
                        populateSubSources('sub_source_id', 'modal', lead.source_id, lead.sub_source_id);
                    }
                    $('#campaign_id').val(lead.campaign_id).trigger('change');

                    if(lead.lead_details){
                        $('#budget').val(lead.lead_details.budget);
                        $('#beds').val(lead.lead_details.bedroom);
                        $('#move_in_date').val(lead.lead_details.move_in);

                        populateCommunities(7, 'community_id', lead.lead_details.community)
                        .then(function () {
                            return populateSubCommunities(7, 'sub_community_id', lead.lead_details.community, lead.lead_details.subcommunity);
                        })
                        .then(function () {
                            return populateTowers(7, 'tower_id', lead.lead_details.community, lead.lead_details.subcommunity, lead.lead_details.property ? lead.lead_details.property : 'two');
                        })
                        .catch(function(error) {
                            console.error('An error occurred:', error);
                        });

                        if(lead.lead_details.cheque != null){
                            $('#cheque').val(lead.lead_details.cheque).trigger('change');
                        }

                        if(lead.lead_details.furnish != null){
                            $('#furnish').val(lead.lead_details.furnish).trigger('change');
                        }

                        if(lead.lead_details.upgraded != null){
                            $('#upgraded').val(lead.lead_details.upgraded).trigger('change');
                        }

                        if(lead.lead_details.landscape != null){
                            $('#landscape').val(lead.lead_details.landscape).trigger('change');
                        }

                        if(lead.lead_details.bathroom != null){
                            $('#bathroom').val(lead.lead_details.bathroom).trigger('change');
                        }

                        if(lead.lead_details.kitchen != null){
                            $('#kitchen').val(lead.lead_details.kitchen).trigger('change');
                        }

                        if(lead.lead_details.schools != null){
                            $('#schools').val(lead.lead_details.schools).trigger('change');
                        }

                        if(lead.lead_details.pets != null){
                            $('#pets').val(lead.lead_details.pets).trigger('change');
                        }

                        if(lead.lead_details.current_home != null){
                            $('#current_home').val(lead.lead_details.current_home).trigger('change');
                        }

                        if(lead.lead_details.parking != null){
                            $('#parking').val(lead.lead_details.parking).trigger('change');
                        }

                        $('#work_place').val(lead.lead_details.work_place);
                        $('#view').val(lead.lead_details.view);
                        $('#floor').val(lead.lead_details.floor);
                        $('#bua').val(lead.lead_details.bua);
                        $('#plot_size').val(lead.lead_details.plot_size);


                        if(lead.lead_details.new_to_dubai != null){
                            $('#new_to_dubai').val(lead.lead_details.new_to_dubai).trigger('change');
                        }
                    }

                    // Display Documents
                    displayDocuments(lead.documents);
                    displayNotes(lead.notes);

                    // Populate change log

                    var activities = lead.activities;
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
                },
                error: function(error) {
                    Swal.fire({
                        title: "Error, contact support",
                        text: error,
                        icon: "warning",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#dc3545",
                    });
                }
            });
        }
        else{
            $('#contact_search').prop('disabled', false);
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

                if (note.created_by_user && note.created_by_user.photo !== null) {
                    user_photo = '<?= asset('public/storage') ?>/' + note.created_by_user.photo;
                } else {
                    user_photo = '<?= asset('assets/media/svg/avatars/blank-dark.svg') ?>';
                }

                var createdAtDate = new Date(note.created_at);
                //var createdAtDate = new Date(note.created_at);

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

                var scheduled = null;
                if(note.event_date && (note.event_date != '' || note.event_date != null)){

                    var eventDate = new Date(note.event_date);

                    var formattedDateEvent = eventDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    // Format time as "4:34 PM"
                    var formattedTimeEvent = eventDate.toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: 'numeric',
                        hour12: true
                    });

                    var timeAgoEvent = moment(eventDate).fromNow();

                    var formattedDateTimeEvent = formattedDateEvent + ' ' + formattedTimeEvent + ' (' + timeAgoEvent + ')';

                    scheduled = 'Scheduled on ' + formattedDateTimeEvent;
                }
                var eventIcon = '<i class="ki-duotone ki-calendar-8 fs-2 text-gray-500"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>';
                var noteIcon = '<i class="ki-duotone ki-message-text-2 fs-2 text-gray-500"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
                var defaultIcon = '<i class="ki-duotone ki-pencil fs-3 text-gray-500"><span class="path1"></span><span class="path2"></span></i>';

                var noteRow = $(
                    '<li class="timeline-item | extra-space noteRow" id="note_' + note.id + '">' +
                        '<span class="timeline-item-icon | filled-icon p-2">' +
                            // '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">' +
                            //     '<path fill="none" d="M0 0h24v24H0z" />' +
                            //     '<path fill="currentColor" d="M6.455 19L2 22.5V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6.455zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z" />' +
                            // '</svg>' +
                            //(note.type == 'note' ? noteIcon : eventIcon) +
                            (note.type == 'note' ? noteIcon : (note.type != null ? eventIcon : defaultIcon)) +
                        '</span>' +
                        '<div class="timeline-item-wrapper">' +
                            '<div class="fw-bold text-gray-500">'+ (note.type ? ucwords(note.type) : '') + (scheduled ? ' - <span class="fs-8">' + scheduled + '</span>' : '') + '</div>'+
                            '<div class="timeline-item-description">' +
                                '<i class="avatar | small">' +
                                    '<img class="img-fluid" src="'+user_photo+'" />' +
                                '</i>' +
                                '<span><h6 class="fw-bold text-dark">' + (note.created_by_user ? note.created_by_user.name : 'System') + '</h6> <h6 style="font-size:12px;" class="text-gray-500 fw-bold"> <time datetime="' + note.created_at + '">' + formattedDateTime + ' ('+ timeAgo +')</time></h6></span>' +
                            '</div>' +
                            '<div class="comment">' +
                                '<input type="hidden" class="note_types" name="note_types[]" value="' + note.type + '">' +
                                '<input type="hidden" class="note_dates" name="note_dates[]" value="' + note.event_date + '">' +
                                '<input type="hidden" class="note_created_at" name="note_created_at[]" value="' + note.created_at + '">' +
                                '<input type="hidden" class="note_updated_at" name="note_updated_at[]" value="' + note.updated_at + '">' +
                                '<textarea class="form-control noteText bg-grey border-0 fs-8" name="note_values[]" rows="' + note.note.split('\n').length + '" readonly>' + note.note + '</textarea>' +
                                '<button class="btn btn-xs btn-light-danger removeButton" type="button" onclick="removeNote(' + note.id + ')"><i class="fa fa-trash fs-9"></i></button>' +
                            '</div>' +
                        '</div>' +
                    '</li>'
                );

                notesDiv.append(noteRow);

                // Add double-click event to remove readonly
                noteRow.find('.noteText').dblclick(function () {
                    $(this).prop('readonly', false);
                    $(this).removeClass('border-0');
                    $(this).removeClass('bg-grey');
                });

                // Add blur event to add readonly
                noteRow.find('.noteText').blur(function () {
                    $(this).addClass('border-0');
                    $(this).addClass('bg-grey');
                    $(this).prop('readonly', true);
                });

                noteRow.find('.noteText').change(function () {
                    //var now = new Date();
                    //var formattedDateTime = now.toISOString().slice(0, 19).replace('T', ' ');
                    var currentDateTimeNow = '{{ Carbon::now()->format("Y-m-d H:i:s") }}';

                    $(this).closest('.comment').find('.note_updated_at').val(currentDateTimeNow);
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
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>

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

    $('#eventType').on('change', function () {
        if($(this).val() == 'note'){
            $('#event_date').val(null);
            $('#event_date').prop('disabled', true);
        }
        else{
            $('#event_date').prop('disabled', false);
        }
    });

    // Function to save a note
    function addNoteFunction() {
        var noteText = $('#note').val();
        var noteType = $('#eventType').val();
        var noteDate = $('#event_date').val();
        var notesTable = $('.notesTable');

        if(noteType != 'note' && noteDate == ''){
            Swal.fire({
                text: "Please select date for " + ucwords(noteType) + " before saving.",
                icon: "error",
                showCancelButton: false,
                confirmButtonText: "Ok",
                confirmButtonColor: "#DF405C",
            });
            return;
        }

        var scheduled = null;
        if(noteDate != ''){
            scheduled = 'Scheduled on ' + noteDate;
        }

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
            var carbonDateTime = '{{ $carbonDateTime }}';
            var timeAgo = moment(currentDate).fromNow();

            var eventIcon = '<i class="ki-duotone ki-calendar-8 fs-2 text-gray-500"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>';
            var noteIcon = '<i class="ki-duotone ki-message-text-2 fs-2 text-gray-500"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>';
            var defaultIcon = '<i class="ki-duotone ki-pencil fs-3 text-gray-500"><span class="path1"></span><span class="path2"></span></i>';

            var newRow = $(
                '<li class="timeline-item | extra-space noteRow" id="note_' + noteId + '">' +
                    '<span class="timeline-item-icon | filled-icon p-2">' +
                        // '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">' +
                        // '<path fill="none" d="M0 0h24v24H0z" />' +
                        // '<path fill="currentColor" d="M6.455 19L2 22.5V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6.455zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z" />' +
                        // '</svg>' +
                        //(noteType == 'note' ? noteIcon : eventIcon) +
                        (noteType == 'note' ? noteIcon : (noteType ? eventIcon : defaultIcon)) +
                    '</span>' +
                    '<div class="timeline-item-wrapper">' +
                        '<div class="fw-bold text-gray-500">'+ ucwords(noteType) + (scheduled ? ' - <span class="fs-8">' + scheduled + '</span>' : '') + '</div>'+
                        '<div class="timeline-item-description">' +
                            '<i class="avatar | small">' +
                                '<img class="img-fluid" src="' + userPhoto + '" />' +
                            '</i>' +
                            '<span><h6 class="fw-bold text-dark">' + userName + '</h6> <h6 style="font-size:12px;" class="text-gray-500 fw-bold"> <time datetime="formattedDate">'+formattedDateTime+' ('+ timeAgo +')</time></h6></span>' +
                        '</div>' +
                        '<div class="comment">' +
                            '<input type="hidden" class="note_types" name="note_types[]" value="' + noteType + '">' +
                            '<input type="hidden" class="note_dates" name="note_dates[]" value="' + noteDate + '">' +
                            '<input type="hidden" class="note_created_at" name="note_created_at[]" value="' + carbonDateTime + '">' +
                            '<input type="hidden" class="note_updated_at" name="note_updated_at[]" value="' + carbonDateTime + '">' +
                            '<textarea class="form-control noteText bg-grey border-0 fs-8" name="note_values[]" rows="' + noteText.split('\n').length + '" readonly>' + noteText + '</textarea>' +
                            '<button class="btn btn-xs btn-light-danger removeButton" type="button" onclick="removeNote(\'' + noteId + '\')"><i class="fa fa-trash fs-9"></i></button>' +
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
            $(this).removeClass('bg-grey');
        });

        // Blur event to add readonly and focus on interaction
        noteText.on('blur', function () {
            $(this).addClass('border-0');
            $(this).addClass('bg-grey');
            $(this).prop('readonly', true);
        });

        // Input event to update note_updated_at when the note is edited
        noteText.on('change', function () {
            var now = new Date();
            var formattedDateTime = now.toISOString().slice(0, 19).replace('T', ' ');
            $(this).closest('.comment').find('.note_updated_at').val(formattedDateTime);
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

    const optionFormatSelect = (item) => {
        if (!item.id) {
            return item.text;
        }

        var span = document.createElement('span');
        var template = '';

        template += '<div class="d-flex align-items-center">';
        template += '<div class="d-flex flex-column">'
        template += '<span class="fs-6 fw-bold lh-1">' + item.name + '</span>';
        
        // Access the data attributes directly from item
        if (item.email) {
            template += '<span class="text-dark fs-7 owner-email">' + item.email + '</span>';
        }
        
        if (item.phone) {
            template += '<span class="text-dark fs-7 owner-phone">' + item.phone + '</span>';
        }

        template += '</div>';
        template += '</div>';

        span.innerHTML = template;

        return $(span);
    }

    // Format options
    var optionFormatAgent = function(item) {
        if ( !item.id ) {
            return item.text;
        }

        var span = document.createElement('span');
        var imgUrl = item.element.getAttribute('data-kt-select2-user');
        var template = '';

        template += '<span class="d-flex"><img src="' + imgUrl + '" class="rounded-circle h-20px w-20px me-2" alt="image"/>';
        template += '<span>' + item.text + '</span></span>';

        span.innerHTML = template;

        return $(span);
    }





    // search property 

    function propertySearch(listingId = null){
        $('#property_search').typeahead({
            source: function(query, result) {
                $.ajax({
                    url: '{{ route('listings.getList') }}',
                    method: 'GET',
                    data: listingId !== null ? { id: listingId } : { q: query },
                    dataType: 'json',
                    success: function(data) {
                        result(data.results);

                        // If listingId is not null and there's only one result, select it
                        if (listingId !== null && data.results.length === 1) {
                            $('#property_search').typeahead('select', data.results[0]);
                        }
                    }
                });
            },
            displayText: function(item) {
                //return item.refno + ' - ' + item.email + ' - ' + item.phone;
                return item.refno + ' (' + item.external_refno + ')';
            },
            afterSelect: function(item) {
                // Set the selected owner_id to the hidden input
                $('#listing_id').val(item.id).trigger('change');
            },
            matcher: function(item) {
                // Customize the matching function if needed
                return true;
            }
        });
    }

    // Initial setup
    propertySearch();

    // Handle clearing the selection
    $('#property_search').on('input', function() {
        if (!$(this).val()) {
            // Clear the hidden input and details
            $('#listing_id').val('');
            $('#listing_refno').text('');
            $('#listing_for').text('');
            $('#listing_community').text('');
            $('#listing_sub_community').text('');

            $('#listing_tower').text('');
            $('#listing_beds').text('');
            $('#listing_baths').text('');
            $('#listing_price').text('');

            // You may clear more details as needed
        }
    });

    $('#listing_id').on('change', function() {
        //alert($(this).val());
        if ($(this).val()) { // Check if the value is not empty
            //ownerSearch($(this).val());

            $.ajax({
                url: '{{ route('listings.getList') }}',
                method: 'GET',
                data: { id: $(this).val() },
                dataType: 'json',
                success: function(data) {
                    //result(data.results);

                    // If ownerId is not null and there's only one result, select it
                    if (data.results.length === 1) {
                        //$('#owner_search').typeahead('select', data.results[0]);

                        //console.log(data.results[0]['community'].name);
                        $('#listing_refno').text(data.results[0]['refno'] + ' (' + data.results[0]['external_refno'] + ')');
                        $('#listing_for').text(ucwords(data.results[0]['property_for']));
                        $('#listing_community').text(data.results[0]['community'] ? data.results[0]['community'].name : '');
                        $('#listing_sub_community').text(data.results[0]['sub_community'] ? data.results[0]['sub_community'].name : '');
                        $('#listing_tower').text(data.results[0]['tower'] ? data.results[0]['tower'].name : '');
                        $('#listing_beds').text(data.results[0]['beds']);
                        $('#listing_baths').text(data.results[0]['baths']);

                        var price = data.results[0]['price'] ? addThousandSeparator(data.results[0]['price']) : '';
                        $('#listing_price').text(price);
                    }
                    else{
                        $('#listing_refno').text('');
                        $('#listing_for').text('');
                        $('#listing_community').text('');
                        $('#listing_sub_community').text('');
                        $('#listing_tower').text('');
                        $('#listing_beds').text('');
                        $('#listing_baths').text('');
                        $('#listing_price').text('');
                    }
                }
            });
        }
        else{
            $('#listing_refno').text('');
            $('#listing_for').text('');
            $('#listing_community').text('');
            $('#listing_sub_community').text('');
            $('#listing_tower').text('');
            $('#listing_beds').text('');
            $('#listing_baths').text('');
            $('#listing_price').text('');
        }
    });


    // search contact 

    function contactSearch(contactId = null){
        $('#contact_search').typeahead({
            source: function(query, result) {
                $.ajax({
                    url: '{{ route('contacts.getList') }}',
                    method: 'GET',
                    data: contactId !== null ? { id: contactId } : { q: query },
                    dataType: 'json',
                    success: function(data) {
                        result(data.results);

                        // If contactId is not null and there's only one result, select it
                        if (contactId !== null && data.results.length === 1) {
                            $('#contact_search').typeahead('select', data.results[0]);
                        }
                    }
                });
            },
            displayText: function(item) {
                //return item.refno + ' - ' + item.email + ' - ' + item.phone;
                return item.refno + ' (' + item.name + ' - ' + item.email + ' - +' + item.phone + ')';
            },
            afterSelect: function(item) {
                // Set the selected owner_id to the hidden input
                $('#contact_id').val(item.id).trigger('change');
            },
            matcher: function(item) {
                // Customize the matching function if needed
                return true;
            }
        });
    }

    // Initial setup
    contactSearch();

    // Handle clearing the selection
    $('#contact_search').on('input', function() {
        if (!$(this).val()) {
            // Clear the hidden input and details
            $('#contact_id').val('');
            $('#email').val('');
            $('#phone').val('');
            $('#name').val('');
            $('#contact_type').val('').trigger('change');
            $('#title').val('Mr').trigger('change');
        }
    });

    $('#contact_id').on('change', function() {
        if ($(this).val()) { // Check if the value is not empty
            //ownerSearch($(this).val());

            $.ajax({
                url: '{{ route('contacts.getList') }}',
                method: 'GET',
                data: { id: $(this).val() },
                dataType: 'json',
                success: function(data) {
                    //result(data.results);

                    // If ownerId is not null and there's only one result, select it
                    if (data.results.length === 1) {
                        //$('#owner_search').typeahead('select', data.results[0]);

                        //console.log(data.results[0]['community'].name);
                        //$('#email').text(data.results[0]['refno'] + ' (' + data.results[0]['external_refno'] + ')');
                        $('#email').val(data.results[0]['email']);
                        $('#phone').val(data.results[0]['phone']);

                        $('#name').val(data.results[0]['name']);
                        
                        if(data.results[0]['title'] != null){
                            $('#title').val(data.results[0]['title']).trigger('change');
                        }
                        $('#contact_type').val(data.results[0]['contact_type']).trigger('change');
                    }
                }
            });
        }
    });

    function addThousandSeparator(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function ucwords (str) {
        return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
            return $1.toUpperCase();
        });
    }

    var dialerElement = document.querySelector("#bedroomDialer");
    var dialerObject = new KTDialer(dialerElement, { /* options */ });
    dialerObject.on('kt.dialer.decreased', function(){
        // Get the current value of the dialer
        var currentValue = dialerObject.getValue();

        // Check if the current value is 0
        if (currentValue == '0') {
            // Replace the value with 'Studio'
            $('#beds').val('Studio');
        } else if (currentValue == '-1') {
            // Set the input to null
            $('#beds').val(null);
        }

        // Additional logic if needed
        //alert('Decreased!');
    });

    dialerObject.on('kt.dialer.increased', function(){
        // Get the current value of the dialer
        var currentValue = dialerObject.getValue();

        if (currentValue == '0') {
            $('#beds').val('Studio');
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

    function populateSubStatuses(idName, type, status_id, selectedId = null) {
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
            selectElement.html('<option value="" data-name="">Select Sub Status</option>');
        }

        // Fetch via AJAX
        $.ajax({
            url: '{{ route('crmSubStatuses.getList') }}',
            type: 'GET',
            data: { status_id: status_id},
            success: function(data) {
                console.log(type == 'search' ? 'id' : 'id');
                var sub_statuses = data.sub_statuses;

                if (!sub_statuses || sub_statuses.length === 0) {
                    console.error('No Sub Status found for the selected status.');
                    return;
                }

                // Populate communities select element
                if(selectedId == null){
                    $.each(sub_statuses, function(index, sub_status) {
                        // Only append options on condition (removed)
                        var option = new Option(sub_status.name, sub_status.id, false, false);
                        $(option).attr('data-name', sub_status.name);
                        selectElement.append(option);
                    });
                }

                // Select the item if item id exists
                if (selectedId) {
                    selectElement.val(selectedId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sub statuses:', error);
            }
        });
    }

    $(document).on('change', '#searchStatus', function() {
        var selectedId = $(this).val();
        populateSubStatuses('searchSubStatus', 'search', selectedId);
    });

    $(document).on('change', '#status_id', function() {
        var selectedId = $(this).val();
        populateSubStatuses('sub_status_id', 'modal', selectedId);
    });




    function populateCommunities(cityId, selector, selectedCommunityId = null) {
        return new Promise(function(resolve, reject) {
            var communitySelect = $('#' + selector);
            communitySelect.select2({
                dropdownParent: $("#editModal")
            });
            communitySelect.empty();
            
            if(selector == 'community_id'){
                communitySelect.html('<option value="" data-name="">Select Community</option>');
            }
            else{
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

    populateCommunities(7, 'community_id');

    $(document).on('change', '#community_id', function() {
        var selectedId = $(this).val();
        populateSubCommunities(7, 'sub_community_id', selectedId);
    });

    function populateSubCommunities(cityId, selector, community_id, selectedSubCommunityId = null) {
        return new Promise(function(resolve, reject) {
            var sub_communitySelect = $('#'+selector);
            sub_communitySelect.select2({
                dropdownParent: $("#editModal")
            });
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

    $(document).on('change', '#sub_community_id', function() {
        var selectedCommunituId = $('#community_id').val();
        var sub_community_id = $(this).val();
        populateTowers(7, 'tower_id', selectedCommunituId, sub_community_id);
    });

    function populateTowers(cityId, selector, community_id, sub_community_id, selected_id = null) {
        
        return new Promise(function(resolve, reject) {
            var selectElement = $('#'+selector);
            selectElement   .select2({
                dropdownParent: $("#editModal")
            });
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


    $(document).on('click', '#acceptLead', function() {
        var leadId = $(this).data('id');
        Swal.fire({
            text: "Please confirm before accepting the lead?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, accept it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#5CC361",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('leads.acceptAjax') }}',
                    method: 'POST',
                    data: {
                        lead_id: leadId,
                    },
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
    });

</script>

@endsection
