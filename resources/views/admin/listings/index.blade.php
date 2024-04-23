@extends('layouts.app')

@section('content')
@php
    use Carbon\Carbon;
    $carbonDateTime = Carbon::now()->format('Y-m-d H:i:s');
@endphp
@php
    $columns = [
        'owner' => ['index' => 1, 'visible' => false],
        'status' => ['index' => 2, 'visible' => true],
        'refno' => ['index' => 3, 'visible' => true],

        'for' => ['index' => 4, 'visible' => true],
        'type' => ['index' => 5, 'visible' => true],
        'unit_no' => ['index' => 6, 'visible' => true],

        'community' => ['index' => 7, 'visible' => true],
        'sub_community' => ['index' => 8, 'visible' => true],
        'tower' => ['index' => 9, 'visible' => true],
        'portal' => ['index' => 10, 'visible' => true],

        'beds' => ['index' => 11, 'visible' => true],
        'baths' => ['index' => 12, 'visible' => true],
        'price' => ['index' => 13, 'visible' => true],

        'bua' => ['index' => 14, 'visible' => true],
        'rera_permit' => ['index' => 15, 'visible' => true],
        'furnished' => ['index' => 16, 'visible' => false],
        'category' => ['index' => 17, 'visible' => false],
        
        'marketing_agent' => ['index' => 18, 'visible' => false],
        'listing_agent' => ['index' => 19, 'visible' => true],
        
        'created_by' => ['index' => 20, 'visible' => false],
        'updated_by' => ['index' => 21, 'visible' => false],
        'added_on' => ['index' => 22, 'visible' => false],
        'last_update' => ['index' => 23, 'visible' => true],
        'published_on' => ['index' => 24, 'visible' => true],
        'project_status' => ['index' => 25, 'visible' => false], 
        'plot_area' => ['index' => 26, 'visible' => false],
        
        'exclusive' => ['index' => 27, 'visible' => false],
        'hot' => ['index' => 28, 'visible' => false],
        'occupancy' => ['index' => 29, 'visible' => false],
        'cheques' => ['index' => 30, 'visible' => false],
        
        'developer' => ['index' => 31, 'visible' => false],
        
        'actions' => ['index' => 32, 'visible' => true],
        'refno2' => ['index' => 33, 'visible' => false],
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

<input type="hidden" name="listing_update_perm" id="listing_update_perm" value="{{ auth()->user()->can('listing_update') ? 'true' : 'false' }}">

<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            @if(isset($_GET['archived']))
                Archived Listings
            @else
                {{ isset($_GET['status']) && $_GET['status'] != null ? ucfirst($_GET['status']) : 'Active' }} Listings {{ isset($_GET['for']) && $_GET['for'] != null ? ' for '.ucfirst($_GET['for']) : '' }}
            @endif
        </h2>

        <div class="card-toolbar">
            <div>
                <div class="symbol-group symbol-hover flex-nowrap mx-3 gap-4">
                    @if(count($portals) > 0)
                        @foreach($portals as $portal)
                            <div class="symbol w-100px bg-grey symbol-rounded py-2 px-3" data-bs-toggle="tooltip" aria-label="{{ $portal->name }}" data-bs-original-title="{{ $portal->name }}" title="{{ $portal->name }}" data-kt-initialized="1">
                                <img alt="{{$portal->name}}" class="w-20px h-20px" src="{{ $portal->logoImage() }}">
                                <span class="fw-semibold color-dark fs-7 mx-2 portalListingsCount">0</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="bulkActions d-none mr-1">
                <div>
                    <button type="button" class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 {{ request()->routeIs('listings.index') ? 'btn-primary' : '' }} px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate" data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start">
                        <span class="d-none d-md-inline"> <i class="fa fa-tasks" style="margin-right:10px;"></i>Bulk Actions</span> 
                        <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i> 
                    </button>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px" data-kt-menu="true">
                        @if(isset($_GET['archived']))
                            @can('listing_archive')
                            <div class="menu-item mx-3 my-3">
                                <a class="menu-link px-3 fs-7 gap-3" id="bulkRestoreBtn" href="">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                    Restore
                                </a>
                            </div>
                            @endcan
                        @else
                            @can('listing_assign')
                                <div class="menu-item mx-3 mt-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3" id="bulkAssign" type="button">
                                        <i class="fa fa-user"></i>
                                        Assign to Agent
                                    </a>
                                </div>
                            @endcan

                            @can('listing_publish')
                                <div class="menu-item mx-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3 bulkStatusChangeBtn" data-status="4" data-type="Publish">
                                        <i class="fa fa-check"></i>
                                        Publish 
                                    </a>
                                </div>
                            @endcan

                            @can('listing_prospect')
                                <div class="menu-item mx-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3 bulkStatusChangeBtn" data-status="2" data-type="Prospect">
                                        <i class="fa fa-hourglass-start"></i>
                                        Prospect 
                                    </a>
                                </div>
                            @endcan

                            @can('listing_off_market')
                                <div class="menu-item mx-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3 bulkStatusChangeBtn" data-status="5" data-type="Off-Market">
                                        <i class="fa fa-eye-slash"></i>
                                        Off-Market 
                                    </a>
                                </div>
                            @endcan

                            @can('listing_duplicate')
                                <div class="menu-item mx-3">
                                    <a href="" class="menu-link px-3 fs-7 gap-3" id="bulkDuplicateBtn" type="button">
                                        <i class="fa fa-file"></i>
                                        Duplicate
                                    </a>
                                </div>
                            @endcan

                            @can('listing_archive')
                                <div class="menu-item mx-3">
                                    <a class="menu-link px-3 fs-7 gap-3" id="bulkDeleteBtn" href="">
                                        <i class="fa fa-trash"></i>
                                        Archive
                                    </a>
                                </div>
                            @endcan

                            @can('listing_send_whatsapp')
                                <div class="menu-item mx-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3">
                                        <i class="fa-brands fa-whatsapp"></i>
                                        Send to WhatsApp
                                    </a>
                                </div>
                            @endcan

                            @can('listing_send_email')
                                <div class="menu-item mx-3 mb-3">
                                    <a href="#" class="menu-link px-3 fs-7 gap-3" type="button" id="bulkEmailBtn">
                                        <i class="fa fa-envelope"></i>
                                        Send to Email
                                    </a>
                                </div>
                            @endcan

                        @endif
                    </div>
                </div>
                
            </div>
            @if(!isset($_GET['archived']))
                <button class="btn btn-flex btn-primary btn-sm mr-1" data-bs-toggle="modal" data-bs-target="#editModal" data-action="create">
                    <i class="ki-duotone ki-plus fs-3"></i>
                    Add
                </button>
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
        table thead th input, table thead th .selectTwo{
            font-size:10px !important;
        }
        table tbody tr td{
            font-weight: 600 !important;
            font-size:10px !important;
        }
        .searchStatus{
            width:80px !important;
        }
        .searchPropertyFor{
            width:70px !important;
        }
        .searchPropertyType{
            width:80px !important;
        }
        .searchUnitNo{
            width:100px !important;
        }
        .searchBeds{
            width:70px !important;
        }
        .searchBaths{
            width:70px !important;
        }
        .searchCommunity{
            width:150px !important;
        }
        .searchSubCommunity{
            width:150px !important;
        }
        .searchTower{
            width:150px !important;
        }
        .searchPortal{
            width:150px !important;    
        }
        .searchExternalAgent{
            width:150px !important;
        }
        .searchListingAgent{
            width:150px !important;
        }
        .searchPrice{
            width:100px !important;
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
                        <th id="{{ $columnDetails['index'] }}" class="{{ $loop->last ? ' px-2 rounded-end text-end' : '' }}" style="{{ $loop->last ? 'width:90px;' : '' }}">{{ $columnName }}</th>
                    @endforeach
                </thead>
                <thead>
                    <tr id="filterHead">
                        <th id="0"></th>
                        <th id="1" class="{{ $columns['owner']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchOwner input" placeholder="Search by Owner name, email, phone" style="width:200px !important;">
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
                        <th id="3" class="{{ $columns['refno']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchRefno input" placeholder="Search by Ref No" style="width:80px !important;" />
                        </th>

                        <th id="4" class="{{ $columns['for']['visible'] ? '' : 'd-none' }}">
                            <select name="searchPropertyFor" id="searchPropertyFor" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchPropertyFor input">
                                <option value="" data-name="">All</option>
                                <option value="sale" data-name="sale">Sale</option>
                                <option value="rent" data-name="rent">Rent</option>
                                <option value="short_term" data-name="short_term">Short-Term</option>
                                <option value="offplan" data-name="offplan">Off-Plan</option>
                            </select>
                        </th>
                        <th id="5" class="{{ $columns['type']['visible'] ? '' : 'd-none' }}">
                            <select name="searchPropertyType" data-dropdown-css-class="w-200px" id="searchPropertyType" class="form-select form-select-sm form-select-solid border selectTwo searchPropertyType input">
                                <option value="" data-name="">All</option>
                                @if(count($property_types))
                                    @foreach($property_types as $property_type)
                                        <option value="{{ $property_type->id }}" data-name="{{ $property_type->name }}">{{ $property_type->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>
                        <th id="6" class="{{ $columns['unit_no']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchUnitNo input" placeholder="Search by Unit No#" />
                        </th>


                        <th id="7" class="{{ $columns['community']['visible'] ? '' : 'd-none' }}">
                            <select name="searchCommunity" id="searchCommunity" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchCommunity input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="8" class="{{ $columns['sub_community']['visible'] ? '' : 'd-none' }}">
                            <select name="searchSubCommunity" id="searchSubCommunity" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchSubCommunity input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="9" class="{{ $columns['tower']['visible'] ? '' : 'd-none' }}">
                            <select name="searchTower" id="searchTower" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchTower input">
                                <option value="" data-name="">All</option>
                            </select>
                        </th>

                        <th id="10" class="{{ $columns['tower']['visible'] ? '' : 'd-none' }}">
                            <select name="searchPortal" id="searchPortal" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchPortal input">
                                <option value="" data-name="">All</option>
                                @if(count($portals) > 0)
                                    @foreach($portals as $key => $portal)
                                        <option value="{{ $portal->name }}" data-kt-image="{{ $portal->logoImage() }}" data-name="{{ $portal->name }}">{{ $portal->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="11" class="{{ $columns['beds']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchBeds input" placeholder="Search by Beds" />
                        </th>
                        <th id="12" class="{{ $columns['baths']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchBaths input" placeholder="Search by Baths" />
                        </th>

                        <th id="13" class="{{ $columns['price']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchPrice input" placeholder="Search by Price" />
                        </th>

                        <th id="14" class="{{ $columns['bua']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchBUA input" placeholder="Search by BUA" />
                        </th>
                        
                        <th id="15" class="{{ $columns['rera_permit']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchReraPermit input" placeholder="Search by Rera Permit" />
                        </th>

                        <th id="16" class="{{ $columns['furnished']['visible'] ? '' : 'd-none' }}">
                            <select name="searchFurnished" id="searchFurnished" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchFurnished input">
                                <option value="" data-name="">All</option>
                                <option value="Furnished" data-name="Furnished">Furnished</option>
                                <option value="Unfurnished" data-name="Unfurnished">Unfurnished</option>
                                <option value="Partly Furnished" data-name="Partly Furnished">Partly Furnished</option>
                            </select>
                        </th>

                        <th id="17" class="{{ $columns['category']['visible'] ? '' : 'd-none' }}">
                            <select name="searchCategory" id="searchCategory" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchCategory input">
                                <option value="" data-name="">All</option>
                                @if(count($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" data-name="{{ $category->name }}">{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="18" class="{{ $columns['marketing_agent']['visible'] ? '' : 'd-none' }}">
                            <select name="searchMarketingAgent" data-dropdown-css-class="w-200px" id="searchMarketingAgent" class="form-select form-select-sm form-select-solid border selectTwo searchMarketingAgent input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="19" class="{{ $columns['listing_agent']['visible'] ? '' : 'd-none' }}">
                            <select name="searchExternalAgent" data-dropdown-css-class="w-200px" id="searchExternalAgent" class="form-select form-select-sm form-select-solid border selectTwo searchExternalAgent input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="20" class="{{ $columns['created_by']['visible'] ? '' : 'd-none' }}">
                            <select name="searchCreatedBy" data-dropdown-css-class="w-200px" id="searchCreatedBy" class="form-select form-select-sm form-select-solid border selectTwo searchCreatedBy input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>
                        <th id="21" class="{{ $columns['updated_by']['visible'] ? '' : 'd-none' }}">
                            <select name="searchUpdatedBy" data-dropdown-css-class="w-200px" id="searchUpdatedBy" class="form-select form-select-sm form-select-solid border selectTwo searchUpdatedBy input">
                                <option value="" data-name="">All</option>
                                @if(count($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="22" class="{{ $columns['added_on']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchCreateDate input" placeholder="Search by Date input" /></th>
                        <th id="23" class="{{ $columns['last_update']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm searchDate input" placeholder="Search by Date input" /></th>
                        <th id="24" class="{{ $columns['published_on']['visible'] ? '' : 'd-none' }}"><input type="text" class="form-control form-control-sm singleDate searchPublishedDate input" placeholder="Search by Date input" /></th>

                        <th id="25" class="{{ $columns['project_status']['visible'] ? '' : 'd-none' }}">
                            <select name="searchProjectStatus" data-dropdown-css-class="w-200px" id="searchProjectStatus" class="form-select form-select-sm form-select-solid border selectTwo searchProjectStatus input">
                                <option value="" data-name="">All</option>
                                @if(count($project_statuses))
                                    @foreach($project_statuses as $project_status)
                                        <option value="{{ $project_status->id }}" data-name="{{ $project_status->name }}">{{ $project_status->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="26" class="{{ $columns['plot_area']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchPlotArea input" placeholder="Search by Plot Area" />
                        </th>

                        <th id="27" class="{{ $columns['exclusive']['visible'] ? '' : 'd-none' }}">
                            <select name="searchExclusive" id="searchExclusive" class="form-select form-select-sm form-select-solid border selectTwo searchExclusive input">
                                <option value="" data-name="">All</option>
                                <option value="yes" data-name="sale">Yes</option>
                                <option value="no" data-name="rent">No</option>
                            </select>
                        </th>

                        <th id="28" class="{{ $columns['hot']['visible'] ? '' : 'd-none' }}">
                            <select name="searchHot" id="searchHot" class="form-select form-select-sm form-select-solid border selectTwo searchHot input">
                                <option value="" data-name="">All</option>
                                <option value="yes" data-name="sale">Yes</option>
                                <option value="no" data-name="rent">No</option>
                            </select>
                        </th>

                        <th id="29" class="{{ $columns['occupancy']['visible'] ? '' : 'd-none' }}">
                            <select name="searchOccupancy" data-dropdown-css-class="w-200px" id="searchOccupancy" class="form-select form-select-sm form-select-solid border selectTwo searchOccupancy input">
                                <option value="" data-name="">All</option>
                                @if(count($occupancies))
                                    @foreach($occupancies as $occupancy)
                                        <option value="{{ $occupancy->id }}" data-name="{{ $occupancy->name }}">{{ $occupancy->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="30" class="{{ $columns['cheques']['visible'] ? '' : 'd-none' }}">
                            <input type="text" class="form-control form-control-sm searchCheques input" placeholder="Search by Cheques" />
                        </th>

                        <th id="31" class="{{ $columns['developer']['visible'] ? '' : 'd-none' }}">
                            <select name="searchDeveloper" id="searchDeveloper" data-dropdown-css-class="w-200px" class="form-select form-select-sm form-select-solid border selectTwo searchDeveloper input">
                                <option value="" data-name="">All</option>
                                @if(count($developers))
                                    @foreach($developers as $developer)
                                        <option value="{{ $developer->id }}" data-name="{{ $developer->name }}">{{ $developer->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </th>

                        <th id="32"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<form id="editForm" action="{{ route('listings.store') }}" method="post" enctype="multipart/form-data">
    <div class="modal fade modalRight w-99" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            @csrf
            <div class="modal-content bg-grey">
                <div class="modal-header py-3 bg-primary rounded-0">
                    <h5 class="modal-title text-white" id="editModalLabel">Listing Details</h5>
                    <div class="d-flex">
                        <span class="badge badge-dark mx-3">Ref No#: &nbsp <span id="modalRefNo"></span></span>
                        <div class="form-group">
                            <div class="form-check form-check-solid form-switch form-check-custom fv-row">
                                <label for="lead_gen" class="form-label my-0 text-white fw-bold">Lead Gen? </label>
                                <input class="form-check-input w-65px h-30px mx-3" type="checkbox" id="lead_gen" name="lead_gen" />
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div>
                                <label for="status" class="form-label my-0 fw-bold text-white mx-2">Listing Status <span class="required"></span></label>
                            </div>
                            <div class="form-group w-200px">
                                <select name="status" id="status" class="form-select form-select-sm form-select-solid border selectTwoModal">
                                    @if(count($statuses) > 0)
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" {{ $status->name == 'Prospect' ? 'selected': '' }}>{{ $status->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <!-- <button type="button" class="btn btn-xs btn-light" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </button> -->
                    </div>
                </div>
                    <div class="modal-body">
                        <div class="page-loader flex-column bg-dark bg-opacity-25">
                            <span class="spinner-border text-primary" role="status"></span>
                            <span class="text-gray-800 fs-6 fw-semibold mt-5">Loading...</span>
                        </div>
                        <input type="hidden" id="editId" name="id">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="card mt-4 h-100">
                                    <div class="card-header p-3 bg-dark d-flex align-items-between">
                                        <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i> Basic Details</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">

                                        <div class="mb-3 form-group">
                                            <label for="external_refno" class="form-label">External Ref No# <span class=""></span></label>
                                            <input type="text" class="form-control form-control-sm form-control-solid border" id="external_refno" name="external_refno" placeholder="eg: AB-001">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="property_for" class="form-label">Property For <span class="required"></span></label>
                                                    <select name="property_for" id="property_for" class="form-select form-select-sm form-select-solid border selectTwoModal property_for">
                                                        <option value="sale" data-name="sale">Sale</option>
                                                        <option value="rent" data-name="rent">Rent</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 form-group">
                                                    <label for="category_id" class="form-label">Category <span class="required"></span></label>
                                                    <select name="category_id" id="category_id" class="form-select form-select-sm form-select-solid border selectTwoModal category_id">
                                                        @if(count($categories) > 0)
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="property_type" class="form-label">Property Type <span class="required"></span></label>
                                            <select name="property_type" id="property_type" class="form-select form-select-sm form-select-solid border property_type">
                                                
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="city_id" class="form-label">City <span class="required"></span></label>
                                            <select name="city_id" id="city_id" class="form-select form-select-sm form-select-solid border selectTwoModal city_id">
                                                <option value="" data-name="">Select City</option>
                                                @if(count($cities) > 0)
                                                    @foreach($cities as $city)
                                                        <option value="{{ $city->id }}" {{ $city->name == 'Dubai' ? 'selected' : ''}}>{{ $city->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="community_id" class="form-label">Community <span class="required"></span></label>
                                            <select name="community_id" id="community_id" class="form-select form-select-sm form-select-solid border selectTwoModal community_id">
                                                <option value="" data-name="">Select Community</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="sub_community_id" class="form-label">Sub Community<span class="required"></span></label>
                                            <select name="sub_community_id" id="sub_community_id" class="form-select form-select-sm form-select-solid border selectTwoModal sub_community_id">
                                                <option value="" data-name="">Select Sub Community</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="tower_id" class="form-label">Tower</label>
                                            <select name="tower_id" id="tower_id" class="form-select form-select-sm form-select-solid border selectTwoModal tower_id">
                                                <option value="" data-name="">Select Tower</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="agent_id" class="form-label">Listing Agent <span class="required"></span></label>
                                            <select name="agent_id" id="agent_id" class="form-select form-select-sm form-select-solid border agentSelectModal">
                                                <option value="">Select Agent</option>
                                                @if(count($users) > 0)
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" data-kt-select2-user="{{ $user->profileImage() }}">{{ $user->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="marketing_agent_id" class="form-label">External Agent</span></label>
                                            <select name="marketing_agent_id" id="marketing_agent_id" class="form-select form-select-sm form-select-solid border agentSelectModal">
                                                <option value="">Select Agent</option>
                                                @if(count($users) > 0)
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" data-kt-select2-user="{{ $user->profileImage() }}">{{ $user->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mt-4 h-100">
                                    <div class="card-header p-3 bg-dark d-flex align-items-between">
                                        <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i>Content</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">
                                        <div class="mb-3 form-group">
                                            <label for="title" class="form-label">Property Title <span class="required"></span></label>
                                            <input type="text" class="form-control form-control-sm max_length mb-2 form-control-solid border" id="property_title" name="title" maxlength="50" placeholder="eg: Luxury apartment">
                                        </div>

                                        <div class="mb-3 form-group">
                                            <label for="desc" class="form-label">Description <span class="required desc"></span></label>
                                            <textarea name="desc" id="ckEditor" class="form-control max_length mb-2 property_desc form-control-solid border" maxlength="50" required placeholder="Property Description"></textarea>
                                        </div>

                                        <div class="row">

                                            <div class="col-md-4  mb-3 form-group">
                                                <label for="occupancy_id" class="form-label">Occupancy <span class="required"></span></label>
                                                <select name="occupancy_id" id="occupancy_id" class="form-select form-select-sm form-select-solid border selectTwoModal occupancy_id">
                                                    <option value="" data-name="">Select Option</option>
                                                    @if(count($occupancies))
                                                        @foreach($occupancies as $occupancy)
                                                            <option value="{{ $occupancy->id }}" data-name="{{ $occupancy->name }}">{{ $occupancy->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="col-md-5 mb-3 form-group">
                                                <label for="next_availability_date" class="form-label">Next Availability Date <span class="required"></span></label>
                                                <input type="text" name="next_availability_date" id="next_availability_date" class="form-control form-control-sm singleDate form-control-solid border" placeholder="Select Date" />
                                            </div>

                                            <div class="col-md-3 mb-3 form-group">
                                                <label for="cheques" class="form-label">Cheques <span class="required"></span></label>
                                                <div class="position-relative border rounded" id="chequesDialer" data-kt-dialer="true" data-kt-dialer-min="0" data-kt-dialer-max="30" data-kt-dialer-step="1" data-kt-dialer-prefix="" data-kt-dialer-decimals="0">
                                                    <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 start-0" data-kt-dialer-control="decrease">
                                                        <i class="ki-duotone ki-minus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                    <input type="text" class="form-control form-control-solid form-control-sm ps-12" data-kt-dialer-control="input" placeholder="cheques" name="cheques" id="cheques" value="0"/>

                                                    <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 end-0" data-kt-dialer-control="increase">
                                                        <i class="ki-duotone ki-plus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-3 form-group">
                                                <label class="form-label" for="amenities">Facilities & Amenities <span class="required"></span></label>
                                                <input class="form-control" value="" id="amenities" name="amenities"/>
                                            </div>

                                            <div class="col-md-12 mb-3 form-group mt-3 mb-5">
                                                <label class="form-label" for="portals">
                                                    Portals <span class="required"></span> 
                                                    <div class="d-inline ml-4">
                                                        <button class="btn btn-primary btn-xs sAllPortals" type="button">Select All</button>
                                                    </div>
                                                </label>

                                                <!--begin::Row-->
                                                <div class="d-flex gap-3" data-kt-buttons="true" data-kt-buttons-target=".form-check-image, .form-check-input">
                                                    <!--begin::Col-->
                                                    @if(count($portals) > 0)
                                                        @foreach($portals as $key => $portal)
                                                            <div class="border shadow rounded p-2 mr-">
                                                                <label class="form-check-image d-flex gap-2">
                                                                    <img src="{{ $portal->logoImage() }}" class="circle w-250x h-20px"/>
                                                                    <div class="form-check form-check-sm form-check-custom form-check-solid gap-2">
                                                                        <div class="form-check-label fs-8">
                                                                            {{ $portal->name }}
                                                                        </div>
                                                                        <input class="form-check-input h-15px w-15px" type="checkbox" value="{{ $portal->id }}" name="portals[]"/>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- <div class="col-md-6 mb-3 form-group">
                                                <label for="price_sqft" class="form-label">Price / Sq Ft</label>

                                                <div class="input-group input-group-sm flex-nowrap">
                                                    <input type="text" class="form-control form-control-sm" id="price_sqft" readonly name="price_sqft" placeholder="Price Sq Ft" style="border-top-left-radius:5px; border-bottom-left-radius:5px;">
                                                    <span class="input-group-text p-0 px-2">
                                                        AED
                                                    </span>
                                                </div>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card mt-4 h-100">
                                    <div class="card-header p-3 bg-dark d-flex align-items-between">
                                        <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i>Other Details</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">

                                        <div class="row">
                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="unit_no" class="form-label">Unit No# <span class="required"></span></label>
                                                <input type="text" class="form-control form-control-sm mb-2 form-control-solid border" id="unit_no" name="unit_no" placeholder="eg: 001">
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="bua" class="form-label">BUA Size (Sq.Ft) <span class="required"></span></label>
                                                <input type="text" class="form-control form-control-sm mb-2 form-control-solid border" id="bua" name="bua" placeholder="eg: 001">
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="plot_no" class="form-label">Plot No# <span class="required"></span></label>
                                                <input type="text" class="form-control form-control-sm mb-2 form-control-solid border" id="plot_no" name="plot_no" placeholder="eg: 001">
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="plot_area" class="form-label">Plot Area <span class="required"></span></label>
                                                <input type="text" class="form-control form-control-sm mb-2 form-control-solid border" id="plot_area" name="plot_area" placeholder="eg: 001">
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="rera_permit" class="form-label">Permit No# <span class="required"></span></label>
                                                <input type="text" class="form-control form-control-sm mb-2 form-control-solid border" id="rera_permit" name="rera_permit" placeholder="eg: 001">
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="parking" class="form-label">Parking <span class="required"></span></label>
                                                <div class="position-relative border rounded" id="parkingDialer" data-kt-dialer="true" data-kt-dialer-min="0" data-kt-dialer-max="10" data-kt-dialer-step="1" data-kt-dialer-suffix="" data-kt-dialer-decimals="0">
                                                    <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 start-0" data-kt-dialer-control="decrease">
                                                        <i class="ki-duotone ki-minus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                    <input type="text" class="form-control form-control-solid form-control-sm ps-12" data-kt-dialer-control="input" placeholder="Parking" name="parking" id="parking" value="0"/>

                                                    <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 end-0" data-kt-dialer-control="increase">
                                                        <i class="ki-duotone ki-plus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="beds" class="form-label">Bedrooms <span class="required"></span></label>
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

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="baths" class="form-label">Bathrooms <span class="required"></span></label>
                                                <div class="position-relative border rounded" id="bathroomDialer" data-kt-dialer="true" data-kt-dialer-min="0" data-kt-dialer-max="10" data-kt-dialer-step="1" data-kt-dialer-suffix="" data-kt-dialer-decimals="0">
                                                    <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 start-0" data-kt-dialer-control="decrease">
                                                        <i class="ki-duotone ki-minus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                    <input type="text" class="form-control form-control-solid form-control-sm border-0 ps-12" data-kt-dialer-control="input" placeholder="Baths" name="baths" id="baths" value="0"/>

                                                    <button type="button" class="btn btn-icon btn-active-color-gray-700 position-absolute translate-middle-y top-50 end-0" data-kt-dialer-control="increase">
                                                        <i class="ki-duotone ki-plus-circle fs-1"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="furnished" class="form-label">Furnished? <span class="required"></span></label>

                                                <select name="furnished" id="furnished" class="form-select form-select-sm form-select-solid border selectTwoModal furnished">
                                                    <option value="" data-name="">Select Option</option>
                                                    <option value="Furnished" data-name="Furnished">Furnished</option>
                                                    <option value="Unfurnished" data-name="Unfurnished">Unfurnished</option>
                                                    <option value="Partly Furnished" data-name="Partly Furnished">Partly Furnished</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3 form-group">
                                                <label for="furnished" class="form-label">Project Status <span class="required"></span></label>
                                                <select name="project_status_id" id="project_status_id" class="form-select form-select-sm form-select-solid border selectTwoModal project_status_id">
                                                    <option value="" data-name="">Select Option</option>
                                                    @if(count($project_statuses))
                                                        @foreach($project_statuses as $project_status)
                                                            <option value="{{ $project_status->id }}" data-name="{{ $project_status->name }}">{{ $project_status->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="col-md-12 mb-3 form-group">
                                                <label for="price" class="form-label">POA / Price <span class="required"></span></label>

                                                <div class="input-group input-group-sm flex-nowrap">
                                                    <span class="input-group-text p-0 border px-2">
                                                        <div class="form-check form-check-solid form-switch form-check-custom fv-row">
                                                            <input class="form-check-input w-45px h-30px border" type="checkbox" id="poa" name="poa" />
                                                        </div>
                                                    </span>
                                                    <input type="text" class="form-control form-control-sm border" id="price" name="price" placeholder="Price" style="border-top-left-radius:5px; border-bottom-left-radius:5px;">
                                                    <span class="input-group-text p-0 px-2 border">
                                                        AED
                                                    </span>
                                                    <span class="input-group-text p-0 border-0 d-none frequency">
                                                        <select name="frequency" id="frequency" class="form-select form-select-sm form-select-solid border" style="border-top-left-radius:0px; border-bottom-left-radius:0px;">
                                                            <option value="Yearly">Yearly</option>
                                                            <option value="Monthly">Monthly</option>
                                                            <option value="Weekly">Weekly</option>
                                                            <option value="Daily">Daily</option>
                                                        </select>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <!-- <div class="mb-3 form-group">
                                                    <label for="owner_id" class="form-label">Owner <span class="required"></span></label>
                                                    <select name="owner_id" id="owner_id" class="form-control ownerSelectModal">
                                                        <option value="">Select Owner</option>
                                                    </select>
                                                </div> -->

                                                <div class="mb-3 form-group">
                                                    <label for="owner_search" class="form-label">Select Owner <span class="required"></span></label>
                                                    <div class="input-group input-group-sm flex-nowrap">
                                                        <input type="text" id="owner_search" class="form-control form-control-sm form-control-solid border" placeholder="Type name, refno, email or phone to search" autocomplete="off">
                                                        <span class="input-group-text p-0 overflow-hidden">
                                                            <button class="btn btn-primary btn-sm rounded-0" id="addContactModalBtn" type="button"><i class="fa fa-plus"></i></button>
                                                        </span>
                                                    </div>
                                                    
                                                    <input type="hidden" id="owner_id" name="owner_id" autocomplete="off">
                                                    
                                                    <!-- Display selected owner details -->
                                                    <div class="mt-3 border rounded p-3 shadow-sm">
                                                        <i class="fa fa-id-card"></i> <span class="mx-3" id="owner_refno"></span>
                                                        <i class="fa fa-user"></i><span class="mx-3" id="owner_name"></span>
                                                        
                                                        <br>
                                                        <div class="separator separator-dashed my-3"></div>
                                                        <i class="fa fa-phone"></i><span class="mx-3" id="owner_phone"></span>
                                                        <br>
                                                        <div class="separator separator-dashed my-3"></div>
                                                        <i class="fa fa-envelope"></i><span class="mx-3" id="owner_email"></span>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
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
                                                        <!-- <div class="new-comment bg-light-primary p-3 rounded">
                                                            <textarea class="form-control note border-0 shadow-sm" id="note" name="note" rows="3" data-kt-element="input" placeholder="Type a note"></textarea>
                                                            <button type="button" class="btn btn-primary btn-xs noteAddBtn mt-3" disabled onclick="addNoteFunction()">Add Note</button>
                                                        </div> -->

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
                                                                    <input type="text" name="event_date" id="event_date" disabled class="singleDateTime btn btn-light-primary py-0 me-1 fs-8 text-start px-1" style="height:25px !important;" placeholder="Select Date" />
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
                                            <!-- <div class="notesTable mt-4">
                                                

                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mt-4 h-100">
                                    <div class="card-header p-3 bg-dark d-flex align-items-between">
                                        <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i>Image Gallery</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">
                                        <div class="fv-row">
                                            <div class="dropzone image_dropzone" id="images_gallery_drop_zone">
                                                <div class="dz-message needsclick d-block">
                                                    <div>
                                                        <i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                                    </div>

                                                    <div class="ms-4">
                                                        <h3 class="fs-5 text-center fw-bold text-gray-900 mb-1">Drop files here or click to upload.</h3>
                                                        <p class="fs-7 text-center text-start m-0 fw-semibold text-gray-500">Upload up to 20 files</p>
                                                        <p class="fs-7 text-center text-start m-0 fw-semibold text-gray-500">Recommended Minimum Dimensions: 1920 x 1080px</p>
                                                        <p class="fs-7 text-center text-start m-0 fw-semibold text-gray-500">Supports: jpg, jpeg, png, gif</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12 mt-2">
                                            <button class="btn btn-primary mb-3 btn-xs selectAllButton" type="button">Select All</button>
                                            <button class="btn btn-danger mb-3 btn-xs" type="button" id="removeSelectedImages" style="display:none;">Remove Selected</button>
                                            <button class="btn btn-warning mb-3 btn-xs d-none" type="button" id="markAllWatermarks" onclick="confirmWatermark()">Mark as Watermark</button>
                                        </div>

                                        <div class="col-md-12 mt-2">
                                            <div class="row mt-4 media_edit">

                                            </div>
                                        </div>

                                        
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row mt-4">

                            <div class="col-md-6">
                                <div class="card mt-4 h-100">
                                    <div class="card-header p-3 bg-dark d-flex align-items-between">
                                        <h3 class="text-white"> <i class="fas fa-edit text-white mr-1"></i>Documents</h3>
                                    </div>
                                    <div class="card-body py-4 px-5">
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

                            <div class="col-md-6">
                                <div class="card mt-4 h-100">
                                    <div class="card-body py-4 px-5">
                                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                                            <li class="nav-item">
                                                <a class="nav-link text-dark fw-bold active" data-bs-toggle="tab" href="#tabeAdditional"> <i class="fa fa-sticky-note"></i> Additional Details</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link text-dark fw-bold" data-bs-toggle="tab" href="#tabLog"> <i class="fa fa-clock"></i> Change Log</a>
                                            </li>
                                        </ul>

                                        <div class="tab-content" id="myTabContent">
                                            <div class="tab-pane fade show active" id="tabeAdditional" role="tabpanel">
                                                <div class="row">

                                                    <div class="col-md-6 mb-3 form-group">
                                                        <label for="view" class="form-label">View </label>
                                                        <input type="text" name="view" id="view" class="form-control form-control-sm form-control-solid border" placeholder="Beautiful view" />
                                                    </div>

                                                    <div class="col-md-6 mb-3 form-group">
                                                        <label for="video_link" class="form-label">YouTube Video </label>
                                                        <input type="text" name="video_link" id="video_link" class="form-control form-control-sm form-control-solid border" placeholder="https://youtube.com/starling" />
                                                    </div>

                                                    <div class="col-md-6 mb-3 form-group">
                                                        <label for="live_tour_link" class="form-label">Livetour 360° </label>
                                                        <input type="text" name="live_tour_link" id="live_tour_link" class="form-control form-control-sm form-control-solid border" placeholder="http://website.com" />
                                                    </div>

                                                    <div class="col-md-6 mb-3 form-group">
                                                        <label for="developer_id" class="form-label">Developer </label>
                                                        <select name="developer_id" id="developer_id" class="form-select form-select-sm form-select-solid border selectTwoModal developer_id">
                                                            <option value="" data-name="">Select Developer</option>
                                                            @if(count($developers))
                                                                @foreach($developers as $developer)
                                                                    <option value="{{ $developer->id }}" data-name="{{ $developer->name }}">{{ $developer->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="tabLog" role="tabpanel">

                                                <div class="changeLogDiv">
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

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white py-2">
                        <button type="submit" class="btn btn-primary btn-sm">Save Listing</button>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    </div>
            </div>
        </div>
    </div>
</form>


<form id="contactAddForm" action="{{ route('owners.storeAjax') }}" method="post">
<div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-grey">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="addContactModalLabel">Add New Owner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                    @csrf
                    <div class="row">
                        <div class="col-lg-12 mb-3 form-group">
                            <label for="name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text p-0 border-0" id="basic-addon1">
                                    <select name="title" id="title" class="form-select form-select-sm form-select-solid border title" required style="border-top-right-radius:0px; border-bottom-right-radius:0px;">
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
                                <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Owner Name" required>
                            </div>

                        </div>

                        <div class="col-lg-6 mb-3 form-group">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm phone" id="phone" name="phone" placeholder="Phone" required>
                        </div>

                        <div class="col-lg-6 mb-3 form-group">
                            <label for="whatsapp" class="form-label">WhatsApp</label>
                            <input type="text" class="form-control form-control-sm phone" id="whatsapp" name="whatsapp" placeholder="WhatsApp">
                        </div>

                        <div class="col-lg-6 mb-3 form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm" id="email" name="email" placeholder="Email">
                        </div>

                        <div class="col-lg-6 mb-3 form-group">
                            <label for="dob" class="form-label">Date Of Birth</label>
                            <input type="text" class="form-control form-control-sm singleDate" id="dob" name="dob" placeholder="Date Of Birth">
                        </div>

                        <div class="col-lg-12 mb-3 form-group">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control form-control-sm address" id="address" name="address" placeholder="Address">
                        </div>
                    </div>
                
            </div>
            <div class="modal-footer py-3">
                <button type="submit" class="btn btn-primary btn-sm">Add Contact</button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Close</button>
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
<script src="{{ asset('assets/plugins/custom/ckeditor/ckeditor-classic.bundle.js') }}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

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

    var images_gallery_drop_zone = new Dropzone("#images_gallery_drop_zone", {
        url: "uploads",
        paramName: "images",
        maxFiles: 20,
        maxFilesize: 10, // MB
        addRemoveLinks: true,
        autoQueue: false,        
        accept: function(file, done) {
            if (file.name == "wow.jpg") {
                done("Naha, you don't.");
            } else {
                done();
            }
        }
    });

    checkSelectAllButton();

    $('.sAllPortals').click(function () {
        var newState = ($(this).text() === 'Select All') ? true : false;
        $('input[name="portals[]"]').prop('checked', newState);
        checkSelectAllButton();
    });

    $('input[name="portals[]"]').change(function () {
        checkSelectAllButton();
    });

    function checkSelectAllButton() {
        var allChecked = $('input[name="portals[]"]').length === $('input[name="portals[]"]:checked').length;
        $('.sAllPortals').text(allChecked ? 'Unselect All' : 'Select All');
    }

    $('.limitToDigits').on('input', function () {
        // Remove non-digit characters
        $(this).val($(this).val().replace(/\D/g, ''));
    });

    var amenitiesInp = document.querySelector("#amenities");
    var tagifyElem = new Tagify(amenitiesInp, {
        whitelist: [],
        placeholder: 'Type amenity name to search...',
        maxTags: 10,
        dropdown: {
            maxItems: 20,
            classname: "tagify__inline__suggestions",
            enabled: 0,
            closeOnSelect: false
        }
    });
    // Fetch amenities from the backend
    fetch('{{ route('amenities.getList') }}')  // Use the route helper here
        .then(response => response.json())
        .then(data => {
            // Extract amenity names from the response
            //var amenityNames = data.amenities.map(amenity => amenity.name);
            var amenityNames = data.amenities.map(amenity => ({ id: amenity.id, value: amenity.name }));

            // Add amenityNames to the Tagify whitelist
            tagifyElem.settings.whitelist = amenityNames;

            // Update Tagify with the new whitelist
            //tagifyElem.updateWhitelist(amenityNames);
        })
        .catch(error => console.error('Error fetching amenities:', error));


    function togglePoa() {
        if ($('#poa').prop('checked')) {
            $('#price, #frequency').prop('readonly', true);
        } else {
            $('#price, #frequency').prop('readonly', false);
        }
    }

    function formatPriceInput() {
        var numericValue = $('#price').val().replace(/[^0-9]/g, '');
        var formattedValue = numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        $('#price').val(formattedValue);
    }
    formatPriceInput();
    $('#price').on('input', formatPriceInput);

    

    // $.ajax({
    //     url: '{{ route('portals.getList') }}',
    //     method: 'GET',
    //     dataType: 'json',
    //     success: function (data) {
    //         if (data && data.portals) {
    //             const usersList = data.portals.map(portal => ({
    //                 value: portal.id,
    //                 name: portal.name,
    //                 avatar: portal.logo_mage,
    //                 email: portal.name,
    //             }));

    //             // Use the fetched portalsList as needed
    //             console.log(usersList);
    //         }
    //     },
    //     error: function (error) {
    //         console.error('Error fetching portals:', error);
    //     }
    // });


    // var inputElm = document.querySelector('#portals');

    // function tagTemplate(tagData) {
    //     return `
    //         <tag title="${(tagData.title)}"
    //                 contenteditable='false'
    //                 spellcheck='false'
    //                 tabIndex="-1"
    //                 class="${this.settings.classNames.tag} ${tagData.class ? tagData.class : ""}"
    //                 ${this.getAttributes(tagData)}>
    //             <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>
    //             <div class="d-flex align-items-center">
    //                 <div class='tagify__tag__avatar-wrap ps-0'>
    //                     <img onerror="this.style.visibility='hidden'" class="rounded-circle w-15px h-15px me-2" src="${tagData.avatar}">
    //                 </div>
    //                 <span class='tagify__tag-text'>${tagData.name}</span>
    //             </div>
    //         </tag>
    //     `
    // }

    // function suggestionItemTemplate(tagData) {
    //     return `
    //         <div ${this.getAttributes(tagData)}
    //             class='tagify__dropdown__item d-flex align-items-center ${tagData.class ? tagData.class : ""}'
    //             tabindex="0"
    //             role="option">

    //             ${tagData.avatar ? `
    //                     <div class='tagify__dropdown__item__avatar-wrap me-2'>
    //                         <img onerror="this.style.visibility='hidden'"  class="rounded-circle w-15px h-15px me-2" src="${tagData.avatar}">
    //                     </div>` : ''
    //                 }

    //             <div class="d-flex flex-column">
    //                 <strong>${tagData.name}</strong>
    //             </div>
    //         </div>
    //     `
    // }

    // $(document).ready(function () {
    //     // Fetch portals using AJAX
    //     $.ajax({
    //         url: '{{ route('portals.getList') }}',
    //         method: 'GET',
    //         dataType: 'json',
    //         success: function (data) {
    //             if (data && data.portals) {
    //                 const usersList = data.portals.map(portal => ({
    //                     value: portal.id,
    //                     name: portal.name,
    //                     avatar: portal.logo_image,
    //                     email: portal.name,
    //                 }));

    //                 // Initialize Tagify on the above input node reference
    //                 var tagify = new Tagify(inputElm, {
    //                     tagTextProp: 'name',
    //                     enforceWhitelist: true,
    //                     skipInvalid: true,
    //                     dropdown: {
    //                         closeOnSelect: false,
    //                         enabled: 0,
    //                         classname: 'users-list',
    //                         searchKeys: ['name']
    //                     },
    //                     templates: {
    //                         tag: tagTemplate,
    //                         dropdownItem: suggestionItemTemplate
    //                     },
    //                     whitelist: usersList
    //                 });

    //                 tagify.on('dropdown:show dropdown:updated', onDropdownShow);
    //                 tagify.on('dropdown:select', onSelectSuggestion);

    //                 var addAllSuggestionsElm;

    //                 function onDropdownShow(e) {
    //                     var dropdownContentElm = e.detail.tagify.DOM.dropdown.content;

    //                     if (tagify.suggestedListItems.length > 1) {
    //                         addAllSuggestionsElm = getAddAllSuggestionsElm();

    //                         // insert "addAllSuggestionsElm" as the first element in the suggestions list
    //                         dropdownContentElm.insertBefore(addAllSuggestionsElm, dropdownContentElm.firstChild)
    //                     }
    //                 }

    //                 function onSelectSuggestion(e) {
    //                     if (e.detail.elm == addAllSuggestionsElm)
    //                         tagify.dropdown.selectAll.call(tagify);
    //                 }

    //                 // create a "add all" custom suggestion element every time the dropdown changes
    //                 function getAddAllSuggestionsElm() {
    //                     // suggestions items should be based on "dropdownItem" template
    //                     return tagify.parseTemplate('dropdownItem', [{
    //                         class: "addAll",
    //                         name: "Add all",
    //                         email: tagify.settings.whitelist.reduce(function (remainingSuggestions, item) {
    //                             return tagify.isTagDuplicate(item.value) ? remainingSuggestions : remainingSuggestions + 1
    //                         }, 0) + " Members"
    //                     }]
    //                     )
    //                 }
    //             }
    //         },
    //         error: function (xhr, status, error) {
    //             console.log(xhr);
    //             console.error('Error fetching portals:', error);
    //         }
    //     });
    // });

    
    
    


    togglePoa();

    $('#poa').on('change', function() {
        togglePoa();
    });


    function populateCommunities(cityId, selector, selectedCommunityId = null) {
        return new Promise(function(resolve, reject) {
            var communitySelect = $('#' + selector).select2();
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
                    if(selectedCommunityId == null){
                        $.each(communities, function(index, community) {
                            // Only append options if the community belongs to the selected city
                            if (community.city_id == cityId) {
                                var option = new Option(community.name, community.id, false, false);
                                $(option).attr('data-name', community.name);
                                communitySelect.append(option);
                            }
                        });
                    }

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

    populateCommunities(7, 'searchCommunity');

    $(document).on('change', '#city_id', function() {
        var selectedCityId = $(this).val();
        populateCommunities(selectedCityId, 'community_id');
    });

    $(document).on('change', '#community_id', function() {
        var selectedCityId = $('#city_id').val();
        var selectedId = $(this).val();
        populateSubCommunities(selectedCityId, 'sub_community_id', selectedId);
    });

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

    function populateSubCommunities(cityId, selector, community_id, selectedSubCommunityId = null) {
        return new Promise(function(resolve, reject) {
            var sub_communitySelect = $('#'+selector).select2();
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
        var selectedCityId = $('#city_id').val();
        var selectedCommunituId = $('#community_id').val();
        var sub_community_id = $(this).val();
        populateTowers(selectedCityId, 'tower_id', selectedCommunituId, sub_community_id);
    });

    function populateTowers(cityId, selector, community_id, sub_community_id, selected_id = null) {
        
        return new Promise(function(resolve, reject) {
            var selectElement = $('#'+selector).select2();
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
    
    function populatePropertyTypes(idName, type, cat_id, selectedId = null) {
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

        // Fetch communities via AJAX
        $.ajax({
            url: '{{ route('propertyTypes.getList') }}',
            type: 'GET',
            data: { cat_id: cat_id},
            success: function(data) {
                var property_types = data.property_types;

                if (!property_types || property_types.length === 0) {
                    console.error('No Property Types found for the selected category.');
                    return;
                }

                // Populate communities select element
                if(selectedId == null){
                    $.each(property_types, function(index, property_type) {
                        // Only append options on condition (removed)
                        var option = new Option(property_type.name, property_type.id, false, false);
                        $(option).attr('data-name', property_type.name);
                        selectElement.append(option);
                    });
                }

                // Select the item if item id exists
                if (selectedId) {
                    selectElement.val(selectedId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching property types:', error);
            }
        });
    }
    populatePropertyTypes('property_type', 'modal', $('#category_id').find(':selected').val());

    $(document).on('change', '#category_id', function() {
        var selectedId = $(this).val();
        populatePropertyTypes('property_type', 'modal', selectedId);
    });

    //alert($('#category_id').find(':selected').val());

    var ckEditorInstance;

    ClassicEditor.create(document.querySelector('#ckEditor'))
        .then(editor => {
            ckEditorInstance = editor;
        })
        .catch(error => {
            console.error(error);
        });

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

    // Function to make images sortable
    function makeImagesSortable() {
        var mediaDiv = $('.media_edit');

        new Sortable(mediaDiv[0], {
            animation: 150,
            handle: '.media_image',
            onEnd: function (evt) {
                updateSortOrder();
            }
        });
    }

    // Function to update sort order after dragging
    function updateSortOrder() {
        var mediaItems = $('.media_edit .position-relative');

        mediaItems.each(function (index) {
            var imageId = $(this).find('.select-image').val();
            $(this).find('.sort-order').val(index + 1);
        });
    }

    function validateAndUpdateClasses() {
        // Validate and add "is-valid" class
        $('#editForm :input, #editForm select').on('input change', function () {
            var $element = $(this);
            $element.removeClass('is-valid');

            if ($element.prop('required')) {
                var isValid = false;

                if ($element.is('select')) {
                    isValid = $element.find('option:selected').val() !== '';
                } else {
                    isValid = $element.val() !== '';
                }

                if (isValid) {
                    $element.addClass('is-valid');
                } else {
                    $element.removeClass('is-valid');
                }
            }
        });
    }
    validateAndUpdateClasses();

    function validateAndUpdateClasses2() {
        $('#editForm :input, #editForm select').each(function () {
            var $element = $(this);
            $element.removeClass('is-valid');

            if ($element.prop('required')) {
                var isValid = false;

                if ($element.is('select')) {
                    isValid = $element.find('option:selected').val() !== '';
                } else {
                    isValid = $element.val() !== '';
                }

                if (isValid) {
                    $element.addClass('is-valid');
                } else {
                    $element.removeClass('is-valid');
                }
            }
        });
    }

    //validateAndUpdateClasses();

    function toggleRequiredFields() {
        var listingStatus = $('#status option:selected').text();

        // Array of fields to be required based on listing status
        var requiredFields = [];

        requiredFields = ['property_title'];

        if (listingStatus === 'Available - Published') {
            requiredFields = ['external_refno', 'property_for', 'owner_id', 'category_id', 'property_type', 'next_availability_date', 'city_id', 'community_id', 'sub_community_id', 'property_title', 'agent_id', 'price', 'unit_no', 'bua', 'plot_no', 'plot_area', 'rera_permit', 'parking', 'beds', 'baths', 'furnished', 'project_status_id', 'occupancy_id', 'cheques', 'amenities'];
        }

        // Remove "required" attribute from all form inputs and selects
        $('#editForm :input, #editForm select').not('#ckeditor').removeAttr('required');

        // Remove the "required" class from all labels
        $('#editForm .form-group label span').each(function () {
            var fieldName = $(this).closest('.form-group').find('select, input').attr('id');

            if ($.inArray(fieldName, requiredFields) === -1) {
                if(!$(this).hasClass('desc')){
                    $(this).removeClass('required');
                }
            }
        });

        // Add required attributes and classes to specified fields
        $.each(requiredFields, function (index, fieldName) {
            // Handle CKEditor separately
            if (fieldName === 'property_title') {
                //$('#' + fieldName).attr('required', true);
            } else {
                var $field = $('#' + fieldName);
                //$field.attr('required', true);

                // Find the label for the field
                var $label = $field.closest('.form-group').find('label');
                $label.find('span').addClass('required');
            }
        });
    }

    // Initial call to set required fields based on default listing status
    toggleRequiredFields();

    $('#status').change(function () {
        
        toggleRequiredFields();
        validateAndUpdateClasses2();
    });

    $('#property_for').on('change', function () {
        var thisval = $(this).val();
        if(thisval == 'rent'){
            $('.frequency').removeClass('d-none');
        }
        else{
            $('.frequency').addClass('d-none');
        }
    });




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
    

    var urlParams = new URLSearchParams(window.location.search);
    var status = urlParams.get('status');
    var archived = urlParams.get('archived');
    var p_for = urlParams.get('for');
    const refnoParam = urlParams.get('refno');

    initializeDateRange('singleDate', null, 'single');
    initializeDateRange('singleDateTime', null, 'singleTime');

    initializeDateRangeTwo('searchDate', '{{ $firstDate }}');
    initializeDateRangeTwo('searchCreateDate', '{{ $firstDate }}');
    initializeDateRangeTwo('searchPublishedDate', '{{ $firstDate }}');
    var columnVisibility = {!! json_encode($columns) !!};

    //columns visib end

    var ajaxURL = '{{ route('listings.getListings') }}';

    if (archived) {
        ajaxURL += '?archivedd=yes';
    }

    if (p_for) {
        ajaxURL += (archived ? '&' : '?') + 'for=' + p_for;
    }

    var userRole = '{{ Auth::user()->getRoleNames()->first() }}';
    var loggedInUserId = '{{ Auth::user()->id }}';

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

                if($('.searchPublishedDate').val() !== ''){
                    d.startPublishedDate = $('.searchPublishedDate').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    d.endPublishedDate = $('.searchPublishedDate').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    d.startPublishedDate = null;
                    d.endPublishedDate = null;
                }
                //d.publishedDate = null;

                d.owner_name = $('.searchOwner').val();
                d.status = $('.searchStatus :selected').text() == 'All' ? '' : $('.searchStatus :selected').text();
                d.refno = $('.searchRefno').val();
                d.property_for = $('.searchPropertyFor :selected').text() == 'All' ? '' : $('.searchPropertyFor :selected').text();
                d.property_type = $('.searchPropertyType :selected').text() == 'All' ? '' : $('.searchPropertyType :selected').text();

                d.unit_no = $('.searchUnitNo').val();
                d.community = $('.searchCommunity :selected').text() == 'All' ? '' : $('.searchCommunity :selected').text();
                d.sub_community = $('.searchSubCommunity :selected').text() == 'All' ? '' : $('.searchSubCommunity :selected').text();
                d.tower = $('.searchTower :selected').text() == 'All' ? '' : $('.searchTower :selected').text();
                d.portal = $('.searchPortal :selected').text() == 'All' ? '' : $('.searchPortal :selected').text();
                d.beds = $('.searchBeds').val();
                d.baths = $('.searchBaths').val();
                d.price = $('.searchPrice').val();
                d.bua = $('.searchBUA').val();
                d.rera_permit = $('.searchReraPermit').val();
                d.furnished = $('.searchFurnished :selected').text() == 'All' ? '' : $('.searchFurnished :selected').text();
                d.category = $('.searchCategory :selected').text() == 'All' ? '' : $('.searchCategory :selected').text();
                d.marketing_agent = $('.searchMarketingAgent :selected').text() == 'All' ? '' : $('.searchMarketingAgent :selected').text();
                d.listing_agent = $('.searchExternalAgent :selected').text() == 'All' ? '' : $('.searchExternalAgent :selected').text();
                d.created_by = $('.searchCreatedBy :selected').text() == 'All' ? '' : $('.searchCreatedBy :selected').text();
                d.updated_by = $('.searchUpdatedBy :selected').text() == 'All' ? '' : $('.searchUpdatedBy :selected').text();
                d.project_status = $('.searchProjectStatus :selected').text() == 'All' ? '' : $('.searchProjectStatus :selected').text();
                d.plot_area = $('.searchPlotArea').val();
                d.exclusive = $('.searchExclusive :selected').text() == 'All' ? '' : $('.searchExclusive :selected').text();
                d.hot = $('.searchHot :selected').text() == 'All' ? '' : $('.searchHot :selected').text();
                d.occupancy = $('.searchOccupancy :selected').text() == 'All' ? '' : $('.searchOccupancy :selected').text();
                d.cheques = $('.searchCheques').val();
                d.developer = $('.searchDeveloper :selected').text() == 'All' ? '' : $('.searchDeveloper :selected').text();
            },
            // dataSrc: 'listings',
        },
        rowCallback: function(row, data) {
            if (data.deleted_at !== null) {
                $(row).addClass('bg-light-danger');
            }

            var agentId = data.agent_id;

            if (userRole != 'Super Admin' && loggedInUserId != agentId) {
                $(row).addClass('row-disabled');
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    if (userRole != 'Super Admin' && loggedInUserId != row.agent_id) {
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input type="checkbox" disabled class="item-checkbox form-check-input" value="' + row.id + '"></div>';
                    }
                    else{
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input type="checkbox" class="item-checkbox form-check-input" value="' + row.id + '"></div>';
                    }
                },
                orderable: false,
                searchable: false,
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if owner data exists
                    if (row.owner) {
                        // Customize the content as needed
                        return '<div class="d-flex align-items-center">' +
                            '<div class="symbol symbol-50px symbol-md-70px"><img src="' + row.owner_image + '" alt="' + row.owner.name + '" class="img-fluid rounded"> </div>' +
                            '<div class="ms-3">' +
                            '<div class="fw-bold">' + row.owner.name + '</div>' +
                            '<div> <a class="link" href="mailto:'+ row.owner.email +'">' + row.owner.email + '</a></div>' +
                            '<div> <a class="link" href="tel:'+ row.owner.phone +'">' + row.owner.phone + '</a></div>' +
                            '</div>' +
                            '</div>';
                    } else {
                        // If owner doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['owner']['visible'],
                className: 'w-100px'
            },

            {
                data: null,
                render: function(data, type, row) {
                    var statusName = row.status ? row.status.name : null;
                    
                    if (statusName === 'Available - Published') {
                        statusName = 'Published';
                    }else if (statusName === 'Available - Off-Market') {
                        statusName = 'Off-Market';
                    }

                    if(statusName != null){
                        if(row.status.badge != null){
                            return '<span class="badge badge-primary" style="background: '+ row.status.badge +';">' + statusName + '</span>';
                        }
                        else{
                            return statusName;
                        }
                    }
                    return null;
                    
                },
                visible: columnVisibility['status']['visible'],
            },
            {
                data: null,
                render: function(data, type, row) {
                    var output = row.refno;
                    if (row.external_refno) {
                        output += ' <br> (' + row.external_refno + ')';
                    }
                    return output;
                },
                visible: columnVisibility['refno']['visible'],
            },

            {
                data: 'property_for',
                render: function (data, type, row) {
                    // Capitalize the first letter
                    var capitalizedValue = data.charAt(0).toUpperCase() + data.slice(1);

                    // Return the capitalized value
                    return capitalizedValue;
                },
                visible: columnVisibility['for']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (row.prop_type) {
                        return row.prop_type.name;
                    } else {
                        return null;
                    }
                },
                visible: columnVisibility['type']['visible'],
            },
            {
                data: 'unit_no',
                visible: columnVisibility['unit_no']['visible'],
                render: function (data, type, row) {
                    //console.log(userRole);
                    if (userRole === 'Super Admin') {
                        return data; // Show unit_no for Super admin
                    } else if (loggedInUserId == row.agent_id) {
                        return data; // Show unit_no if logged-in user matches agent_id
                    } else {
                        return '****'; // Show **** for other users
                    }
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    if (row.community) {
                        return row.community.name;
                    } else {
                        return null;
                    }
                },
                visible: columnVisibility['community']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if sub_community data exists
                    if (row.sub_community) {
                        // Display the sub_community name
                        return row.sub_community.name;
                    } else {
                        // If sub_community doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['sub_community']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if tower data exists
                    if (row.tower) {
                        // Display the tower name
                        return row.tower.name;
                    } else {
                        // If tower doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['tower']['visible'],
            },
            { 
                data: 'portals_info', 
                name: 'portal',
                render: function(data, type, full, meta) {
                    return '<div class="symbol-group symbol-hover flex-nowrap">' +
                        data.map(function(portal) {
                            return `<div class="symbol symbol-20px bg-grey p-1 symbol-circle" style="border:3px solid white;" data-bs-toggle="tooltip" title="${portal.name}" data-kt-initialized="1">
                                        <img alt="Logo" src="${portal.portal_logo}" class="symbol-img" style="max-height: 25px; max-width: 25px;">
                                        <span class="d-none">${portal.name}</span>
                                    </div>`;
                        }).join('') +
                        '</div>';
                },
                visible: columnVisibility['portal']['visible'],
            },
            {
                data: 'beds',
                visible: columnVisibility['beds']['visible'],
            },
            {
                data: 'baths',
                visible: columnVisibility['baths']['visible'],
            },
            {
                data: 'price',
                visible: columnVisibility['price']['visible'],
                render: function(data, type, row) {
                    var price = null;
                    if(data){
                        // Format the price with commas
                        price = parseFloat(data).toLocaleString(undefined, { maximumFractionDigits: 2 }) + ' AED';
                    }

                    return price;
                }
            },
            {
                data: 'bua',
                visible: columnVisibility['bua']['visible'],
            },
            {
                data: 'rera_permit',
                visible: columnVisibility['rera_permit']['visible'],
            },
            {
                data: 'furnished',
                visible: columnVisibility['furnished']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if category data exists
                    if (row.category) {
                        // Display the category name
                        return row.category.name;
                    } else {
                        // If category doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['category']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if marketing_agent data exists
                    if (row.marketing_agent) {
                        // Customize the content as needed
                        return '<div class="d-flex align-items-center">' +
                            '<div class=""><img src="' + row.marketing_agent_image + '" alt="' + row.marketing_agent.name + '" class="rounded-circle w-25px h-25px"> </div>' +
                            '<div class="ms-3">' +
                            '<div class="fw-bold fs-8">' + row.marketing_agent.name + '</div>' +
                            '</div>' +
                            '</div>';
                    } else {
                        // If marketing_agent doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['marketing_agent']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if listing_agent data exists
                    if (row.listing_agent) {
                        // Customize the content as needed
                        return '<div class="d-flex align-items-center">' +
                            '<div class=""><img src="' + row.listing_agent_image + '" alt="' + row.listing_agent.name + '" class="rounded-circle w-25px h-25px"> </div>' +
                            '<div class="ms-3">' +
                            '<div class="fw-bold fs-8">' + row.listing_agent.name + '</div>' +
                            '</div>' +
                            '</div>';
                    } else {
                        // If listing_agent doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['listing_agent']['visible'],
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
                visible: columnVisibility['added_on']['visible'],
            },
            {
                data: 'updated_at',
                render: function(data, type, row) {
                    return moment.utc(data).fromNow();
                },
                visible: columnVisibility['last_update']['visible'],
            },
            {
                data: 'published_at',
                render: function(data, type, row) {
                    if(data != null){
                        return moment.utc(data).format('MMMM D, YYYY');
                    }
                    return '';
                },
                visible: columnVisibility['published_on']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if project_status data exists
                    if (row.project_status) {
                        // Display the project status name
                        return row.project_status.name;
                    } else {
                        // If project_status doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['project_status']['visible'],
            },
            {
                data: 'plot_area',
                visible: columnVisibility['plot_area']['visible'],
            },
            {
                data: 'exclusive',
                render: function(data, type, row) {
                    return data == true ? 'Yes' : 'No';
                },
                visible: columnVisibility['exclusive']['visible'],
            },
            {
                data: 'hot',
                render: function(data, type, row) {
                    return data == true ? 'Yes' : 'No';
                },
                visible: columnVisibility['hot']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if occupancy data exists
                    if (row.occupancy) {
                        // Display the occupancy name
                        return row.occupancy.name;
                    } else {
                        // If occupancy doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['occupancy']['visible'],
            },
            {
                data: 'cheques',
                visible: columnVisibility['cheques']['visible'],
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Check if developer data exists
                    if (row.developer) {
                        // Display the developer name
                        return row.developer.name;
                    } else {
                        // If developer doesn't exist, return null
                        return null;
                    }
                },
                visible: columnVisibility['developer']['visible'],
            },
            {
                data: 'id',
                render: function (data, type, row) {
                    var isDeleted = row.deleted_at !== null;
                    var brochureLink = '{{ route('api.brochure') }}' + '/' + row.refno;

                    return '<div class="text-end d-flex">' +
                        '<button class="btn btn-xs mr-1 btn-primary btn-active-primary" ' +
                        (isDeleted ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#editModal" data-action="edit" data-agentid="' + row.agent_id + '" data-id="' + data + '"') +
                        '><i class="fa fa-pencil fs-9"></i></button>' +
                        '<a class="btn btn-xs mr-1 btn-dark btn-active-dark ' + (isDeleted ? "d-none" : "") + '" ' +' href="'+ brochureLink +'" target="_blank"><i class="fa fa-file-pdf fs-9"></i></a>' +
                    '</div>';
                },

                visible: columnVisibility['actions']['visible'],
            },
            {
                data: 'refno',
                visible: columnVisibility['refno2']['visible'],
            },
        ],
        initComplete: function () {
            
            var api = this.api();

            // Your existing code for updating portal counts
            var updatePortalCounts = function () {
                //alert('ook');
                $.ajax({
                    url: '{{ route('listings.portalCounts') }}',
                    method: 'POST',
                    data: {
                        p_for: p_for,
                        archived: archived,

                        startDate: $('.searchDate').val() !== '' ? $('.searchDate').data('daterangepicker').startDate.format('YYYY-MM-DD') : null,
                        endDate: $('.searchDate').val() !== '' ? $('.searchDate').data('daterangepicker').endDate.format('YYYY-MM-DD') : null,

                        startCreatedDate: $('.searchCreateDate').val() !== '' ? $('.searchCreateDate').data('daterangepicker').startDate.format('YYYY-MM-DD') : null,
                        endCreatedDate: $('.searchCreateDate').val() !== '' ? $('.searchCreateDate').data('daterangepicker').endDate.format('YYYY-MM-DD') : null,
                        
                        startPublishedDate: $('.searchPublishedDate').val() !== '' ? $('.searchPublishedDate').data('daterangepicker').startDate.format('YYYY-MM-DD') : null,
                        endPublishedDate: $('.searchPublishedDate').val() !== '' ? $('.searchPublishedDate').data('daterangepicker').endDate.format('YYYY-MM-DD') : null,
                        
                        owner_name: $('.searchOwner').val(),
                        status: $('.searchStatus :selected').text() == 'All' ? '' : $('.searchStatus :selected').text(),
                        refno: $('.searchRefno').val(),
                        property_for: $('.searchPropertyFor :selected').text() == 'All' ? '' : $('.searchPropertyFor :selected').text(),
                        property_type: $('.searchPropertyType :selected').text() == 'All' ? '' : $('.searchPropertyType :selected').text(),

                        unit_no: $('.searchUnitNo').val(),
                        community: $('.searchCommunity :selected').text() == 'All' ? '' : $('.searchCommunity :selected').text(),
                        sub_community: $('.searchSubCommunity :selected').text() == 'All' ? '' : $('.searchSubCommunity :selected').text(),
                        tower: $('.searchTower :selected').text() == 'All' ? '' : $('.searchTower :selected').text(),
                        portal: $('.searchPortal :selected').text() == 'All' ? '' : $('.searchPortal :selected').text(),
                        beds: $('.searchBeds').val(),
                        baths: $('.searchBaths').val(),
                        price: $('.searchPrice').val(),
                        bua: $('.searchBUA').val(),
                        rera_permit: $('.searchReraPermit').val(),
                        furnished: $('.searchFurnished :selected').text() == 'All' ? '' : $('.searchFurnished :selected').text(),
                        category: $('.searchCategory :selected').text() == 'All' ? '' : $('.searchCategory :selected').text(),
                        marketing_agent: $('.searchMarketingAgent :selected').text() == 'All' ? '' : $('.searchMarketingAgent :selected').text(),
                        listing_agent: $('.searchExternalAgent :selected').text() == 'All' ? '' : $('.searchExternalAgent :selected').text(),
                        created_by: $('.searchCreatedBy :selected').text() == 'All' ? '' : $('.searchCreatedBy :selected').text(),
                        updated_by: $('.searchUpdatedBy :selected').text() == 'All' ? '' : $('.searchUpdatedBy :selected').text(),
                        project_status: $('.searchProjectStatus :selected').text() == 'All' ? '' : $('.searchProjectStatus :selected').text(),
                        plot_area: $('.searchPlotArea').val(),
                        exclusive: $('.searchExclusive :selected').text() == 'All' ? '' : $('.searchExclusive :selected').text(),
                        hot: $('.searchHot :selected').text() == 'All' ? '' : $('.searchHot :selected').text(),
                        occupancy: $('.searchOccupancy :selected').text() == 'All' ? '' : $('.searchOccupancy :selected').text(),
                        cheques: $('.searchCheques').val(),
                        developer: $('.searchDeveloper :selected').text() == 'All' ? '' : $('.searchDeveloper :selected').text(),
                    },
                    success: function (portalCounts) {
                        console.log(portalCounts);
                        $('.symbol-group .portalListingsCount').each(function () {
                            var portalName = $(this).closest('.symbol').data('bs-original-title');
                            var count = portalCounts[portalName] || 0;
                            $(this).text(count);
                        });
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr);
                        console.error('Error fetching portal counts:', error);
                    }
                });
            };

            // Your existing code to handle refnoParam
            if (refnoParam) {
                $.ajax({
                    url: '{{ route('listings.searchRefno') }}',
                    method: 'POST',
                    data: {
                        refno: refnoParam,
                        length: api.page.len(),
                    },
                    success: function (response) {
                        if (response && response.record) {

                            var listing_update_perm = $('#listing_update_perm').val();
                            if(listing_update_perm == 'false'){
                                Swal.fire({
                                    title: "Permission Denied",
                                    text: "You don't have the permission to update any listing.",
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

            // Initial portal count update
            updatePortalCounts();

            // Callback to update portal counts after each draw
            api.on('draw.dt', function () {
                updatePortalCounts();
            });
            
            // Use the existing search elements for search
            $('.searchOwner').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchRefno').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchStatus').on('change', function () {
                api.clear().draw();
            });

            $('.searchPropertyFor').on('change', function () {
                api.clear().draw();
            });

            $('.searchPropertyType').on('change', function () {
                api.clear().draw();
            });

            $('.searchUnitNo').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchBeds').on('keyup', function () {
                api.clear().draw();
            });
            $('.searchBaths').on('keyup', function () {
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

            $('.searchPortal').on('change', function () {
                api.clear().draw();
            });

            $('.searchMarketingAgent').on('change', function () {
                api.clear().draw();
            });

            $('.searchExternalAgent').on('change', function () {
                api.clear().draw();
            });

            $('.searchPrice').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchCreatedBy').on('change', function () {
                api.clear().draw();
            });

            $('.searchUpdatedBy').on('change', function () {
                api.clear().draw();
            });

            $('.searchProjectStatus').on('change', function () {
                api.clear().draw();
            });


            $('.searchPlotArea').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchBUA').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchFurnished').on('change', function () {
                api.clear().draw();
            });

            $('.searchExclusive').on('change', function () {
                api.clear().draw();
            });

            $('.searchHot').on('change', function () {
                api.clear().draw();
            });

            $('.searchOccupancy').on('change', function () {
                api.clear().draw();
            });


            $('.searchCheques').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchReraPermit').on('keyup', function () {
                api.clear().draw();
            });

            $('.searchDeveloper').on('change', function () {
                api.clear().draw();
            });

            $('.searchCategory').on('change', function () {
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

            // Clear date filter on cancel
            $('.searchPublishedDate').on('apply.daterangepicker', function (ev, picker) {
                if (picker.chosenLabel == 'Reset') {
                    $(this).val('');
                }
                api.clear().draw();
            });
            $('.searchPublishedDate').on('cancel.daterangepicker', function (ev, picker) {
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
        },
        
    });

    $('.selectAll').on('change', function () {
        // var checkboxes;
        // checkboxes = dataTable.rows().nodes().to$().find('.item-checkbox');

        // checkboxes.prop('checked', this.checked);
        // dataTable.rows().select(this.checked);

        var checkboxes = dataTable.rows().nodes().to$().find('.item-checkbox');
        checkboxes.each(function() {
            // Check if the checkbox is disabled
            if (!$(this).prop('disabled')) {
                // Set the checked property only if the checkbox is not disabled
                $(this).prop('checked', $('.selectAll').prop('checked'));
            }
        });
        
        // Select rows only if their corresponding checkbox is not disabled
        dataTable.rows().nodes().to$().find('.item-checkbox').each(function() {
            var row = $(this).closest('tr');
            // Check if the checkbox is not disabled
            if (!$(this).prop('disabled')) {
                // Select or deselect the row based on the state of the .selectAll checkbox
                if ($('.selectAll').prop('checked')) {
                    dataTable.row(row).select();
                } else {
                    dataTable.row(row).deselect();
                }
            }
        });

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
        var agentId = button.data('agentid');

        var listing_update_perm = $('#listing_update_perm').val();
        if(listing_update_perm == 'false'){
            Swal.fire({
                title: "Permission Denied",
                text: "You don't have the permission to update any listing.",
                icon: "error",
                showCancelButton: false,
                confirmButtonText: "Ok",
                confirmButtonColor: "#DF405C",
            });
            return false;
        }
        
        //alert(userRole);
        if (userRole != 'Super Admin' && loggedInUserId != agentId) {
            Swal.fire({
                title: "Not Allowed",
                text: 'You are not allowed to edit this listing',
                icon: "warning",
                confirmButtonText: "OK",
                confirmButtonColor: "#dc3545",
            });
            return false;
        }
        
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

        images_gallery_drop_zone.files.forEach(function (file, index) {
            formData.append('images[' + index + ']', file);
        });
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                //console.log(data);
                submitButton.prop('disabled', false).html('Save Listing');

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

                    if (images_gallery_drop_zone) {
                        images_gallery_drop_zone.removeAllFiles();
                    }

                    $('#editModal').modal('hide');

                    if (data.message) {
                        toastr.success(data.message);
                    }
                }
                
            },
            error: function(xhr, status, error) {
                submitButton.prop('disabled', false).html('Save Listing');
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
            text: "Are you sure you want to archive this Listing?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, archive it!",
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
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            Swal.fire({
                text: "Are you sure you want to archive these listings? Write the reason below.",
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
                    performBulkAction('{{ route('listings.bulkDelete') }}', { item_ids: selectedItems, reason: reason });     
                }
            });
        }
    });

    $('#bulkEmailBtn').on('click', function (e) {
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
                title: 'Send selected listings to email',
                html:'<input type="email" id="emailForm" required class="form-control form-control-sm mb-2 kt_tagify text-start" placeholder="Enter email addresses saperated by comma">' +
                    '<input type="text" id="subjectForm" class="form-control form-control-sm mb-2" placeholder="Enter the email subject">' +
                    '<textarea id="formMessage" class="form-control form-control-sm" rows="4" placeholder="write your email message here"></textarea>' +
                    '<p class="mt-3 mb-1 fw-bold text-start">Selected Listings:</p>' +
                    '<div class="d-flex w-100 text-gray-600" id="selectedItemsList"></div>',
                showCancelButton: true,
                confirmButtonText: "Send",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#589CF0",
                cancelButtonColor: "#6c757d",
                width: '40%',
                heightAuto: false,
                customClass: {
                    popup: 'sendEmailSwal'
                },
                preConfirm: function () {
                    // const emailInput = document.getElementById('emailForm');
                    // const emailValue = emailInput.value.trim();
                    // console.log(document.getElementById('emailForm').value.split(','));

                    const emailInput = document.getElementById('emailForm');
                    const emailValue = emailInput.value.trim();
                    const parsedEmails = JSON.parse(emailValue);

                    //console.log(parsedEmails);

                    if (!emailValue) {
                        Swal.showValidationMessage('Email is required');
                    }else {
                        const emails = parsedEmails.map(emailObj => emailObj.value);
                        return {
                            email: emails,
                            subject: document.getElementById('subjectForm').value,
                            message: document.getElementById('formMessage').value
                        };
                    }
                },
                // showLoaderOnConfirm: true, // Show loader while waiting for the response
                // backdrop: true, // Add this line
                // allowOutsideClick: true, // Add this line
                // allowEscapeKey: false,
                didOpen: function () {
                    // Initialize Tagify after the dialog is opened
                    var tagifyInputPrimary = document.getElementById('emailForm');
                    new Tagify(tagifyInputPrimary);
                }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading text and spinner in the popup
                        Swal.fire({
                            title: 'Sending Email...',
                            html: 'Emails being sent. Please wait...',
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
                        performBulkActionEmail('{{ route('listings.bulkSendEmail') }}', { item_ids: selectedItems, formValues: result.value }).then((response) => {
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

    function performBulkActionEmail(url, data) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (response) {
                    //console.log(response);
                    resolve(response); // Resolve the promise with the response data
                },
                error: function (xhr, status, error) {
                    console.log(error);
                    reject(error); // Reject the promise with the error message
                }
            });
        });
    }

    $('#bulkDuplicateBtn').on('click', function (e) {
        e.preventDefault();
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            Swal.fire({
                text: "Are you sure you want to duplicate these listings?",
                icon: "warning",
               // input: 'text',
                showCancelButton: true,
                confirmButtonText: "Yes, do it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    //var reason = result.value;
                    // Perform AJAX request for bulk action
                    performBulkAction('{{ route('listings.bulkDuplicate') }}', { item_ids: selectedItems });     
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
                title: 'Please select the agent below to assign the selected listings.',
                html:
                    '<select id="agentForm" class="form-select form-select-sm form-select-solid border mb-3" placeholder="Select Agent"><option value="">Select Agent</option</select>' +
                    '<p class="mt-3 mb-1 fw-bold text-start">Write the reason below if applicable:</p>' +
                    '<input type="text" id="reasonForm" class="form-control form-control-sm mb-2" placeholder="Enter the reason">' +
                    '<p class="mt-3 mb-1 fw-bold text-start">Selected Listings:</p>' +
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
                    // Initialize Select2 after the dialog is opened
                    // $('#agentForm').select2({
                    //     ajax: {
                    //         url: '{{ route("users.getList") }}',
                    //         dataType: 'json',
                    //         delay: 250,
                    //         processResults: function (data) {
                    //             return {
                    //                 results: data.users.map(function(user) {
                    //                     return {
                    //                         id: user.id,
                    //                         text: user.name,
                    //                     };
                    //                 }),
                    //             };
                    //         },
                    //         cache: true
                    //     },
                    //     dropdownParent: $(".swal2-container"),
                    //     placeholder: 'Select Agent',
                    //     allowClear: true
                    // });
                }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading text and spinner in the popup
                        Swal.fire({
                            title: 'Processing...',
                            html: 'Assigning the listings. Please wait...',
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
                        performBulkActionAssign('{{ route('listings.bulkAssign') }}', { item_ids: selectedItems, formValues: result.value }).then((response) => {
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

    $('.bulkStatusChangeBtn').on('click', function (e) {
        e.preventDefault();
        var status = $(this).data('status');
        var type = $(this).data('type');
        var selectedItems;
        // In DataTable view
        selectedItems = dataTable.rows({ selected: true }).data().toArray().map(function(data) {
            return data.id;
        });

        if (selectedItems.length > 0) {
            Swal.fire({
                text: "Are you sure you want to "+ type +" these listings? Write the reason below.",
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
                    performBulkAction('{{ route('listings.bulkStatusChange') }}', { item_ids: selectedItems, status_id: status, reason: reason});    
                }
            });
            
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
                text: "Are you sure you want to restore these listings?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, do it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Perform AJAX request for bulk action
                    performBulkAction('{{ route('listings.bulkRestore') }}', { item_ids: selectedItems });
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

    function performBulkAction(url, data) {
        //console.log(url);
        //console.log(data);

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
        //console.log(data);
        
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
            if (id === '23') {
                if($(this).val() !== ''){
                    filterValues['startDate'] = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    filterValues['endDate'] = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    filterValues['startDate'] = null;
                    filterValues['endDate'] = null;
                }
            }
            else if (id === '22') {

                if($(this).val() !== ''){
                    filterValues['startCreatedDate'] = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    filterValues['endCreatedDate'] = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    filterValues['startCreatedDate'] = null;
                    filterValues['endCreatedDate'] = null;
                }

                // var startCreatedDate = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                // var endCreatedDate = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                
                // filterValues['startCreatedDate'] = startCreatedDate;
                // filterValues['endCreatedDate'] = endCreatedDate;
            }
            else if (id === '24') {
                if($(this).val() !== ''){
                    filterValues['startPublishedDate'] = $(this).data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
                    filterValues['endPublishedDate'] = $(this).data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');
                }
                else{
                    filterValues['startPublishedDate'] = null;
                    filterValues['endPublishedDate'] = null;
                }

                //filterValues['endPublishedDate'] = endPublishedDate;
            } else {
                switch(id) {
                    case '1':
                        //filterValues['source_name'] = value;
                        filterValues['owner_name'] = value;
                        break;
                    case '2':
                        //filterValues['source_name'] = value;
                        //filterValues['status'] = value;
                        filterValues['status'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '3':
                        //filterValues['source_name'] = value;
                        filterValues['refno'] = value;
                        break;

                    case '4':
                        //filterValues['source_name'] = value;
                        filterValues['property_for'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '5':
                        //filterValues['sub_source_name'] = value;
                        filterValues['property_type'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '6':
                        //filterValues['created_by'] = value;
                        filterValues['unit_no'] = value;
                        break;
                    case '7':
                        //filterValues['upadted_by'] = value;
                        filterValues['community'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '8':
                        //filterValues['upadted_by'] = value;
                        filterValues['sub_community'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '9':
                        //filterValues['upadted_by'] = value;
                        filterValues['tower'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '10':
                        //filterValues['upadted_by'] = value;
                        filterValues['portal'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '11':
                        filterValues['beds'] = value;
                        break;
                    case '12':
                        filterValues['baths'] = value;
                        break;
                    case '13':
                        filterValues['price'] = value;
                        break;
                    case '14':
                        filterValues['bua'] = value;
                        break;
                    case '15':
                        filterValues['rera_permit'] = value;
                        break;
                    case '16':
                        //filterValues['upadted_by'] = value;
                        filterValues['furnished'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '17':
                        //filterValues['upadted_by'] = value;
                        filterValues['category'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '18':
                        //filterValues['upadted_by'] = value;
                        filterValues['marketing_agent'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '19':
                        filterValues['listing_agent'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;

                    case '20':
                        filterValues['created_by'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '21':
                        filterValues['updated_by'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '25':
                        filterValues['project_status'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '26':
                        filterValues['plot_area'] = value;
                        break;
                    case '27':
                        filterValues['exclusive'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '28':
                        filterValues['hot'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;
                    case '29':
                        filterValues['occupancy'] = $(this).text() == 'All' ? '' : $(this).text();
                        break;

                    case '30':
                        filterValues['cheques'] = value;
                        break;

                    case '31':
                        filterValues['developer'] = $(this).text() == 'All' ? '' : $(this).text();
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
            url: '{{ route('listings.export') }}',
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
        ckEditorInstance.setData('');

        $('.selectTwoModal').each(function () {
            $(this).val(null).trigger('change');
        });
        $('.agentSelectModal').val(null).trigger('change');
        //$('.ownerSelectModal').val(null).trigger('change');

        $('#city_id').val($('#city_id option:contains("Dubai")').val()).trigger('change');
        $('#property_for').val($('#property_for option:contains("Sale")').val()).trigger('change');
        $('#category_id').val($('#category_id option:contains("Residential")').val()).trigger('change');
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
            modalTitle.text('Create Listing');
            modalAction = '{{ route('listings.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Listing');
            modalAction = '{{ route('listings.update', ':itemId') }}'.replace(':itemId', itemId);
        }

        tagifyElem.removeTags();
        
        // Set ID in the hidden input
        $('#editId').val(itemId);

        form.attr('action', modalAction);
        
        // Fetch data via AJAX
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('listings.edit', ['listing' => ':itemId']) }}'.replace(':itemId', itemId),
                type: 'GET',
                success: function(data) {
                    var listing = data.listing;

                    populateCommunities(listing.city_id, 'community_id', listing.community_id)
                    .then(function () {
                        return populateSubCommunities(listing.city_id, 'sub_community_id', listing.community_id, listing.sub_community_id);
                    })
                    .then(function () {
                        return populateTowers(listing.city_id, 'tower_id', listing.community_id, listing.sub_community_id, listing.tower_id ? listing.tower_id : 'two');
                    })
                    .catch(function(error) {
                        console.error('An error occurred:', error);
                    });

                    $('#status').val(listing.status_id).trigger('change');

                    // Basic Details
                    $('#modalRefNo').text(listing.refno);
                    $('#external_refno').val(listing.external_refno);

                    $('#property_for').val(listing.property_for).trigger('change');
                    $('#category_id').val(listing.category_id).trigger('change');

                    $('#property_type').val(listing.property_type).trigger('change');
                    $('#city_id').val(listing.city_id).trigger('change');

                    $('#property_title').val(listing.title);
                    if (ckEditorInstance) {
                        ckEditorInstance.setData(listing.desc);
                    } else {
                        console.error("CKEditor is not initialized yet.");
                    }

                    $('#agent_id').val(listing.agent_id).trigger('change');
                    $('#marketing_agent_id').val(listing.marketing_agent_id).trigger('change');

                    $('#price').val(listing.price);
                    formatPriceInput();
                    $('#frequency').val(listing.frequency);

                    $('#unit_no').val(listing.unit_no);
                    $('#bua').val(listing.bua);
                    $('#plot_no').val(listing.plot_no);
                    $('#plot_area').val(listing.plot_area);
                    $('#rera_permit').val(listing.rera_permit);
                    $('#parking').val(listing.parking);
                    $('#beds').val(listing.beds);
                    $('#baths').val(listing.baths);
                    $('#furnished').val(listing.furnished).trigger('change');
                    $('#project_status_id').val(listing.project_status_id).trigger('change');
                    $('#owner_id').val(listing.owner_id).trigger('change');
                    // populateOwnerSelect(listing.owner_id);
                    ownerSearch(listing.owner_id);

                    $('#occupancy_id').val(listing.occupancy_id).trigger('change');
                    $('#next_availability_date').val(listing.next_availability_date);
                    $('#cheques').val(listing.cheques);

                    $('#view').val(listing.view);
                    $('#video_link').val(listing.video_link);
                    $('#live_tour_link').val(listing.live_tour_link);
                    $('#developer_id').val(listing.developer_id).trigger('change');
                    $('#cheques').val(listing.cheques);

                    if(listing.lead_gen == true){
                        $('#lead_gen').prop('checked', true);
                    }

                    if(listing.poa == true){
                        $('#poa').prop('checked', true);
                    }

                    // //$('#amenity').val('ok');

                    // Loop through each portal checkbox
                    $('input[name="portals[]"]').each(function() {
                        var portalId = $(this).val();

                        // Check if the portal is in the listing's portals
                        var isPortalSelected = listing.portals.some(function(portal) {
                            return portal.id == portalId;
                        });

                        // If the portal is selected, mark the checkbox as checked
                        if (isPortalSelected) {
                            $(this).prop('checked', true);
                        }
                    });

                    // Display Documents
                    displayDocuments(listing.documents);
                    displayMedia(listing.images);
                    displayNotes(listing.notes);

                    toggleRequiredFields();
                    validateAndUpdateClasses2();

                    // Populate change log

                    var activities = listing.activities;
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

                    tagifyElem.addTags(data.amenityNames);
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
            toggleRequiredFields();
            validateAndUpdateClasses2();
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

        function displayMedia(media) {
            var mediaDiv = $('.media_edit');
            mediaDiv.html('');

            media.forEach(function (image, index) {
                var mediaRow = '<div class="col-md-3 mb-2 bg-light-primary p-2 mb-3 rounded shadow-sm position-relative p-2 media_image" id="media_file'+image.id+'">';
                mediaRow += '<img src="' + image.file_url + '" class="img-fluid h-150px w-100" style="object-fit: cover">';
                mediaRow += '<input type="hidden" name="image_id[]" value="'+image.id+'">';
                mediaRow += '<input type="hidden" class="form-control form-control-sm sort-order" name="sort_order[]" value="' + image.sort_order + '">';
                mediaRow += '<input type="hidden" class="form-control form-control-sm is-floorplan" name="is_floorplan[]" value="' + image.floor_plan + '">';
                mediaRow += '<input type="hidden" class="form-control form-control-sm is-watermark" name="is_watermark[]" value="' + image.watermark + '">';
                mediaRow += '<input type="checkbox" class="form-check-input form-check-input-sm shadow select-image position-absolute" style="top:10px; left:10px;" value="' + image.id + '">';
                mediaRow += '<button type="button" class="btn btn-danger btn-xs ms-auto position-absolute" style="top:10px; right:10px;" onclick="confirmMediaRemoval(' + image.id + ')"><i class="fa fa-trash"></i></button>';
                mediaRow += '</div>';
                mediaDiv.append(mediaRow);
            });
            // Check if all checkboxes are selected
            updateSelectAllText();
            makeImagesSortable();
        }

        window.confirmMediaRemoval = function(index) {
            Swal.fire({
                text: "Are you sure you want to remove this image?",
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
                    $('#media_file' + index).fadeOut(500, function () {
                        $(this).remove();
                    });
                }
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
        // function displayNotes(notes) {
        //     var notesDiv = $('.notesTable');
        //     notesDiv.html('');

        //     notes.forEach(function (note) {

        //         var user_photo;

        //         if (note.created_by_user.photo !== null) {
        //             user_photo = '<?= asset('public/storage') ?>/' + note.created_by_user.photo;
        //         } else {
        //             user_photo = '<?= asset('assets/media/svg/avatars/blank-dark.svg') ?>';
        //         }

        //         var createdAtDate = new Date(note.created_at);

        //         // Format date as "12 July 2022"
        //         var formattedDate = createdAtDate.toLocaleDateString('en-US', {
        //             year: 'numeric',
        //             month: 'long',
        //             day: 'numeric'
        //         });

        //         // Format time as "4:34 PM"
        //         var formattedTime = createdAtDate.toLocaleTimeString('en-US', {
        //             hour: 'numeric',
        //             minute: 'numeric',
        //             hour12: true
        //         });

        //         // Combine formatted date and time
        //         var formattedDateTime = formattedDate + ' ' + formattedTime;
        //         var timeAgo = moment(createdAtDate).fromNow();

        //         var noteRow = $(
        //             '<li class="timeline-item | extra-space noteRow" id="note_' + note.id + '">' +
        //                 '<span class="timeline-item-icon | filled-icon">' +
        //                     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">' +
        //                         '<path fill="none" d="M0 0h24v24H0z" />' +
        //                         '<path fill="currentColor" d="M6.455 19L2 22.5V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6.455zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z" />' +
        //                     '</svg>' +
        //                 '</span>' +
        //                 '<div class="timeline-item-wrapper">' +
        //                     '<div class="timeline-item-description">' +
        //                         '<i class="avatar | small">' +
        //                             '<img class="img-fluid" src="'+user_photo+'" />' +
        //                         '</i>' +
        //                         '<span><span class="fw-bold text-dark">' + note.created_by_user.name + '</span> <span style="font-size:13px;"> on <time datetime="' + note.created_at + '">' + formattedDateTime + ' ('+ timeAgo +')</time></span></span>' +
        //                     '</div>' +
        //                     '<div class="comment">' +
        //                         '<textarea class="form-control noteText bg-light-primary border-0" name="note_values[]" rows="' + note.note.split('\n').length + '" readonly>' + note.note + '</textarea>' +
        //                         '<button class="btn btn-xs btn-light-danger removeButton" type="button" onclick="removeNote(' + note.id + ')"><i class="fa fa-trash"></i></button>' +
        //                     '</div>' +
        //                 '</div>' +
        //             '</li>'
        //         );

        //         notesDiv.append(noteRow);

        //         // Add double-click event to remove readonly
        //         noteRow.find('.noteText').dblclick(function () {
        //             $(this).prop('readonly', false);
        //             $(this).removeClass('border-0');
        //             $(this).removeClass('bg-light-primary');
        //         });

        //         // Add blur event to add readonly
        //         noteRow.find('.noteText').blur(function () {
        //             $(this).addClass('border-0');
        //             $(this).addClass('bg-light-primary');
        //             $(this).prop('readonly', true);
        //         });
        //     });
        // }

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
                                '<span><h6 class="fw-bold text-dark">' + note.created_by_user.name + '</h6> <h6 style="font-size:12px;" class="text-gray-500 fw-bold"> <time datetime="' + note.created_at + '">' + formattedDateTime + ' ('+ timeAgo +')</time></h6></span>' +
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

    //$(document).ready(function () {
        function removeSelectedImages() {
            var selectedImages = $('.select-image:checked');

            Swal.fire({
                text: "Are you sure you want to remove these images?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, remove it!",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    selectedImages.each(function() {
                        var imageId = $(this).val();
                        $('#media_file' + imageId).fadeOut(500, function () {
                            $(this).remove();
                            updateSelectAllText();
                        });
                    });
                }
            });
            
        }

        function confirmWatermark() {
            Swal.fire({
                text: "Are you sure you want to mark selected images as watermark?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, mark as watermark",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#ffc107",
                cancelButtonColor: "#6c757d"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.select-image:checked').each(function() {
                        var imageId = $(this).val();
                        $('#media_file' + imageId + ' .is-watermark').val(true);
                    });
                }
            });
        }

        function selectAllImages() {
            var allCheckboxes = $('.select-image');
            var selectAllButton = $('.selectAllButton');

            if (allCheckboxes.length > 0 && allCheckboxes.length === allCheckboxes.filter(':checked').length) {
                // Unselect all
                allCheckboxes.prop('checked', false);
            } else {
                // Select all
                allCheckboxes.prop('checked', true);
            }

            updateSelectAllText();
        }

        function updateSelectAllText() {
            var selectedImages = $('.select-image:checked');
            var allCheckboxes = $('.select-image');
            var selectAllButton = $('.selectAllButton');
            var removeSelectedImagesButton = $('#removeSelectedImages');

            // Check if all checkboxes are selected
            if (selectedImages.length === allCheckboxes.length) {
                selectAllButton.text('Unselect All');
            } else {
                selectAllButton.text('Select All');
            }

            if (selectedImages.length > 0) {
                removeSelectedImagesButton.show();
            } else {
                removeSelectedImagesButton.hide();
            }
        }

        $('.selectAllButton').on('click', function(){
            selectAllImages();
        });

        $('#removeSelectedImages').on('click', function(){
            removeSelectedImages();
        });

        // Attach a click event handler to checkboxes
        // Attach a click event handler to checkboxes using event delegation on the document
        $(document).on('change', '.select-image', function() {
            updateSelectAllText();
        });

   // });
    
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
    // function addNoteFunction() {
    //     var noteText = $('#note').val();
    //     var notesTable = $('.notesTable');

    //     if (noteText.trim() !== '') {
    //         // Create a unique ID for the note
    //         var noteId = 'note_' + Date.now();

    //         var userName = "{{ auth()->user()->name }}";
    //         var userPhoto = "{{ auth()->user()->profileImage() }}";

    //         // Get the current date and time
    //         var currentDate = new Date();

    //         // Format date as "12 July 2022"
    //         var formattedDate = currentDate.toLocaleDateString('en-US', {
    //             year: 'numeric',
    //             month: 'long',
    //             day: 'numeric'
    //         });

    //         // Format time as "4:34 PM"
    //         var formattedTime = currentDate.toLocaleTimeString('en-US', {
    //             hour: 'numeric',
    //             minute: 'numeric',
    //             hour12: true
    //         });

    //         // Combine formatted date and time
    //         var formattedDateTime = formattedDate + ' ' + formattedTime;
    //         var timeAgo = moment(currentDate).fromNow();

    //         var newRow = $(
    //             '<li class="timeline-item | extra-space noteRow" id="note_' + noteId + '">' +
    //                 '<span class="timeline-item-icon | filled-icon">' +
    //                     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">' +
    //                     '<path fill="none" d="M0 0h24v24H0z" />' +
    //                     '<path fill="currentColor" d="M6.455 19L2 22.5V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6.455zM7 10v2h2v-2H7zm4 0v2h2v-2h-2zm4 0v2h2v-2h-2z" />' +
    //                     '</svg>' +
    //                 '</span>' +
    //                 '<div class="timeline-item-wrapper">' +
    //                     '<div class="timeline-item-description">' +
    //                         '<i class="avatar | small">' +
    //                             '<img class="img-fluid" src="' + userPhoto + '" />' +
    //                         '</i>' +
    //                         '<span><span class="fw-bold text-dark">' + userName + '</span> <span style="font-size:13px;"> on <time datetime="formattedDate">'+formattedDateTime+' ('+ timeAgo +')</time></span></span>' +
    //                     '</div>' +
    //                     '<div class="comment">' +
    //                         '<textarea class="form-control noteText bg-light-primary border-0" name="note_values[]" rows="' + noteText.split('\n').length + '" readonly>' + noteText + '</textarea>' +
    //                         '<button class="btn btn-xs btn-light-danger removeButton" type="button" onclick="removeNote(\'' + noteId + '\')"><i class="fa fa-trash"></i></button>' +
    //                     '</div>' +
    //                 '</div>' +
    //             '</li>'
    //         );

    //         notesTable.prepend(newRow);

    //         // Clear the textarea and disable the button
    //         $('#note').val('');
    //         $('.noteAddBtn').prop('disabled', true);

    //         // Apply animations
    //         newRow.hide().fadeIn(500);
    //         attachNoteEvents(newRow);
    //     }
    // }

    // function attachNoteEvents(noteRow) {
    //     var noteText = noteRow.find('.noteText');

    //     // Double-click event to remove readonly
    //     noteText.on('dblclick', function () {
    //         $(this).prop('readonly', false);
    //         $(this).removeClass('border-0');
    //         $(this).removeClass('bg-light-primary');
    //     });

    //     // Blur event to add readonly and focus on interaction
    //     noteText.on('blur', function () {
    //         $(this).addClass('border-0');
    //         $(this).addClass('bg-light-primary');
    //         $(this).prop('readonly', true);
    //     });
    // }

    $('#eventType').on('change', function () {
        if($(this).val() == 'note'){
            $('#event_date').val(null);
            $('#event_date').prop('disabled', true);
        }
        else{
            $('#event_date').prop('disabled', false);
        }
    });

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

    // const optionFormatSelect = (item) => {
    //     if (!item.id) {
    //         return item.text;
    //     }

    //     var span = document.createElement('span');
    //     var template = '';

    //     template += '<div class="d-flex align-items-center">';
    //     //template += '<img src="' + item.element.getAttribute('data-kt-rich-content-icon') + '" class="rounded-circle h-40px w-40px me-3" alt="' + item.text + '"/>';
    //     template += '<div class="d-flex flex-column">'
    //     template += '<span class="fs-6 fw-bold lh-1">' + item.text + '</span>';
    //     template += '<span class="text-dark fs-7 owner-email">' + item.element.getAttribute('data-owner-email') + '</span>';
    //     template += '<span class="text-dark fs-7 owner-phone">' + item.element.getAttribute('data-owner-phone') + '</span>';
    //     template += '</div>';
    //     template += '</div>';

    //     span.innerHTML = template;

    //     return $(span);
    // }

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


    // function populateOwnerSelect(owner_id = null) {
    //     var ownerSelect = $('#owner_id');

    //     // Clear existing options
    //     ownerSelect.empty();

    //     // Initialize select2
    //     ownerSelect.select2({
    //         placeholder: "Select an owner",
    //         dropdownParent: $("#editModal"),
    //         maximumResultsForSearch: 20,
    //         //minimumInputLength: 1,
    //         ajax: {
    //             url: '{{ route('owners.getList') }}',
    //             dataType: 'json',
    //             delay: 250,
    //             data: function (params) {
    //                 if (owner_id !== null && params.term === undefined) {
    //                     // If owner_id is not null and no new search term, return it as data
    //                     return {
    //                         id: owner_id,
    //                     };
    //                 } else {
    //                     // Otherwise, return the search term
    //                     return {
    //                         q: params.term,
    //                     };
    //                 }
    //             },
    //             processResults: function (data, params) {
    //                 return {
    //                     results: data.results,
    //                 };
    //             },

    //             cache: true
    //         },
    //         escapeMarkup: function(markup) {
    //             return markup;
    //         },
    //         templateSelection: optionFormatSelect,
    //         templateResult: optionFormatSelect
    //     });

    //     if (owner_id !== null) {
    //         // ownerSelect.select2('open');
    //         // // ownerSelect.val(owner_id).trigger('change');
    //         // // ownerSelect.val(owner_id).trigger('select2:select');
    //         // var dataaa = $('#owner_id').select2('data');
    //         // console.log(dataaa);
    //         // console.log($('#owner_id').val(owner_id).trigger('change'));
    //         // $('#owner_id').val(owner_id).trigger('change');
    //         // Use the event to ensure the data is loaded

    //         ownerSelect.on('select2:opening', function() {
    //             var dataaa = ownerSelect.select2('data');
    //             console.log(dataaa);
    //             // Set the value and trigger change
    //             ownerSelect.val(owner_id).trigger('change');
    //             // Remove the event listener to avoid unnecessary triggers
    //             ownerSelect.off('select2:opening');
    //         });
    //         // Trigger the dropdown to open, which will eventually set the value
    //         ownerSelect.select2('open');
    //         // ownerSelect.select2('open');
    //         // ownerSelect.on('select2:open', function() {
    //         //     // Set the value and trigger change
    //         //     ownerSelect.val(owner_id).trigger('change');
    //         //     // Remove the event listener to avoid unnecessary triggers
    //         //     ownerSelect.off('select2:open');
    //         //     var dataaa = $('#owner_id').select2('data');
    //         //     console.log(dataaa);
    //         // });
    //         // Trigger the dropdown to open, which will eventually set the value
            
    //     }
        
    // }

    // function populateOwnerSelect(owner_id = null) {
    //     var ownerSelect = $('#owner_id');

    //     // Clear existing options
    //     ownerSelect.empty();

    //     ownerSelect.select2({
    //         placeholder: "Select an owner",
    //         dropdownParent: $("#editModal"),
    //         ajax: {
    //             url: '{{ route('owners.getList') }}',
    //             dataType: 'json',
    //             //delay: 250,
    //             data: function (params) {
    //                 if (owner_id !== null && params.term === undefined) {
    //                     // If owner_id is not null and no new search term, return it as data
    //                     return {
    //                         id: owner_id,
    //                     };
    //                 } else {
    //                     // Otherwise, return the search term
    //                     return {
    //                         q: params.term,
    //                     };
    //                 }
    //             },
    //             processResults: function (data) {
    //                 return {
    //                     results: data.results,
    //                 };
    //             },
    //             cache: true
    //         },
    //         templateSelection: optionFormatSelect,
    //         templateResult: optionFormatSelect
    //     });

    //     // If owner_id is not null, fetch owner details and set it as selected
    //     if (owner_id !== null) {
    //         $.ajax({
    //             url: '{{ route('owners.getList') }}',
    //             method: 'GET',
    //             data: { id: owner_id },
    //             dataType: 'json',
    //             success: function(data) {
    //                 // if (data.results.length === 1) {
    //                 //     var selectedOwner = data.results[0]['id'];
    //                 //     console.log(selectedOwner);

    //                 //     // Set the data for select2 (this will update the displayed text)
    //                 //     ownerSelect.val(selectedOwner).trigger('change');
    //                 // }
    //                 if (data.results.length === 1) {
    //                         var selectedOwner = data.results[0]['id'];
    //                         console.log(selectedOwner);

    //                         // Set the data for select2 (this will update the displayed text)
    //                         ownerSelect.val(selectedOwner).trigger('change');

    //                         // Use setTimeout to ensure that the change event is triggered after initialization
    //                         setTimeout(function() {
    //                             ownerSelect.trigger('change');
    //                         }, 0);
    //                     }
    //             }
    //         });
    //     }
    // }


    // function populateOwnerSelect(owner_id = null) {
    //     var ownerSelect = $('#owner_id');

    //     // Clear existing options
    //     ownerSelect.empty();

    //     ownerSelect.select2({
    //         placeholder: "Select an owner",
    //         //minimumInputLength: 1,
    //         dropdownParent: $("#editModal"),
    //         ajax: {
    //             url: '{{ route('owners.getList') }}',
    //             dataType: 'json',
    //             delay: 250,
    //             data: function (params) {
    //                 if (owner_id !== null) {
    //                     return {
    //                         id: owner_id,
    //                     };
    //                 } else {
    //                     return {
    //                         q: params.term,
    //                     };
    //                 }
    //             },
    //             // processResults: function (data) {
    //             //     return {
    //             //         results: data.results,
    //             //     };
    //             // },
    //             processResults: function (data) {
    //                 return {
    //                     results: data.results,
    //                 };
    //             },
    //             cache: true
    //         },
    //         templateSelection: optionFormatSelect,
    //         templateResult: optionFormatSelect
    //     });

    //     // // Manually trigger selection if owner_id is not null
    //     // if (owner_id !== null) {
    //     //     // Fetch the owner using ownerId
    //     //     $.ajax({
    //     //         url: '{{ route('owners.getList') }}',
    //     //         method: 'GET',
    //     //         data: { id: owner_id },
    //     //         dataType: 'json',
    //     //         success: function(data) {
    //     //             // Check if there is one result
    //     //             if (data.results.length === 1) {
    //     //                 // Set the selected owner
    //     //                 ownerSelect.val(data.results[0].id).trigger('change');
    //     //             }
    //     //         }
    //     //     });
    //     // }
    // }


    

    // Define a function to populate the select2 dropdown
    // function populateOwnerSelect() {
    //     // Make an AJAX request to fetch the data
    //     $.ajax({
    //         url: '{{ route('owners.getList') }}',
    //         method: 'GET',
    //         success: function (data) {
    //             var ownersData = data.owners;
    //             var ownerSelect = $('#owner_id');
    //             //ownerSelect.empty();
    //             ownerSelect.append('<option value="">Select Owner</option>');

    //             ownersData.forEach(function (owner) {
    //                 var option = new Option(owner.name, owner.id, false, false);
    //                 //$(option).attr('data-kt-rich-content-icon', owner.profile_image_url);
    //                 $(option).attr('data-owner-email', owner.email);
    //                 $(option).attr('data-owner-phone', owner.phone);

    //                 ownerSelect.append(option);
    //             });

    //             ownerSelect.select2({
    //                 placeholder: "Select an owner",
    //                 minimumInputLength: 1,
    //                 //minimumResultsForSearch: Infinity,
    //                 templateSelection: optionFormatSelect,
    //                 templateResult: optionFormatSelect,
    //                 dropdownParent: $("#editModal")
    //             });
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Error fetching owner data:', error);
    //         }
    //     });
    // }

    


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

    // Call the function on page load
    $(document).ready(function () {
        //populateOwnerSelect();

        $('.agentSelectModal').select2({
            templateSelection: optionFormatAgent,
            templateResult: optionFormatAgent,
            dropdownParent: $("#editModal")
        });

        $('#agent_id').on('change', function () {
            var selectedAgentId = $(this).val();
            var externalAgentValue = $('#marketing_agent_id').val();

            // Check if the external agent doesn't have a value
            if (!externalAgentValue) {
                // Set the selected value and trigger change for external agent select
                $('#marketing_agent_id').val(selectedAgentId).trigger('change');
            }
        });
    });
    

</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>


<script>
    //$(document).ready(function() {
        function ownerSearch(ownerId = null){
            $('#owner_search').typeahead({
                source: function(query, result) {
                    $.ajax({
                        url: '{{ route('owners.getList') }}',
                        method: 'GET',
                        data: ownerId !== null ? { id: ownerId } : { q: query },
                        dataType: 'json',
                        success: function(data) {
                            result(data.results);

                            // If ownerId is not null and there's only one result, select it
                            if (ownerId !== null && data.results.length === 1) {
                                $('#owner_search').typeahead('select', data.results[0]);
                            }
                        }
                    });
                },
                displayText: function(item) {
                    return item.refno + ' - ' + item.email + ' - ' + item.phone;
                },
                afterSelect: function(item) {
                    // Set the selected owner_id to the hidden input
                    $('#owner_id').val(item.id).trigger('change');

                    // Display selected owner details
                    // $('#owner_name').text('Name: ' + item.name);
                    // $('#owner_email').text('Email: ' + item.email);
                    // $('#owner_phone').text('Phone: ' + item.phone);
                    // You may add more details as needed
                },
                matcher: function(item) {
                    // Customize the matching function if needed
                    return true;
                }
            });
        }
        
        // Initial setup
        ownerSearch();

        // Handle clearing the selection
        $('#owner_search').on('input', function() {
            if (!$(this).val()) {
                // Clear the hidden input and details
                $('#owner_id').val('');
                $('#owner_name').text('');
                $('#owner_email').text('');
                $('#owner_phone').text('');
                $('#owner_refno').text('');
                // You may clear more details as needed
            }
        });

        $('#owner_id').on('change', function() {
            if ($(this).val()) { // Check if the value is not empty
                //ownerSearch($(this).val());

                $.ajax({
                    url: '{{ route('owners.getList') }}',
                    method: 'GET',
                    data: { id: $(this).val() },
                    dataType: 'json',
                    success: function(data) {
                        //result(data.results);

                        // If ownerId is not null and there's only one result, select it
                        if (data.results.length === 1) {
                            //$('#owner_search').typeahead('select', data.results[0]);

                            $('#owner_name').text(data.results[0]['name']);
                            $('#owner_email').text(data.results[0]['email']);
                            $('#owner_phone').text(data.results[0]['phone']);
                            $('#owner_refno').text(data.results[0]['refno']);
                        }
                    }
                });
            }
        });
    //});

    $('#addContactModalBtn').click(function() {
      // Do not close the first modal
      $('#addContactModal').modal('show');
    });


    $(document).ready(function () {
        var contactForm = $('#contactAddForm');

        contactForm.submit(function (e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route('owners.storeAjax') }}',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    // Check if the response contains an error
                    if (response.error) {
                        console.error(response.error);
                        toastr.error(response.error);
                        // Handle the error as needed (showing an alert, logging, etc.)
                    } else {
                        toastr.success(response.success);
                        contactForm[0].reset();
                        $('#addContactModal').modal('hide');
                        var ownerId = response.ownerId;
                        console.log('Owner ID:', ownerId);
                        $('#owner_id').val(ownerId).trigger('change');
                    }
                },
                error: function (xhr, status, error) {
                    console.error(error);
                    toastr.error(error);

                    console.error('XHR Response:', xhr);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    // Handle the error as needed (showing an alert, logging, etc.)
                }
            });
        });
    });

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

    function ucwords (str) {
        return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
            return $1.toUpperCase();
        });
    }


</script>


@endsection
