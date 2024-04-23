@extends('layouts.app')

@section('content')
@php $userRole = Auth::user()->getRoleNames()->first(); @endphp
<div class="d-flex flex-wrap flex-sm-nowrap mb-6 mt-4">
    <style>
        @media print {
            body * {
                visibility: hidden; // part to hide at the time of print
                -webkit-print-color-adjust: exact !important; // not necessary use if colors not visible
            }
        }
    </style>
    
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center">
                    <a href="#" class="text-gray-800 text-hover-primary fs-2 fw-bold me-3">Quick Report For <span class="agent_name"></span></a>
                </div>
            </div>
            <div class="d-flex">
                <a href="#" class="btn btn-sm btn-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="fa fa-filter fs-5 text-white me-1"></i>               
                    Filter
                </a>

                <button type="button" href="#" class="btn btn-sm btn-dark me-3" onclick="printContent();">
                    <i class="fa fa-print fs-5 text-white me-1"></i>               
                    Print
                </button>

                <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_65c48b275588b">
                    <div class="px-7 py-5">
                        <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Date:</label>
                            <div class="btn btn-sm fw-bold btn-secondary d-flex justify-content-between px-4">
                                <div class="text-gray-600 fw-bold dateFilter w-100 text-start" data-kt-daterangepicker-opens="left">
                                    Loading date range...
                                </div>
                                <i class="ki-duotone ki-calendar-8 fs-2 ms-2 me-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                            </div>
                            <input type="hidden" class="startDate" value="{{ now()->format('Y-m-d') }}">
                            <input type="hidden" class="endDate" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        @if($userRole == 'Super Admin' || auth()->user()->is_teamleader == true)
                            <!-- <div class="mb-4">
                                <label class="form-label fw-semibold">Team:</label>
                                <div>
                                    <select class="form-select form-select-sm form-select-solid" name="team_id" data-kt-select2="true" data-close-on-select="false" data-placeholder="Select option" data-dropdown-parent="#kt_menu_65c48b275588b" data-allow-clear="true">
                                        <option></option>
                                        <option value="1">Off-Plan</option>
                                        <option value="2">Telesales</option>
                                        <option value="2">Richards Team</option>
                                    </select>
                                </div>
                            </div> -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Agent:</label>
                                <div>
                                    <select class="form-select form-select-solid form-select-sm agentsSelect agent_id" name="agent_id" data-kt-select2="true" data-close-on-select="true" data-placeholder="Select agent" data-dropdown-parent="#kt_menu_65c48b275588b" data-allow-clear="true">
                                        <option value="">All Agents</option>
                                    </select>
                                </div>
                            </div>
                        @endif
            
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-light btn-active-light-primary me-2 resetFilter" data-kt-menu-dismiss="true">Reset</button>
                
                            <button type="submit" class="btn btn-sm btn-primary applyFilterBtn" data-kt-menu-dismiss="true">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--begin::Info-->
        
        <!--end::Info-->
    </div>
    <!--end::Wrapper-->
</div>

<div class="row mt-5" id="printableContent">
    <div class="col-md-4">
        <div class="card bg-none border border-gray-300 border-dashed card-xl-stretch h-100 shadow blockCardsUIOne">
            
            <!--begin::Body-->
            <div class="card-body my-1 pt-5 px-1">
                <div class="justify-content-end ribbon ribbon-start ribbon-clip position-absolute w-100" style="left:0;">
                    <div class="ribbon-label fw-bold">
                        <i class="fa fa-building text-white" style="margin-right:10px;"></i>Listings KPI
                        <span class="ribbon-inner bg-primary"></span>
                    </div>
                </div>
                
                <div class="d-flex gap-1 mt-4 h-100 align-items-center">
                    <div class="w-50 border-right text-center px-4 offMarketKPI">
                        <a href="#" class="card-title fw-bold text-grey fs-5 mb-3 d-block">
                            Off-Market
                        </a>

                        <div class="mb-3">
                            <span class="text-gray-900 fs-1 fw-bold me-2"><i class="fa fa-trophy fs-2" style="margin-right: 10px;"></i><span class="acheived">0</span></span>    
                            <span class="fw-semibold text-muted fs-7">/ Acheived</span>
                        </div>

                        <div class="separator border-gray-200 mb-3"></div>

                        <div class="d-flex justify-content-between w-100 mt-auto mb-1">
                            <span class="fw-bolder fs-8 text-gray-900"><i class="fa-solid fa-crosshairs" style="margin-right:5px;"></i><span class="goal">0</span> to Goal</span>
                            <span class="fw-bold fs-8 text-gray-500 percent">0%</span>
                        </div>
                
                        <div class="progress h-7px bg-danger bg-opacity-50">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="w-50 text-center px-4 publishedKPI">
                        <a href="#" class="card-title fw-bold text-grey fs-5 mb-3 d-block">
                            Published
                        </a>

                        <div class="mb-3">
                            <span class="text-gray-900 fs-1 fw-bold me-2"><i class="fa fa-trophy fs-2" style="margin-right: 10px;"></i><span class="acheived">0</span></span>    
                            <span class="fw-semibold text-muted fs-7">/ Acheived</span>
                        </div>

                        <div class="separator border-gray-200 mb-3"></div>

                        <div class="d-flex justify-content-between w-100 mt-auto mb-1">
                            <span class="fw-bolder fs-8 text-gray-900"><span class="goal">0</span> to Goal</span>
                            <span class="fw-bold fs-8 text-gray-500 percent">0%</span>
                        </div>
                
                        <div class="progress h-7px bg-danger bg-opacity-50">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                
            </div>
            <!--end:: Body-->
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-none border border-gray-300 border-dashed card-xl-stretch h-100 shadow blockCardsUITwo">
            <!--begin::Body-->
            <div class="card-body my-1 pt-5 px-1">

                <div class="justify-content-end ribbon ribbon-start ribbon-clip position-absolute w-100" style="left:0;">
                    <div class="ribbon-label fw-bold">
                        <i class="fa fa-phone text-white" style="margin-right:10px;"></i> Calls KPI
                        <span class="ribbon-inner bg-primary"></span>
                    </div>
                </div>
                
                <div class="d-flex gap-5 mt-4 h-100 align-items-center">
                    <div class="w-100 text-center px-4 callsKPI">
                        <a href="#" class="card-title fw-bold text-grey fs-5 mb-3 d-block">
                            Calls KPI
                        </a>

                        <div class="mb-3">
                            <span class="text-gray-900 fs-1 fw-bold me-2"><i class="fa fa-trophy fs-2" style="margin-right: 10px;"></i> <span class="acheived">0</span></span>    
                            <span class="fw-semibold text-muted fs-7">/ Acheived</span>
                        </div>

                        <div class="separator border-gray-200 mb-3"></div>

                        <div class="d-flex justify-content-between w-100 mt-auto mb-1">
                            <span class="fw-bolder fs-8 text-gray-900"><i class="fa-solid fa-crosshairs" style="margin-right:5px;"></i><span class="goal">0</span> to Goal</span>
                            <span class="fw-bold fs-8 text-gray-500 percent">0%</span>
                        </div>
                
                        <div class="progress h-7px bg-danger bg-opacity-50">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                
            </div>
            <!--end:: Body-->
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-none border border-gray-300 border-dashed card-xl-stretch h-100 shadow blockCardsUIThree">
            <!--begin::Body-->
            <div class="card-body my-1 pt-5 px-5">

                <div class="justify-content-end ribbon ribbon-start ribbon-clip position-absolute w-100" style="left:0;">
                    <div class="ribbon-label fw-bold">
                        <i class="fas fa-bullhorn text-white" style="margin-right:10px;"></i>Leads Report
                        <span class="ribbon-inner bg-primary"></span>
                    </div>
                </div>

                <div class="h-100 d-flex align-items-center">
                    <div class="w-100">

                        <div class="row mt-5">
                            <div class="col-md-6">
                                <div class="d-flex flex-stack">
                                    <a href="#" class="text-dark fw-semibold fs-6 me-2">Not Yet Contacted</a>
                                    
                                    <h3 class="justify-content-end h-auto" id="not_yet_contacted">0</h3>
                                </div>
                                <div class="separator separator-dashed my-1"></div>

                                <div class="d-flex flex-stack">
                                    <a href="#" class="text-dark fw-semibold fs-6 me-2">In Progress</a>
                                    
                                    <h3 class="justify-content-end h-auto" id="leads_inprogress">0</h3>
                                </div>
                                <div class="separator separator-dashed my-1"></div>

                                <div class="d-flex flex-stack">
                                    <a href="#" class="text-dark fw-semibold fs-6 me-2">Attempt to Contact</a>
                                    
                                    <h3 class="justify-content-end h-auto" id="leads_attempt_contact">0</h3>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex flex-stack">
                                    <a href="#" class="text-dark fw-semibold fs-6 me-2">Received</a>
                                    
                                    <h3 class="justify-content-end h-auto" id="leads_receieved">0</h3>
                                </div>
                                <div class="separator separator-dashed my-1"></div>

                                <div class="d-flex flex-stack">
                                    <a href="#" class="text-dark fw-semibold fs-6 me-2">Missed</a>
                                    
                                    <h3 class="justify-content-end h-auto">0</h3>
                                </div>
                                <div class="separator separator-dashed my-1"></div>

                                <div class="d-flex flex-stack">
                                    <a href="#" class="text-dark fw-semibold fs-6 me-2">Balance</a>
                                    
                                    <h3 class="justify-content-end h-auto">0</h3>
                                </div>
                            </div>
                        </div>

                        

                        <!-- <div class="w-100 mt-3">
                            <div class="separator border-gray-200 mb-2"></div>

                            <div class="d-flex justify-content-between w-100 mt-auto mb-1">
                                <span class="fw-bolder fs-8 text-gray-900"><i class="fa-solid fa-crosshairs" style="margin-right:5px;"></i>106 to Goal</span>
                                <span class="fw-bold fs-8 text-gray-500">10%</span>
                            </div>
                    
                            <div class="progress h-7px bg-danger bg-opacity-50">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div> -->
                    </div>
                </div>
                
                
            </div>
            <!--end:: Body-->
        </div>
    </div>

    <div class="col-md-2">
        <div class="card bg-none border border-gray-300 border-dashed card-xl-stretch h-100 shadow blockCardsUIFour">
            <!--begin::Body-->
            <div class="card-body my-1 pt-5 px-4">

                <div class="justify-content-end ribbon ribbon-start ribbon-clip position-absolute w-100" style="left:0;">
                    <div class="ribbon-label fw-bold">
                        <i class="fa fa-tasks text-white" style="margin-right:10px;"></i> Activity
                        <span class="ribbon-inner bg-primary"></span>
                    </div>
                </div>

                <div class="h-100 d-flex align-items-center">
                    <div class="w-100">
                    <div class="d-flex flex-column gap-2 mt-2">
                        <div class="d-flex flex-stack">
                            <a href="#" class="fs-7 fw-bold text-gray-800">Meetings</a>
                            <div class="badge badge-primary" id="meetings_count">0</div>
                        </div>
                        <div class="separator border-gray-200"></div>
                        

                        <div class="d-flex flex-stack">
                            <a href="#" class="fs-7 fw-bold text-gray-800">Viewings</a>
                            <div class="badge badge-primary" id="viewings_count">0</div>
                        </div>
                        <div class="separator border-gray-200"></div>

                        <div class="d-flex flex-stack">
                            <a href="#" class="fs-7 fw-bold text-gray-800">Reminders</a>
                            <div class="badge badge-primary" id="reminders_count">0</div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Stats-->
</div>

<!-- <div class="card blockCardsUI mt-4">
    <div class="card-body pt-9 pb-0">
        <div class="row">
            <div class="col-md-1">
                <div class="d-flex flex-center flex-shrink-0 bg-light rounded h-100px w-100px mb-4">
                    <img class="w-100 shadow-sm rounded h-100" src="{{ Auth::user()->profileImage() }}" alt="image" style="object-fit: cover !important;"/>
                </div>
            </div>
            <div class="col-md-2">
                <div class="d-flex flex-column gap-2 mt-2">
                    <div class="d-flex flex-stack">
                        <a href="#" class="fs-7 fw-bold text-gray-800">Meetings</a>
                        <div class="badge badge-primary" id="meetings_count">0</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    

                    <div class="d-flex flex-stack">
                        <a href="#" class="fs-7 fw-bold text-gray-800">Viewings</a>
                        <div class="badge badge-primary" id="viewings_count">0</div>
                    </div>
                    <div class="separator border-gray-200"></div>

                    <div class="d-flex flex-stack">
                        <a href="#" class="fs-7 fw-bold text-gray-800">Reminders</a>
                        <div class="badge badge-primary" id="reminders_count">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

@include('admin.components.calendar')
@endsection
@section('scripts')


<script>
    

    function datePickerInit(){
        var start = moment();
        var end = moment();

        function cb(start, end) {
            //$(".dateFilter").html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));

            var label;

            if (isToday(start, end)) {
                label = 'Today';
            } else if (isYesterday(start, end)) {
                label = 'Yesterday';
            } else if (isLast7Days(start, end)) {
                label = 'Last 7 Days';
            } else if (isLast30Days(start, end)) {
                label = 'Last 30 Days';
            } else if (isThisMonth(start, end)) {
                label = 'This Month';
            } else if (isLastMonth(start, end)) {
                label = 'Last Month';
            } else if (isAllTimes(start, end)) {
                label = 'All Dates';
            } else {
                label = start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY');
            }

            $('.dateFilter').html(label);
        }

        function isToday(start, end) {
            var today = moment();
            return start.isSame(today, 'day') && end.isSame(today, 'day');
        }

        function isYesterday(start, end) {
            var yesterday = moment().subtract(1, 'days');
            return start.isSame(yesterday, 'day') && end.isSame(yesterday, 'day');
        }

        function isLast7Days(start, end) {
            var last7Days = moment().subtract(6, 'days');
            return start.isSame(last7Days, 'day') && end.isSame(moment(), 'day');
        }

        function isLast30Days(start, end) {
            var last30Days = moment().subtract(29, 'days');
            return start.isSame(last30Days, 'day') && end.isSame(moment(), 'day');
        }

        function isThisMonth(start, end) {
            var startOfMonth = moment().startOf('month');
            var endOfMonth = moment().endOf('month');
            return start.isSame(startOfMonth, 'day') && end.isSame(endOfMonth, 'day');
        }

        function isLastMonth(start, end) {
            var startOfLastMonth = moment().subtract(1, 'month').startOf('month');
            var endOfLastMonth = moment().subtract(1, 'month').endOf('month');
            return start.isSame(startOfLastMonth, 'day') && end.isSame(endOfLastMonth, 'day');
        }

        function isAllTimes(start, end) {
            var allTimesStart = moment('2018-01-01'); // Start date for "All Times"
            return start.isSame(allTimesStart, 'day') && end.isSame(moment(), 'day');
        }

        $(".dateFilter").daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                "Today": [moment(), moment()],
                "Yesterday": [moment().subtract(1, "days"), moment().subtract(1, "days")],
                "Last 7 Days": [moment().subtract(6, "days"), moment()],
                "Last 30 Days": [moment().subtract(29, "days"), moment()],
                "This Month": [moment().startOf("month"), moment().endOf("month")],
                "Last Month": [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")]
            }
        }, cb);

        cb(start, end);

        $('.dateFilter').on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            $('.startDate').val(startDate);
            $('.endDate').val(endDate);
        });
    }

    datePickerInit();

    $("div.daterangepicker").click( function(e) {
        e.stopPropagation();
    });

    function pageLoader(){
        const loadingEl = document.createElement("div");
        document.body.prepend(loadingEl);
        loadingEl.classList.add("page-loader");
        loadingEl.classList.add("flex-column");
        loadingEl.classList.add("bg-dark");
        loadingEl.classList.add("bg-opacity-25");
        loadingEl.innerHTML = `
            <span class="spinner-border text-primary" role="status"></span>
            <span class="text-gray-800 fs-6 fw-semibold mt-5">Loading...</span>
        `;

        // Show page loading
        KTApp.showPageLoading();
    }

    $(document).ready(function() {
        var targetOne = document.querySelector(".blockCardsUIOne");
        var targetTwo = document.querySelector(".blockCardsUITwo");
        var targetThree = document.querySelector(".blockCardsUIThree");
        var targetFour = document.querySelector(".blockCardsUIFour");
        //var blockUI = new KTBlockUI(target);
        var blockUIOne = new KTBlockUI(targetOne, {
            overlayClass: "bg-warning bg-opacity-25",
        });

        var blockUITwo = new KTBlockUI(targetTwo, {
            overlayClass: "bg-warning bg-opacity-25",
        });

        var blockUIThree = new KTBlockUI(targetThree, {
            overlayClass: "bg-warning bg-opacity-25",
        });

        var blockUIFour = new KTBlockUI(targetFour, {
            overlayClass: "bg-warning bg-opacity-25",
        });

        function loadStats(agent_id = null, startDate = null, endDate = null, blockUIOne, blockUITwo, blockUIThree, blockUIFour) {

            blockUIOne.block();
            blockUITwo.block();
            blockUIThree.block();
            blockUIFour.block();

            $.ajax({
                url: '{{ route('dashboardStats') }}',
                method: 'GET',
                dataType: 'json',
                data: { agent_id: agent_id, startDate: startDate,  endDate: endDate},
                success: function(data) {
                    try {
                        if (data.stats.type == 'single') {
                            $('.agent_name').text(data.stats.user.name);
                        } else {
                            $('.agent_name').text(data.stats.user_count + ' Agents');
                        }

                        var off_market_goal = data.stats.off_market_goal;
                        var published_goal = data.stats.published_goal;
                        var calls_goal = data.stats.calls_goal;

                        var leads_not_contacted = data.stats.leads_not_contacted && data.stats.leads_not_contacted != null ? data.stats.leads_not_contacted : 0;
                        var leads_inprogress = data.stats.leads_inprogress && data.stats.leads_inprogress != null ? data.stats.leads_inprogress : 0;
                        var leads_attempt_contact = data.stats.leads_attempt_contact && data.stats.leads_attempt_contact != null ? data.stats.leads_attempt_contact : 0;

                        updateKPI('.offMarketKPI', data.stats.offMarket, off_market_goal);
                        updateKPI('.publishedKPI', data.stats.published, published_goal);
                        updateKPI('.callsKPI', data.stats.calls, calls_goal);

                        $('#not_yet_contacted').text(leads_not_contacted);
                        $('#leads_inprogress').text(leads_inprogress);
                        $('#leads_attempt_contact').text(leads_attempt_contact);

                        $('#leads_receieved').text(leads_not_contacted + leads_inprogress + leads_attempt_contact);

                        $('#meetings_count').text(data.stats.meetings_count);
                        $('#viewings_count').text(data.stats.viewings_count);
                        $('#reminders_count').text(data.stats.reminders_count);
                    } finally {
                        blockUIOne.release();
                        blockUITwo.release();
                        blockUIThree.release();
                        blockUIFour.release();
                    }
                },
                error: function(error) {
                    try {
                        Swal.fire({
                            title: "Error fetching dashboard stats",
                            text: "Contact to support",
                            icon: "warning",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#dc3545",
                        });
                    } finally {
                        blockUIOne.release();
                        blockUITwo.release();
                        blockUIThree.release();
                        blockUIFour.release();
                    }

                }
            });
        }

        function updateKPI(kpiClass, achieved, goal) {
            var $kpi = $(kpiClass);

            var percentage = (achieved / goal) * 100;
            var progressBar = $kpi.find('.progress-bar');
            var progress = $kpi.find('.progress');

            progressBar.attr('style', 'width: ' + percentage + '%');

            if (percentage < 30) {
                progressBar.removeClass('bg-warning bg-success').addClass('bg-danger');
                progress.removeClass('bg-warning bg-success').addClass('bg-danger');
            } else if (percentage >= 30 && percentage < 60) {
                progressBar.removeClass('bg-danger bg-success').addClass('bg-warning');
                progress.removeClass('bg-danger bg-success').addClass('bg-warning');
            } else {
                progressBar.removeClass('bg-danger bg-warning').addClass('bg-success');
                progress.removeClass('bg-danger bg-warning').addClass('bg-success');
            }

            $kpi.find('.percent').text(percentage.toFixed(2) + '%');
            $kpi.find('.goal').text((goal - achieved).toFixed(0));

            $kpi.find('.acheived').text(achieved);
        }

        loadStats(null, $('.startDate').val(), $('.endDate').val(), blockUIOne, blockUITwo, blockUIThree, blockUIFour);

        var optionFormatAgent = function(item) {
            if (!item.id) {
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

        function loadAgents() {
            $.ajax({
                url: '{{ route('users.getTeamUsers') }}',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    var users = data.users;
                    //console.log(users.length);

                    $('.agentsSelect').empty();
                    $('.agentsSelect').append('<option value="">All Agents</option>');
                    users.forEach(function(user) {
                        var option = new Option(user.name, user.id);
                        option.setAttribute('data-kt-select2-user', user.profile_image_url);
                        $('.agentsSelect').append(option);
                    });
                    $('.agentsSelect').select2({
                        templateSelection: optionFormatAgent,
                        templateResult: optionFormatAgent,
                    });
                },
                error: function(error) {
                    console.error('Error fetching team users:', error);
                }
            });
        }

        loadAgents();

        var defaultStartDate = moment();
        var defaultEndDate = moment();

        function resetDateRangeFilter() {
            // Set the start and end dates to the default values
            $(".dateFilter").data("daterangepicker").setStartDate(defaultStartDate);
            $(".dateFilter").data("daterangepicker").setEndDate(defaultEndDate);

            // Update the displayed text
            $(".dateFilter").html('Today');

            $('.startDate').val(defaultStartDate.format('YYYY-MM-DD'));
            $('.endDate').val(defaultEndDate.format('YYYY-MM-DD'));
            
        }

        $('.applyFilterBtn').on('click', function() {
            var agent_id = $('.agent_id').val();
            var startDate = $('.startDate').val();
            var endDate = $('.endDate').val();
            loadStats(agent_id, startDate, endDate, blockUIOne, blockUITwo, blockUIThree, blockUIFour);
        });

        $('.resetFilter').on('click', function(){
            resetDateRangeFilter();
            var startDate = $('.startDate').val();
            var endDate = $('.endDate').val();
            loadStats(null, startDate, endDate, blockUIOne, blockUITwo, blockUIThree, blockUIFour);
            $('.agentsSelect').val(null).trigger('change');
            
        });
    });

</script>

<script>
    function printContent() {
        var printContents = document.getElementById('printableContent').innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    }
</script>
@endsection