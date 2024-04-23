<!DOCTYPE html>
<html lang="en">

<head>
    <title>Starling Properties</title>
    <meta charset="utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Starling Properties" />
    <meta property="og:url" content="https://starlingproperties.ae" />
    <meta property="og:site_name" content="Starling Properties" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="https://portal.starlingproperties.ae/favicon.ico" />

    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Vendor Stylesheets(used for this page only)-->
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <!--end::Vendor Stylesheets-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link href="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/intlTelInput.css') }}" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/jquery.skeleton.loader@1.2.0/dist/jquery.skeleton.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.css">

    <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.2.0/css/bootstrap-colorpicker.min.css"/>


    @yield('styles')

    <!--end::Global Stylesheets Bundle-->
    @include('layouts.styles')
    <link rel="stylesheet" href="{{ asset('public/vendor/mckenziearts/laravel-notify/css/notify.css') }}">
    <script>
    // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
    if (window.top != window.self) {
        window.top.location.replace(window.self.location.href);
    }
    </script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // var userId = `{{ auth()->user()->id }}`;
        // // Enable pusher logging - don't include this in production
        // Pusher.logToConsole = true;

        // var pusher = new Pusher('abe35d96c69c92213bed', {
        //     cluster: 'ap2'
        // });

        // var channel = pusher.subscribe('user.' + userId);
        // channel.bind('UserUpdated', function(data) {
        //     alert(JSON.stringify(data));
        // });

        // Enable pusher logging - don't include this in production
        
        
        // Pusher.logToConsole = true;

        // var pusher = new Pusher('abe35d96c69c92213bed', {
        //     cluster: 'ap2'
        // });

        // var channel = pusher.subscribe('my-channel');
        //     channel.bind('my-event', function(data) {
        //         console.log("Hi Noman.");
        //         console.log(data);
        //         alert(JSON.stringify(data));
        // });

    </script>
</head>
<!--end::Head-->

<!--begin::Body-->

<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed">
    <!--begin::Theme mode setup on page load-->
    <script>
    var defaultThemeMode = "light";
    var themeMode;

    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }

        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }

        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
    </script>

    <style>
        
        @media (min-width: 770px) {
            .header-fixed .header {
                height: 120px !important;
            }
        }
    </style>

    <div class="d-flex flex-column flex-root">
        <div class="page d-flex flex-row flex-column-fluid">
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                @impersonating($guard = null)
                <div class="container-fluid bg-warning text-center mt-5">
                    You are logged in as user, <a href="{{ route('impersonate.leave') }}" class="mt-4  mb-4 fw-bold">Leave impersonation</a>
                </div>
                @endImpersonating
                <div id="kt_header" class="header ">
                    <div class="container-fluid d-flex flex-stack my-5">
                        <div class="d-flex align-items-center me-5 gap-10">
                            <div class="d-lg-none btn btn-icon btn-active-color-white w-30px h-30px ms-n2 me-3"
                                id="kt_aside_toggle">
                                <i class="ki-duotone ki-abstract-14 fs-2"><span class="path1"></span><span
                                        class="path2"></span></i>
                            </div>
                            <a href="{{ route('dashboard') }}">
                                <img alt="Logo" src="https://portal.starlingproperties.ae/public/assets/images/logo.png"
                                    class="h-25px h-lg-50px" />
                            </a>
                            @include('layouts.search')
                        </div>

                        <div class="d-flex align-items-center flex-shrink-0">

                            <div class="d-flex align-items-center ms-1">
                                <div class="btn btn-icon btn-color-white bg-hover-white bg-hover-opacity-10 w-30px h-30px h-40px w-40px position-relative"
                                    id="kt_drawer_chat_toggle">
                                    <i class="ki-duotone ki-message-text-2 fs-1"><span class="path1"></span><span
                                            class="path2"></span><span class="path3"></span></i>
                                    <span
                                        class="bullet bullet-dot bg-success h-6px w-6px position-absolute translate-middle top-0 start-50 animation-blink"></span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center ms-1">
                                <a href="#"
                                    class="btn btn-icon btn-color-white bg-hover-white bg-hover-opacity-10 w-30px h-30px h-40px w-40px"
                                    data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                                    data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-night-day theme-light-show fs-1"><span
                                            class="path1"></span><span class="path2"></span><span
                                            class="path3"></span><span class="path4"></span><span
                                            class="path5"></span><span class="path6"></span><span
                                            class="path7"></span><span class="path8"></span><span
                                            class="path9"></span><span class="path10"></span></i> <i
                                        class="ki-duotone ki-moon theme-dark-show fs-1"><span class="path1"></span><span
                                            class="path2"></span></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="light">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-night-day fs-2"><span class="path1"></span><span
                                                        class="path2"></span><span class="path3"></span><span
                                                        class="path4"></span><span class="path5"></span><span
                                                        class="path6"></span><span class="path7"></span><span
                                                        class="path8"></span><span class="path9"></span><span
                                                        class="path10"></span></i>
                                            </span>
                                            <span class="menu-title">
                                                Light
                                            </span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="dark">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-moon fs-2"><span class="path1"></span><span
                                                        class="path2"></span></i> </span>
                                            <span class="menu-title">
                                                Dark
                                            </span>
                                        </a>
                                    </div>
                                    <div class="menu-item px-3 my-0">
                                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode"
                                            data-kt-value="system">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-screen fs-2"><span class="path1"></span><span
                                                        class="path2"></span><span class="path3"></span><span
                                                        class="path4"></span></i> </span>
                                            <span class="menu-title">
                                                System
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-icon btn-color-white bg-hover-white bg-hover-opacity-10 h-30px w-30px" data-provide="fullscreen">
                                        <i class="ki-duotone ki-maximize fs-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                    
                                </a>
                            </div>

                            <div class="d-flex align-items-center ms-1" id="kt_header_user_menu_toggle">
                                <div class="btn btn-flex align-items-center bg-hover-white bg-hover-opacity-10 py-2 px-2 px-md-3"
                                    data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                                    data-kt-menu-placement="bottom-end">
                                    <div
                                        class="d-none d-md-flex flex-column align-items-end justify-content-center me-2 me-md-4">
                                        <span
                                            class="text-muted fs-8 fw-semibold lh-1 mb-1">{{ Auth::user()->name }}</span>
                                        <span
                                            class="text-white fs-8 fw-bold lh-1">{{ ucfirst(auth()->user()->getRoleNames()->first()) }}</span>
                                    </div>

                                    <div class="symbol symbol-30px symbol-md-40px">
                                        <img src="{{ Auth::user()->profileImage() }}" alt="image" />
                                    </div>
                                </div>

                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                                    data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <div class="menu-content d-flex align-items-center px-3">
                                            <div class="symbol symbol-50px me-5">
                                                <img alt="Logo" src="{{ auth()->user()->profileImage() }}" />
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold d-flex align-items-center fs-5">
                                                    {{ auth()->user()->name }}
                                                </div>

                                                <a href="mailto:noman.m@starlingproperties.ae"
                                                    class="fw-semibold text-muted text-hover-primary fs-7">{{ auth()->user()->user_name }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="separator my-2"></div>
                                    <div class="menu-item px-5">
                                        <a href="{{ route('profile') }}" class="menu-link px-5">
                                            My Profile
                                        </a>
                                    </div>
                                    <!--end::Menu item-->

                                    <!--begin::Menu item-->
                                    <div class="menu-item px-5">
                                        <a href="#" class="menu-link px-5">
                                            <span class="menu-text">Notifications</span>
                                            <span class="menu-badge">
                                                <span
                                                    class="badge badge-light-danger badge-circle fw-bold fs-7">3</span>
                                            </span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->

                                    <!--begin::Menu item-->
                                    <!-- Blade file -->
                                    <div class="menu-item px-5">
                                        <a href="{{ route('logout') }}" class="menu-link px-5"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Sign Out
                                        </a>
                                    </div>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                    </form>

                                    <!--end::Menu item-->
                                </div>
                                <!--end::User account menu-->
                            </div>
                            <!--end::User -->
                        </div>
                        <!--end::Topbar-->
                    </div>
                    <div class="bg-dark w-100 py-2 d-none d-md-block">
                        <div class="ms-5 ms-md-10 d-flex gap-2">
                            <div>
                                <a href="{{ route('dashboard') }}"
                                    class="btn btn-active-color-white btn-color-white {{ request()->routeIs('dashboard') ? 'btn-primary' : '' }} btn-active-primary py-2 rounded p-0 px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between">
                                    <i class="ki-duotone ki-element-11"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    Dashboard
                                </a>
                            </div>

                            @can('listing_view')
                                <div>
                                    <button type="button"
                                        class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 {{ request()->routeIs('listings.index') ? 'btn-primary' : '' }} px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate"
                                        data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start"
                                        data-kt-menu-offset="0px, 6px">
                                        <span class="d-none d-md-inline"> 
                                            <i class="ki-duotone ki-call"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span></i>
                                            Listings
                                        </span>
                                        <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px"
                                        data-kt-menu="true">

                                        @can('listing_view')
                                            <div class="menu-item px-3 mt-3">
                                                <a href="{{ route('listings.index') }}" class="menu-link px-3">
                                                    All Listings
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="{{ route('listings.index') }}?for=sale" class="menu-link px-3">
                                                    Sales
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="{{ route('listings.index') }}?for=rent" class="menu-link px-3">
                                                    Rentals
                                                </a>
                                            </div>
                                        @endcan

                                        @can('listing_view_archived')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('listings.index') }}?archived=yes" class="menu-link px-3">
                                                    Archived
                                                </a>
                                            </div>
                                        @endcan

                                        @can('listing_import')
                                            <div class="separator mt-3 opacity-75"></div>
                                            <div class="menu-item px-3">
                                                <div class="menu-content px-3 py-3">
                                                    <a class="btn btn-light-primary btn-sm px-4 w-100" href="{{ route('listings.import') }}">
                                                        Import Listings
                                                    </a>
                                                </div>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                            @endcan

                            @canany(['leads_view_all', 'leads_view_unassigned', 'leads_view_active', 'leads_view_closed', 'leads_view_dead', 'leads_import'])
                                <div>
                                    <button type="button"
                                        class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 btn-color-white p-0 px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate"
                                        data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start"
                                        data-kt-menu-offset="0px, 6px">
                                        <span class="d-none d-md-inline"> 
                                            <i class="ki-duotone ki-element-plus"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            Leads
                                        </span>
                                        <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column pt-3 pb-3 menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px"
                                        data-kt-menu="true">
                                        @can('leads_view_all')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('leads.index') }}" class="menu-link px-3 d-flex justify-content-between">
                                                    All Leads <span id="all_leads_count" class="badge badge-primary mx-2">0</span>
                                                </a>
                                            </div>
                                        @endcan

                                        @can('leads_view_unassigned')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('leads.index') }}?type=unassigned" class="menu-link px-3 d-flex justify-content-between">
                                                    Unassigned <span id="unassigned_leads_count" class="badge badge-primary mx-2">0</span>
                                                </a>
                                            </div>
                                        @endcan

                                        @can('leads_view_active')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('leads.index') }}?type=active" class="menu-link px-3 d-flex justify-content-between">
                                                    Active Leads <span id="active_leads_count" class="badge badge-primary mx-2">0</span>
                                                </a>
                                            </div>
                                        @endcan

                                        @can('leads_view_closed')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('leads.index') }}?type=closed" class="menu-link px-3 d-flex justify-content-between">
                                                    Closed Leads <span id="closed_leads_count" class="badge badge-primary mx-2">0</span>
                                                </a>
                                            </div>
                                        @endcan

                                        @can('leads_view_dead')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('leads.index') }}?type=dead" class="menu-link px-3 d-flex justify-content-between">
                                                    Dead Leads <span id="dead_leads_count" class="badge badge-primary mx-2">0</span>
                                                </a>
                                            </div>
                                        @endcan

                                        @if(auth()->user()->user_name == 'noman.m')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('leads.manual') }}?type=dead" class="menu-link px-3 d-flex justify-content-between">
                                                    Process Lead Manually 
                                                </a>
                                            </div>
                                        @endif

                                        @can('leads_import')
                                            <div class="separator mt-3 opacity-75"></div>
                                            <div class="menu-item px-3">
                                                <div class="menu-content px-3 pb-0 pt-3">
                                                    <a class="btn btn-light-primary btn-sm px-4 w-100" href="{{ route('leads.import') }}">
                                                        Import Leads
                                                    </a>
                                                </div>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                            @endcanany

                            @canany(['contacts_view_all', 'contacts_view_buyers', 'contacts_view_sellers', 'contacts_view_tenants', 'contacts_view_landlords'])
                                <div>
                                    <button type="button"
                                        class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 btn-color-white p-0 px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate"
                                        data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start"
                                        data-kt-menu-offset="0px, 6px">
                                        <span class="d-none d-md-inline">
                                            <i class="ki-duotone ki-abstract-25"><span class="path1"></span><span class="path2"></span></i>
                                            Contacts
                                        </span>
                                        <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown py-3 menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px"
                                        data-kt-menu="true">
                                        @can('contacts_view_all')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('contacts.index') }}" class="menu-link px-3">
                                                    All Contacts
                                                </a>
                                            </div>
                                        @endcan

                                        @can('contacts_view_buyers')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('contacts.index') }}?type=buyer" class="menu-link px-3">
                                                    Buyers
                                                </a>
                                            </div>
                                        @endcan

                                        @can('contacts_view_sellers')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('contacts.index') }}?type=seller" class="menu-link px-3">
                                                    Sellers
                                                </a>
                                            </div>
                                        @endcan

                                        @can('contacts_view_tenants')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('contacts.index') }}?type=tenant" class="menu-link px-3">
                                                    Tenants
                                                </a>
                                            </div>
                                        @endcan

                                        @can('contacts_view_landlords')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('contacts.index') }}?type=landlord" class="menu-link px-3">
                                                    Landlords
                                                </a>
                                            </div>
                                        @endcan

                                        @can('contacts_import')
                                            <div class="separator mt-3 opacity-75"></div>
                                            <div class="menu-item px-3">
                                                <div class="menu-content px-3 pt-3 pb-0">
                                                    <a class="btn btn-light-primary btn-sm px-4 w-100" href="#">
                                                        Import Contacts
                                                    </a>
                                                </div>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                            @endcanany

                            @canany(['owners_view', 'owners_import'])
                                <div>
                                    <button type="button"
                                        class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 {{ request()->routeIs('owners.index') ? 'btn-primary' : '' }} p-0 px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate"
                                        data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start"
                                        data-kt-menu-offset="0px, 6px">
                                        <span class="d-none d-md-inline"> 
                                            <i class="ki-duotone ki-address-book"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            Owners
                                        </span>
                                        <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i>
                                    </button>
                                    <div class="menu menu-sub py-3 menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px"
                                        data-kt-menu="true">
                                        @can('owners_view')
                                            <div class="menu-item px-3">
                                                <a href="{{ route('owners.index') }}" class="menu-link px-3">
                                                    All Owners
                                                </a>
                                            </div>
                                        @endcan

                                        @can('owners_import')
                                            <div class="separator mt-3 opacity-75"></div>
                                            <div class="menu-item px-3">
                                                <div class="menu-content px-3 pt-3 pb-0">
                                                    <a class="btn btn-light-primary btn-sm px-4 w-100" href="#">
                                                        Import Owners
                                                    </a>
                                                </div>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                            @endif

                            <div>
                                <button type="button"
                                    class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 p-0 px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate"
                                    data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start"
                                    data-kt-menu-offset="0px, 6px">
                                    <span class="d-none d-md-inline"> <i class="fa fa-chart-simple"
                                            style="margin-right:10px;"></i>Stats</span>
                                    <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i>
                                </button>
                                <div class="menu menu-sub menu-sub-dropdown py-3 menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px"
                                    data-kt-menu="true">
                                    @can('stats_all_calls')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('calls.index') }}" class="menu-link px-3">
                                                All Calls
                                            </a>
                                        </div>
                                    @endcan

                                    @can('stats_team_calls')
                                        <div class="menu-item px-3 mt-3">
                                            <a href="{{ route('stats.calls') }}" class="menu-link px-3">
                                                Team Calls
                                            </a>
                                        </div>
                                    @endcan

                                    <!-- <div class="separator mt-3 opacity-75"></div>
                                    <div class="menu-item px-3">
                                        <div class="menu-content px-3 pt-3 pb-0">
                                            <a class="btn btn-light-primary btn-sm px-4" href="#">
                                                Events
                                            </a>
                                        </div>
                                    </div> -->
                                </div>
                            </div>

                            <button type="button"
                                class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 p-0 px-3 btn-flex align-items-cenrer justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between"
                                data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start">
                                <span class="d-none d-md-inline"> <i class="fa fa-file"
                                        style="margin-right:10px;"></i>Reports</span>
                                <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i>
                            </button>
                            <div>
                                <button type="button"
                                    class="btn btn-active-color-white btn-active-primary rounded btn-color-white py-2 p-0 px-3 btn-flex align-items-center justify-content-center justify-content-md-between align-items-lg-center flex-md-content-between rotate @php if(request()->routeIs(['users.index', 'roles.index', 'communities.index', 'subCommunities.index', 'towers.index', 'settings.index'])) echo 'btn-primary'; @endphp"
                                    data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-start"
                                    data-kt-menu-offset="0px, 6px">
                                    <span class="d-none d-md-inline"> <i class="fa fa-gear"
                                            style="margin-right:10px;"></i>Settings</span>
                                    <i class="ki-duotone ki-down fs-4 ms-2 ms-md-3 me-0"></i>
                                </button>
                                <div class="menu menu-sub menu-sub-dropdown py-3 menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-auto min-w-200px mw-300px"
                                    data-kt-menu="true">
                                    @can('users_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('users.index') }}" class="menu-link px-3">
                                                Users
                                            </a>
                                        </div>
                                    @endcan

                                    @can('teams_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('teams.index') }}" class="menu-link px-3">
                                                Teams
                                            </a>
                                        </div>
                                    @endcan

                                    @can('campaigns_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('campaigns.index') }}" class="menu-link px-3">
                                                Campaigns
                                            </a>
                                        </div>
                                    @endcan

                                    @can('roles_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('roles.index') }}" class="menu-link px-3">
                                                Roles
                                            </a>
                                        </div>
                                    @endcan

                                    @can('roles_permissions_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('permissions.index') }}" class="menu-link px-3">
                                                Permissions
                                            </a>
                                        </div>
                                    @endcan

                                    @can('locations_view')
                                        <div class="menu-item px-3" data-kt-menu-trigger="hover"
                                            data-kt-menu-placement="right-start">
                                            <a href="#" class="menu-link px-3">
                                                <span class="menu-title">Locations</span>
                                                <span class="menu-arrow"></span>
                                            </a>
                                            <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                                <div class="menu-item px-3">
                                                    <a href="" class="menu-link px-3">
                                                        Cities
                                                    </a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('communities.index') }}" class="menu-link px-3">
                                                        Communities
                                                    </a>
                                                </div>

                                                <div class="menu-item px-3">
                                                    <a href="{{ route('subCommunities.index') }}" class="menu-link px-3">
                                                        Sub Communities
                                                    </a>
                                                </div>

                                                <div class="menu-item px-3">
                                                    <a href="{{ route('towers.index') }}" class="menu-link px-3">
                                                        Towers
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan

                                    <div class="menu-item px-3" data-kt-menu-trigger="hover"
                                        data-kt-menu-placement="right-start">
                                        <a href="#" class="menu-link px-3">
                                            <span class="menu-title">Listings</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <div class="menu-sub menu-sub-dropdown w-175px py-4">
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">
                                                    Developers
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="{{ route('propertyCats.index') }}" class="menu-link px-3">
                                                    Property Categories
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="{{ route('propertyTypes.index') }}" class="menu-link px-3">
                                                    Property Types
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="{{ route('occupancies.index') }}" class="menu-link px-3">
                                                    Occupancies
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="{{ route('portals.index') }}" class="menu-link px-3">
                                                    Portals
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="{{ route('sources.index') }}" class="menu-link px-3">
                                                    Sources
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="{{ route('subSources.index') }}" class="menu-link px-3">
                                                    Sub Sources
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="{{ route('amenities.index') }}" class="menu-link px-3">
                                                    Amenities
                                                </a>
                                            </div>

                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">
                                                    Bedrooms
                                                </a>
                                            </div>
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3">
                                                    Bathrooms
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    @can('statuses_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('crmStatuses.index') }}" class="menu-link px-3">
                                                Statuses
                                            </a>
                                        </div>
                                    @endcan

                                    @can('sub_statuses_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('crmSubStatuses.index') }}" class="menu-link px-3">
                                                Sub Statuses
                                            </a>
                                        </div>
                                    @endcan

                                    @can('general_settings_view')
                                        <div class="menu-item px-3">
                                            <a href="{{ route('settings.index') }}" class="menu-link px-3">
                                                General Settings
                                            </a>
                                        </div>
                                    @endcan

                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Header-->
                <!--begin::Content wrapper-->
                <div class="d-flex flex-column-fluid mt-8">
                    <!--begin::Aside-->
                    <div id="kt_aside" class="aside card d-none" data-kt-drawer="true" data-kt-drawer-name="aside"
                        data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
                        data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start"
                        data-kt-drawer-toggle="#kt_aside_toggle">
                        <!--begin::Aside menu-->
                        <div class="aside-menu flex-column-fluid px-4">
                            <!--begin::Aside Menu-->

                            <div class="hover-scroll-overlay-y mh-100 my-5" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="{default: '#kt_aside_footer', lg: '#kt_header, #kt_aside_footer'}" data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu" data-kt-scroll-offset="{default: '5px', lg: '75px'}">
                                <!--begin::Menu-->
                                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_aside_menu" data-kt-menu="true">
                                    <div data-kt-menu-trigger="click" class="menu-item here show menu-accordion">
                                        <a class="menu-link active" href="{{ route('dashboard') }}">
                                            <span class="menu-icon"><i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i></span>
                                            <span class="menu-title">
                                                Dashboard
                                            </span>
                                        </a>
                                    </div>
                                    <div class="menu-item pt-5">
                                        <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Other Resources</span></div>
                                    </div>
                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link"><span class="menu-icon"><i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                            <span class="menu-title">Listings</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('listings.index') }}">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">All Listings</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('listings.index') }}?for=sale">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Sales</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('listings.index') }}?for=rent">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Rentals</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('listings.index') }}?archived=yes">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Archived</span>
                                                </a>
                                            </div>

                                            @can('listing_import')
                                                <div class="menu-item">
                                                    <a class="menu-link" href="{{ route('listings.import') }}">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">Import Listings</span>
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>

                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link"><span class="menu-icon"><i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                            <span class="menu-title">Leads</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">All Leads</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Unassigned</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Active Leads</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Closed Leads</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Dead Leads</span>
                                                </a>
                                            </div>

                                            @can('listing_import')
                                                <div class="menu-item">
                                                    <a class="menu-link" href="#">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">Import Listings</span>
                                                    </a>
                                                </div>
                                            @endcan
                                           
                                        </div>
                                    </div>

                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link"><span class="menu-icon"><i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                            <span class="menu-title">Contacts</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">All Contacts</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Buyers</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Sellers</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Tenants</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Landlords</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Import Contacts</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link"><span class="menu-icon"><i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                            <span class="menu-title">Owners</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('owners.index') }}">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">All Owners</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Import Owners</span>
                                                </a>
                                            </div>

                                        </div>
                                    </div>

                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link"><span class="menu-icon"><i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                            <span class="menu-title">Stats</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('calls.index') }}">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">All Calls</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('stats.calls') }}">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Team Calls</span>
                                                </a>
                                            </div>

                                            <div class="menu-item">
                                                <a class="menu-link" href="#">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">Events</span>
                                                </a>
                                            </div>

                                        </div>
                                    </div>

                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link"><span class="menu-icon"><i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                            <span class="menu-title">Reports</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="{{ route('calls.index') }}">
                                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                    <span class="menu-title">All Reports</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link"><span class="menu-icon"><i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span>
                                            <span class="menu-title">Settings</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        
                                        <div class="menu-sub menu-sub-accordion">
                                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                                <div class="menu-item">
                                                    <a class="menu-link" href="{{ route('users.index') }}">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">Users</span>
                                                    </a>
                                                </div>
                                                <div class="menu-item">
                                                    <a class="menu-link" href="{{ route('teams.index') }}">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">Teams</span>
                                                    </a>
                                                </div>
                                                <div class="menu-item">
                                                    <a class="menu-link" href="{{ route('roles.index') }}">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">Roles & Permissions</span>
                                                    </a>
                                                </div>
                                                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                                    <span class="menu-link">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">Locations</span>
                                                        <span class="menu-arrow"></span>
                                                    </span>
                                                    <div class="menu-sub menu-sub-accordion">
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="#">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Cities</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('communities.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Communities</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('subCommunities.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Sub Communities</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('towers.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Towers</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                                    <span class="menu-link">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">Listings</span>
                                                        <span class="menu-arrow"></span>
                                                    </span>
                                                    <div class="menu-sub menu-sub-accordion">
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="#">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Developers</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('propertyCats.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title"> Property Categories</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('propertyTypes.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Property Types</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('occupancies.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Occupancies</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('portals.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Portals</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('sources.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Sources</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('subSources.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Sub Sources</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="{{ route('amenities.index') }}">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Amenities</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="#">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Bedrooms</span>
                                                            </a>
                                                        </div>
                                                        <div class="menu-item">
                                                            <a class="menu-link" href="#">
                                                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                                <span class="menu-title">Bathrooms</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="menu-item">
                                                    <a class="menu-link" href="{{ route('settings.index') }}">
                                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                                        <span class="menu-title">General Settings</span>
                                                    </a>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Aside-->

                    <!--begin::Container-->
                    <div class="d-flex flex-column flex-column-fluid container-fluid overflow-hidden">

                        <!--begin::Post-->
                        <div class="content flex-column-fluid" id="kt_content">

                            @yield('content')

                        </div>
                        <div class="footer py-4 text-end" id="kt_footer">
                            <div class="text-gray-900 order-2 order-md-1">
                                <span class="text-muted fw-semibold me-1">2024&copy;</span>
                                <a href="https://starlingproperties.ae" target="_blank" class="text-gray-800 text-hover-primary">Starling Properties</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--begin::Chat drawer-->
    <div id="kt_drawer_chat" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="chat"
        data-kt-drawer-activate="true" data-kt-drawer-overlay="true"
        data-kt-drawer-width="{default:'300px', 'md': '500px'}" data-kt-drawer-direction="end"
        data-kt-drawer-toggle="#kt_drawer_chat_toggle" data-kt-drawer-close="#kt_drawer_chat_close">

        <!--begin::Messenger-->
        <div class="card w-100 border-0 rounded-0" id="kt_drawer_chat_messenger">
            <!--begin::Card header-->
            <div class="card-header pe-5" id="kt_drawer_chat_messenger_header">
                <!--begin::Title-->
                <div class="card-title">
                    <!--begin::User-->
                    <div class="d-flex justify-content-center flex-column me-3">
                        <a href="#" class="fs-4 fw-bold text-gray-900 text-hover-primary me-1 mt-1 lh-1">
                            Notifications
                            <span>
                                <span class="badge badge-success badge-circle w-10px h-10px me-1"></span>
                                <span class="fs-7 fw-semibold text-muted">12 Un-Read</span>
                            </span>
                        </a>
                    </div>
                    <!--end::User-->
                </div>
                <!--end::Title-->

                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Menu-->
                    <div class="me-0">
                        <button class="btn btn-sm btn-icon btn-active-color-primary" data-kt-menu-trigger="click"
                            data-kt-menu-placement="bottom-end">
                            <i class="ki-duotone ki-dots-square fs-2"><span class="path1"></span><span
                                    class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                        </button>

                        <!--begin::Menu 3-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3"
                            data-kt-menu="true">

                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3">
                                    Mark All as Read
                                </a>
                            </div>

                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3">
                                    Mark All as Un-Read
                                </a>
                            </div>

                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3">
                                    See All Notifications
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" id="kt_drawer_chat_close">
                        <i class="ki-duotone ki-cross-square fs-2"><span class="path1"></span><span
                                class="path2"></span></i>
                    </div>
                </div>
            </div>

            <!--begin::Card body-->
            <div class="card-body" id="kt_drawer_chat_messenger_body">
                <!--begin::Messages-->
                <div class="scroll-y me-n5 pe-5" data-kt-element="messages" data-kt-scroll="true"
                    data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                    data-kt-scroll-dependencies="#kt_drawer_chat_messenger_header, #kt_drawer_chat_messenger_footer"
                    data-kt-scroll-wrappers="#kt_drawer_chat_messenger_body" data-kt-scroll-offset="0px">

                    <div class="d-flex justify-content-start mb-2 ">
                        <div class="d-flex flex-column align-items-start">
                            <div class="d-flex align-items-center mb-2">
                                <div class="symbol symbol-35px p-2 pb-1 bg-light-primary">
                                    <i class="ki-duotone ki-message-text-2 fs-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-muted fs-7 m-0">2 mins ago</p>
                                    <a href="#" class="fs-7 fw-bold text-gray-900 text-hover-primary me-1 mt-0">You've received a lead.</a>    
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="separator my-2"></div>

                </div>
            </div>
        </div>
    </div>
    <!--end::Chat drawer-->
    <!--end::Drawers-->
    <!--end::Main-->

    <!--end::Engage-->

    <!--begin::Engage modals-->
    <!--begin::Modal - Sitemap-->

    <!--end::Modal - Sitemap-->
    <!--end::Engage modals-->
    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up"><span class="path1"></span><span class="path2"></span></i>
    </div>
    <!--end::Scrolltop-->

    <!-- Bootstrap Modal -->
    <!-- <div class="modal fade" id="sessionTimeoutModal" tabindex="-1" role="dialog" aria-labelledby="sessionTimeoutModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sessionTimeoutModalLabel">Session Timeout</h5>
                </div>
                <div class="modal-body">
                    <p>Your session has timed out. Please <a href="{{ route('login') }}">login again</a>.</p>
                </div>
            </div>
        </div>
    </div> -->

    <!--begin::Javascript-->
    <script>
    var hostUrl = '<?= route('home'); ?>';
    </script>

    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->

    <!--begin::Vendors Javascript(used for this page only)-->
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
    <!-- <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/radar.js"></script> -->
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <!-- <script src="https://cdn.amcharts.com/lib/5/map.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/continentsLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/usaLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZoneAreasLow.js"></script> -->
    <script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>

    <script src = "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.2.0/js/bootstrap-colorpicker.min.js" > </script>
    <!--begin::Custom Javascript(used for this page only)-->

    <!-- <script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script> -->

    <script>
    // $(document).ready(function () {
    //     // Check Laravel session status
    //     $.ajax({
    //         url: '{{ route('check.session') }}', // Replace with your route to check session
    //         type: 'GET',
    //         success: function (data) {
    //             if (data.sessionTimeout) {
    //                 // Show the Bootstrap modal
    //                 $('#sessionTimeoutModal').modal({
    //                     backdrop: 'static', // Prevent closing on backdrop click
    //                     keyboard: false, // Prevent closing with keyboard
    //                 });
    //             }
    //         },
    //         error: function (xhr, status, error) {
    //             console.error(xhr.responseText);
    //         }
    //     });
    // });
    </script>

    <script>
    toastr.options = {
        "closeButton": true,
        "debug": true,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toastr-top-right",
        "preventDuplicates": false,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    </script>


    <!--end::Custom Javascript-->
    <!--end::Javascript-->

    <x-notify::notify />
    <script src="{{ asset('public/vendor/mckenziearts/laravel-notify/js/notify.js') }}"></script>

    <script src="https://intl-tel-input.com/intl-tel-input/js/intlTelInput.js?1706723638591"></script>
    <script>
    $(document).ready(function() {
        $('.selectTwo').select2({});
        $('.selectTwoModal').select2({
            dropdownParent: $("#editModal")
        });


        // function initializeDateRange(class, firstDate){
        //     $("."+class).daterangepicker({
        //         startDate: moment.utc(firstDate, 'YYYY-MM-DD'),
        //         endDate: moment.utc(),
        //         ranges: {
        //             "Today": [moment().startOf('day'), moment().endOf('day')],
        //             "Yesterday": [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
        //             "Last 7 Days": [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
        //             "Last 30 Days": [moment().subtract(29, 'days').startOf('day'), moment().endOf('day')],
        //             "This Month": [moment().startOf('month'), moment().endOf('month')],
        //             "Last Month": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        //         },
        //         format: 'YYYY-MM-DD'
        //     });
        // }
    });

    $('.max_length').each(function() {
        $(this).maxlength({
            warningClass: "badge badge-warning",
            alwaysShow: true,
            limitReachedClass: "badge badge-success",
            separator: ' of ',
            preText: 'You have ',
            postText: ' chars remaining.',
            validate: true
        });
    });


        document.addEventListener('DOMContentLoaded', function() {
            // Get all elements with the class name "phone"
            const phoneInputs = document.querySelectorAll('.phone');

            // Loop through each phone input and initialize intlTelInput
            phoneInputs.forEach(function(inputPhone) {
                window.intlTelInput(inputPhone, {
                    initialCountry: 'ae',
                    //separateDialCode: true,
                    //nationalMode: true,
                    //placeholderNumberType: 'MOBILE',
                    useFullscreenPopup: false,
                    //showSelectedDialCode: true,	
                    utilsScript: "https://intl-tel-input.com/intl-tel-input/js/utils.js"
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3"></script>

    <!-- <script src="https://cdn.jsdelivr.net/npm/jquery.skeleton.loader@1.2.0/dist/jquery.scheletrone.min.js"></script> -->
    @yield('scripts')
    @yield('scripts_component')

    <script>
        //"use strict";var KTAppCalendar=function(){var e,t,n,a,o,r,i,l,d,c,s,m,u,v,f,p,y,D,k,_,b,g,S,h,T,Y,w,x,L,E={id:"",eventName:"",eventDescription:"",eventLocation:"",startDate:"",endDate:"",allDay:!1};const M=()=>{v.innerText="Add a New Event",u.show();const o=f.querySelectorAll('[data-kt-calendar="datepicker"]'),i=f.querySelector("#kt_calendar_datepicker_allday");i.addEventListener("click",(e=>{e.target.checked?o.forEach((e=>{e.classList.add("d-none")})):(l.setDate(E.startDate,!0,"Y-m-d"),o.forEach((e=>{e.classList.remove("d-none")})))})),C(E),D.addEventListener("click",(function(o){o.preventDefault(),p&&p.validate().then((function(o){console.log("validated!"),"Valid"==o?(D.setAttribute("data-kt-indicator","on"),D.disabled=!0,setTimeout((function(){D.removeAttribute("data-kt-indicator"),Swal.fire({text:"New event added to calendar!",icon:"success",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}}).then((function(o){if(o.isConfirmed){u.hide(),D.disabled=!1;let o=!1;i.checked&&(o=!0),0===c.selectedDates.length&&(o=!0);var d=moment(r.selectedDates[0]).format(),s=moment(l.selectedDates[l.selectedDates.length-1]).format();if(!o){const e=moment(r.selectedDates[0]).format("YYYY-MM-DD"),t=e;d=e+"T"+moment(c.selectedDates[0]).format("HH:mm:ss"),s=t+"T"+moment(m.selectedDates[0]).format("HH:mm:ss")}e.addEvent({id:A(),title:t.value,description:n.value,location:a.value,start:d,end:s,allDay:o}),e.render(),f.reset()}}))}),2e3)):Swal.fire({text:"Sorry, looks like there are some errors detected, please try again.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))}))},B=()=>{var e,t,n;w.show(),E.allDay?(e="All Day",t=moment(E.startDate).format("Do MMM, YYYY"),n=moment(E.endDate).format("Do MMM, YYYY")):(e="",t=moment(E.startDate).format("Do MMM, YYYY - h:mm a"),n=moment(E.endDate).format("Do MMM, YYYY - h:mm a")),b.innerText=E.eventName,g.innerText=e,S.innerText=E.eventDescription?E.eventDescription:"--",h.innerText=E.eventLocation?E.eventLocation:"--",T.innerText=t,Y.innerText=n},q=()=>{x.addEventListener("click",(o=>{o.preventDefault(),w.hide(),(()=>{v.innerText="Edit an Event",u.show();const o=f.querySelectorAll('[data-kt-calendar="datepicker"]'),i=f.querySelector("#kt_calendar_datepicker_allday");i.addEventListener("click",(e=>{e.target.checked?o.forEach((e=>{e.classList.add("d-none")})):(l.setDate(E.startDate,!0,"Y-m-d"),o.forEach((e=>{e.classList.remove("d-none")})))})),C(E),D.addEventListener("click",(function(o){o.preventDefault(),p&&p.validate().then((function(o){console.log("validated!"),"Valid"==o?(D.setAttribute("data-kt-indicator","on"),D.disabled=!0,setTimeout((function(){D.removeAttribute("data-kt-indicator"),Swal.fire({text:"New event added to calendar!",icon:"success",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}}).then((function(o){if(o.isConfirmed){u.hide(),D.disabled=!1,e.getEventById(E.id).remove();let o=!1;i.checked&&(o=!0),0===c.selectedDates.length&&(o=!0);var d=moment(r.selectedDates[0]).format(),s=moment(l.selectedDates[l.selectedDates.length-1]).format();if(!o){const e=moment(r.selectedDates[0]).format("YYYY-MM-DD"),t=e;d=e+"T"+moment(c.selectedDates[0]).format("HH:mm:ss"),s=t+"T"+moment(m.selectedDates[0]).format("HH:mm:ss")}e.addEvent({id:A(),title:t.value,description:n.value,location:a.value,start:d,end:s,allDay:o}),e.render(),f.reset()}}))}),2e3)):Swal.fire({text:"Sorry, looks like there are some errors detected, please try again.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))}))})()}))},C=()=>{t.value=E.eventName?E.eventName:"",n.value=E.eventDescription?E.eventDescription:"",a.value=E.eventLocation?E.eventLocation:"",r.setDate(E.startDate,!0,"Y-m-d");const e=E.endDate?E.endDate:moment(E.startDate).format();l.setDate(e,!0,"Y-m-d");const o=f.querySelector("#kt_calendar_datepicker_allday"),i=f.querySelectorAll('[data-kt-calendar="datepicker"]');E.allDay?(o.checked=!0,i.forEach((e=>{e.classList.add("d-none")}))):(c.setDate(E.startDate,!0,"Y-m-d H:i"),m.setDate(E.endDate,!0,"Y-m-d H:i"),l.setDate(E.startDate,!0,"Y-m-d"),o.checked=!1,i.forEach((e=>{e.classList.remove("d-none")})))},N=e=>{E.id=e.id,E.eventName=e.title,E.eventDescription=e.description,E.eventLocation=e.location,E.startDate=e.startStr,E.endDate=e.endStr,E.allDay=e.allDay},A=()=>Date.now().toString()+Math.floor(1e3*Math.random()).toString();return{init:function(){const C=document.getElementById("kt_modal_add_event");f=C.querySelector("#kt_modal_add_event_form"),t=f.querySelector('[name="calendar_event_name"]'),n=f.querySelector('[name="calendar_event_description"]'),a=f.querySelector('[name="calendar_event_location"]'),o=f.querySelector("#kt_calendar_datepicker_start_date"),i=f.querySelector("#kt_calendar_datepicker_end_date"),d=f.querySelector("#kt_calendar_datepicker_start_time"),s=f.querySelector("#kt_calendar_datepicker_end_time"),y=document.querySelector('[data-kt-calendar="add"]'),D=f.querySelector("#kt_modal_add_event_submit"),k=f.querySelector("#kt_modal_add_event_cancel"),_=C.querySelector("#kt_modal_add_event_close"),v=f.querySelector('[data-kt-calendar="title"]'),u=new bootstrap.Modal(C);const H=document.getElementById("kt_modal_view_event");var F,O,I,R,V,P;w=new bootstrap.Modal(H),b=H.querySelector('[data-kt-calendar="event_name"]'),g=H.querySelector('[data-kt-calendar="all_day"]'),S=H.querySelector('[data-kt-calendar="event_description"]'),h=H.querySelector('[data-kt-calendar="event_location"]'),T=H.querySelector('[data-kt-calendar="event_start_date"]'),Y=H.querySelector('[data-kt-calendar="event_end_date"]'),x=H.querySelector("#kt_modal_view_event_edit"),L=H.querySelector("#kt_modal_view_event_delete"),F=document.getElementById("kt_calendar_app"),O=moment().startOf("day"),I=O.format("YYYY-MM"),R=O.clone().subtract(1,"day").format("YYYY-MM-DD"),V=O.format("YYYY-MM-DD"),P=O.clone().add(1,"day").format("YYYY-MM-DD"),(e=new FullCalendar.Calendar(F,{headerToolbar:{left:"prev,next today",center:"title",right:"dayGridMonth,timeGridWeek,timeGridDay"},initialDate:V,navLinks:!0,selectable:!0,selectMirror:!0,select:function(e){N(e),M()},eventClick:function(e){N({id:e.event.id,title:e.event.title,description:e.event.extendedProps.description,location:e.event.extendedProps.location,startStr:e.event.startStr,endStr:e.event.endStr,allDay:e.event.allDay}),B()},editable:!0,dayMaxEvents:!0,events:[{id:A(),title:"All Day Event",start:I+"-01",end:I+"-02",description:"Toto lorem ipsum dolor sit incid idunt ut",className:"border-success bg-success text-inverse-success",location:"Federation Square"},{id:A(),title:"Reporting",start:I+"-14T13:30:00",description:"Lorem ipsum dolor incid idunt ut labore",end:I+"-14T14:30:00",className:"border-warning bg-warning text-inverse-success",location:"Meeting Room 7.03"},{id:A(),title:"Company Trip",start:I+"-02",description:"Lorem ipsum dolor sit tempor incid",end:I+"-03",className:"border-info bg-info text-info-success",location:"Seoul, Korea"},{id:A(),title:"ICT Expo 2021 - Product Release",start:I+"-03",description:"Lorem ipsum dolor sit tempor inci",end:I+"-05",className:"fc-event-light fc-event-solid-primary",location:"Melbourne Exhibition Hall"},{id:A(),title:"Dinner",start:I+"-12",description:"Lorem ipsum dolor sit amet, conse ctetur",end:I+"-13",location:"Squire's Loft"},{id:A(),title:"Repeating Event",start:I+"-09T16:00:00",end:I+"-09T17:00:00",description:"Lorem ipsum dolor sit ncididunt ut labore",className:"fc-event-danger",location:"General Area"},{id:A(),title:"Repeating Event",description:"Lorem ipsum dolor sit amet, labore",start:I+"-16T16:00:00",end:I+"-16T17:00:00",location:"General Area"},{id:A(),title:"Conference",start:R,end:P,description:"Lorem ipsum dolor eius mod tempor labore",className:"fc-event-primary",location:"Conference Hall A"},{id:A(),title:"Meeting",start:V+"T10:30:00",end:V+"T12:30:00",description:"Lorem ipsum dolor eiu idunt ut labore",location:"Meeting Room 11.06"},{id:A(),title:"Lunch",start:V+"T12:00:00",end:V+"T14:00:00",className:"fc-event-info",description:"Lorem ipsum dolor sit amet, ut labore",location:"Cafeteria"},{id:A(),title:"Meeting",start:V+"T14:30:00",end:V+"T15:30:00",className:"fc-event-warning",description:"Lorem ipsum conse ctetur adipi scing",location:"Meeting Room 11.10"},{id:A(),title:"Happy Hour",start:V+"T17:30:00",end:V+"T21:30:00",className:"fc-event-info",description:"Lorem ipsum dolor sit amet, conse ctetur",location:"The English Pub"},{id:A(),title:"Dinner",start:P+"T18:00:00",end:P+"T21:00:00",className:"fc-event-solid-danger fc-event-light",description:"Lorem ipsum dolor sit ctetur adipi scing",location:"New York Steakhouse"},{id:A(),title:"Birthday Party",start:P+"T12:00:00",end:P+"T14:00:00",className:"fc-event-primary",description:"Lorem ipsum dolor sit amet, scing",location:"The English Pub"},{id:A(),title:"Site visit",start:I+"-28",end:I+"-29",className:"fc-event-solid-info fc-event-light",description:"Lorem ipsum dolor sit amet, labore",location:"271, Spring Street"}],datesSet:function(){}})).render(),p=FormValidation.formValidation(f,{fields:{calendar_event_name:{validators:{notEmpty:{message:"Event name is required"}}},calendar_event_start_date:{validators:{notEmpty:{message:"Start date is required"}}},calendar_event_end_date:{validators:{notEmpty:{message:"End date is required"}}}},plugins:{trigger:new FormValidation.plugins.Trigger,bootstrap:new FormValidation.plugins.Bootstrap5({rowSelector:".fv-row",eleInvalidClass:"",eleValidClass:""})}}),r=flatpickr(o,{enableTime:!1,dateFormat:"Y-m-d"}),l=flatpickr(i,{enableTime:!1,dateFormat:"Y-m-d"}),c=flatpickr(d,{enableTime:!0,noCalendar:!0,dateFormat:"H:i"}),m=flatpickr(s,{enableTime:!0,noCalendar:!0,dateFormat:"H:i"}),q(),y.addEventListener("click",(e=>{E={id:"",eventName:"",eventDescription:"",startDate:new Date,endDate:new Date,allDay:!1},M()})),L.addEventListener("click",(t=>{t.preventDefault(),Swal.fire({text:"Are you sure you would like to delete this event?",icon:"warning",showCancelButton:!0,buttonsStyling:!1,confirmButtonText:"Yes, delete it!",cancelButtonText:"No, return",customClass:{confirmButton:"btn btn-primary",cancelButton:"btn btn-active-light"}}).then((function(t){t.value?(e.getEventById(E.id).remove(),w.hide()):"cancel"===t.dismiss&&Swal.fire({text:"Your event was not deleted!.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))})),k.addEventListener("click",(function(e){e.preventDefault(),Swal.fire({text:"Are you sure you would like to cancel?",icon:"warning",showCancelButton:!0,buttonsStyling:!1,confirmButtonText:"Yes, cancel it!",cancelButtonText:"No, return",customClass:{confirmButton:"btn btn-primary",cancelButton:"btn btn-active-light"}}).then((function(e){e.value?(f.reset(),u.hide()):"cancel"===e.dismiss&&Swal.fire({text:"Your form has not been cancelled!.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))})),_.addEventListener("click",(function(e){e.preventDefault(),Swal.fire({text:"Are you sure you would like to cancel?",icon:"warning",showCancelButton:!0,buttonsStyling:!1,confirmButtonText:"Yes, cancel it!",cancelButtonText:"No, return",customClass:{confirmButton:"btn btn-primary",cancelButton:"btn btn-active-light"}}).then((function(e){e.value?(f.reset(),u.hide()):"cancel"===e.dismiss&&Swal.fire({text:"Your form has not been cancelled!.",icon:"error",buttonsStyling:!1,confirmButtonText:"Ok, got it!",customClass:{confirmButton:"btn btn-primary"}})}))})),(e=>{e.addEventListener("hidden.bs.modal",(e=>{p&&p.resetForm(!0)}))})(C)}}}();KTUtil.onDOMContentLoaded((function(){KTAppCalendar.init()}));
    </script>


    <script>
    $(document).ready(function() {

        $.ajax({
            url: '{{ route('getLeadCounts') }}',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var data = data.counts;
                $('#all_leads_count').text(data.all_leads);
                $('#unassigned_leads_count').text(data.unassigned_leads);
                $('#active_leads_count').text(data.active_leads);
                $('#closed_leads_count').text(data.closed_leads);
                $('#dead_leads_count').text(data.dead_leads);
            },
            error: function(error) {
                console.log(error);
            }
        });

        var searchElement = $('[data-kt-search="true"]');
        var resultsElement = searchElement.find('[data-kt-search-element="results"]');
        var spinnerElement = searchElement.find('[data-kt-search-element="spinner"]');
        var clearElement = searchElement.find('[data-kt-search-element="clear"]');
        var inputElement = searchElement.find('[data-kt-search-element="input"]');
        var placeholderElement = searchElement.find('[data-kt-search-element="placeholder"]');
        var emptyElement = searchElement.find('[data-kt-search-element="empty"]');

        function checkSearchInput() {
            if (inputElement.val().trim() === '') {
                placeholderElement.removeClass('d-none');
                emptyElement.addClass('d-none');
                resultsElement.addClass('d-none');
            } else {
                placeholderElement.addClass('d-none');
            }
        }

        checkSearchInput();

        inputElement.on('input', function() {
            checkSearchInput();

            var query = $(this).val().trim();

            // Show spinner
            spinnerElement.removeClass('d-none');

            // Clear previous results
            resultsElement.empty().addClass('d-none');
            emptyElement.addClass('d-none');

            // Make AJAX request
            $.ajax({
                url: '{{ route("searchModule") }}',
                method: 'GET',
                data: {
                    query: query
                },
                success: function(data) {
                    // Hide spinner
                    spinnerElement.addClass('d-none');

                    // Display results or empty message
                    if ((data.users && data.users.length > 0) || (data.owners && data.owners
                            .length > 0) || (data.listings && data.listings.length > 0)) {
                        // Combine user and owner data into a single array
                        var combinedData = [].concat(data.users || [], data.owners || [],
                            data.listings || []);

                        for (var i = 0; i < combinedData.length; i++) {
                            var html_output = null;

                            var result = combinedData[i];

                            if (result.type !== undefined) {
                                if (result.type === 'listings') {
                                    html_output = '<a target="_blank" href="' + result
                                        .link +
                                        '" class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">' +
                                        '<div class="symbol symbol-70px me-4">' +
                                        '<span class="symbol-label bg-light">' +
                                        '<i class="ki-duotone ki-bank fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i>' +
                                        '</span>' +
                                        '</div>' +
                                        '<div class="d-flex flex-column justify-content-start fw-semibold">' +
                                        '<span class="fs-6 fw-semibold">' + result.beds +
                                        ' beds ' + result.property_type + '</span>' +
                                        '<span class="fs-7 fw-semibold text-muted"> for ' +
                                        result.property_for + '</span>' +
                                        '<span class="fs-7 fw-semibold text-muted">Ref No#' +
                                        result.refno + '</span>' +
                                        '<span class="fs-7 fw-semibold text-muted">External Ref# ' +
                                        result.external_refno + '</span>' +
                                        '</div>' +
                                        '</a>';
                                } else {
                                    html_output = '<a target="_blank" href="' + result
                                        .link +
                                        '" class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">' +
                                        '<div class="symbol symbol-70px me-4">' +
                                        '<img src="' + result.profile_photo + '" alt="">' +
                                        '</div>' +
                                        '<div class="d-flex flex-column justify-content-start fw-semibold">' +
                                        '<span class="fs-6 fw-semibold">' + result.name +
                                        '</span>' +
                                        '<span class="fs-7 fw-semibold text-muted">' +
                                        result.email + '</span>' +
                                        '<span class="fs-7 fw-semibold text-muted">' +
                                        result.phone + '</span>' +
                                        '<span class="fs-8 fw-semibold text-muted">' +
                                        result.type + '</span>' +
                                        '</div>' +
                                        '</a>';
                                }
                            }



                            var resultHtml = html_output;
                            resultsElement.append(resultHtml);
                        }
                        resultsElement.removeClass('d-none');
                    } else {
                        // Show empty message
                        emptyElement.removeClass('d-none');
                        resultsElement.addClass('d-none');
                    }
                },
                error: function(error) {
                    console.error('Error during search:', error);
                    // Hide spinner on error
                    spinnerElement.addClass('d-none');
                }
            });
        });

        // Clear search results on input focus
        inputElement.focus(function() {
            resultsElement.empty().addClass('d-none');
            emptyElement.addClass('d-none');
        });

        // Clear input and hide results on clear button click
        clearElement.click(function() {
            inputElement.val('');
            checkSearchInput(); // Check again after clearing
            resultsElement.empty().addClass('d-none');
            emptyElement.addClass('d-none');
        });

        // var tagifyInputPrimary = document.querySelector(".kt_tagify");
        // new Tagify(tagifyInputPrimary);

    });



    /*!
     * screenfull
     * v4.0.0 - 2018-12-15
     * (c) Sindre Sorhus; MIT License
     */

    ! function() {
        "use strict";
        var u = "undefined" != typeof window && void 0 !== window.document ? window.document : {},
            e = "undefined" != typeof module && module.exports,
            t = "undefined" != typeof Element && "ALLOW_KEYBOARD_INPUT" in Element,
            c = function() {
                for (var e, n = [
                        ["requestFullscreen", "exitFullscreen", "fullscreenElement", "fullscreenEnabled",
                            "fullscreenchange", "fullscreenerror"
                        ],
                        ["webkitRequestFullscreen", "webkitExitFullscreen", "webkitFullscreenElement",
                            "webkitFullscreenEnabled", "webkitfullscreenchange", "webkitfullscreenerror"
                        ],
                        ["webkitRequestFullScreen", "webkitCancelFullScreen", "webkitCurrentFullScreenElement",
                            "webkitCancelFullScreen", "webkitfullscreenchange", "webkitfullscreenerror"
                        ],
                        ["mozRequestFullScreen", "mozCancelFullScreen", "mozFullScreenElement",
                            "mozFullScreenEnabled", "mozfullscreenchange", "mozfullscreenerror"
                        ],
                        ["msRequestFullscreen", "msExitFullscreen", "msFullscreenElement", "msFullscreenEnabled",
                            "MSFullscreenChange", "MSFullscreenError"
                        ]
                    ], r = 0, l = n.length, t = {}; r < l; r++)
                    if ((e = n[r]) && e[1] in u) {
                        for (r = 0; r < e.length; r++) t[n[0][r]] = e[r];
                        return t
                    } return !1
            }(),
            l = {
                change: c.fullscreenchange,
                error: c.fullscreenerror
            },
            n = {
                request: function(l) {
                    return new Promise(function(e) {
                        var n = c.requestFullscreen,
                            r = function() {
                                this.off("change", r), e()
                            }.bind(this);
                        l = l || u.documentElement, / Version\/5\.1(?:\.\d+)? Safari\//.test(navigator
                            .userAgent) ? l[n]() : l[n](t ? Element.ALLOW_KEYBOARD_INPUT : {}), this.on(
                            "change", r)
                    }.bind(this))
                },
                exit: function() {
                    return new Promise(function(e) {
                        var n = function() {
                            this.off("change", n), e()
                        }.bind(this);
                        u[c.exitFullscreen](), this.on("change", n)
                    }.bind(this))
                },
                toggle: function(e) {
                    return this.isFullscreen ? this.exit() : this.request(e)
                },
                onchange: function(e) {
                    this.on("change", e)
                },
                onerror: function(e) {
                    this.on("error", e)
                },
                on: function(e, n) {
                    var r = l[e];
                    r && u.addEventListener(r, n, !1)
                },
                off: function(e, n) {
                    var r = l[e];
                    r && u.removeEventListener(r, n, !1)
                },
                raw: c
            };
        c ? (Object.defineProperties(n, {
            isFullscreen: {
                get: function() {
                    return Boolean(u[c.fullscreenElement])
                }
            },
            element: {
                enumerable: !0,
                get: function() {
                    return u[c.fullscreenElement]
                }
            },
            enabled: {
                enumerable: !0,
                get: function() {
                    return Boolean(u[c.fullscreenEnabled])
                }
            }
        }), e ? module.exports = n : window.screenfull = n) : e ? module.exports = !1 : window.screenfull = !1
    }();

    // Fullscreen
    //
    // $(document).on('click', '.box-btn-fullscreen', function(){
    //   $(this).parents('.box').toggleClass('box-fullscreen').removeClass('box-maximize');
    // });

    $(function() {
        'use strict';

        // Function to update elements based on fullscreen state
        function updateElements() {
            $('#kt_header').toggle($('#container')[0]);
            $('.wrapper').toggleClass('pt-5');
        }

        // Toggle fullscreen on button click
        $('[data-provide~="fullscreen"]').on('click', function() {
            screenfull.toggle($('#container')[0]);
            $('#kt_header').toggle($('#container')[0]);
            $('.wrapper').toggleClass('pt-5');
            updateElements();
        });

        // Listen for changes in fullscreen state
        screenfull.on('change', function() {
            updateElements();
        });

        $(function () {
            $('.colorPicker').colorpicker();
        });
    });
    </script>

</body>

</html>