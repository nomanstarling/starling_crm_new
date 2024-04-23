<div id="kt_header_search" class="header-search d-flex align-items-center w-lg-250px menu-dropdown d-none"
    data-kt-search-keypress="false" data-kt-search-min-length="2" data-kt-search-enter="enter"
    data-kt-search-layout="menu" data-kt-search-responsive="lg" data-kt-menu-trigger="auto"
    data-kt-menu-permanent="true" data-kt-menu-placement="bottom-start" data-kt-search="true">

    <!--begin::Tablet and mobile search toggle-->
    <div data-kt-search-element="toggle" class="search-toggle-mobile d-flex d-lg-none align-items-center">
        <div class="d-flex btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px border-0">
            <i class="ki-duotone ki-magnifier fs-1 "><span class="path1"></span><span class="path2"></span></i>
        </div>
    </div>
    <!--end::Tablet and mobile search toggle-->

    <!--begin::Form(use d-none d-lg-block classes for responsive search)-->
    <form data-kt-search-element="form" class="d-none d-lg-block w-100 position-relative mb-2 mb-lg-0 headerSearchForm mt-3" autocomplete="off">
        <input type="hidden">

        <i class="ki-duotone ki-magnifier search-icon fs-2 position-absolute top-50 translate-middle-y ms-4"><span class="path1"></span><span class="path2"></span></i>
        <input type="text" class="form-control headerSearch ps-13 fs-7 h-35px" name="search" value=""
            placeholder="Quick Search" data-kt-search-element="input">
        <span class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-5"
            data-kt-search-element="spinner">
            <span class="spinner-border h-15px w-15px align-middle text-gray-500"></span>
        </span>
        <span
            class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-4"
            data-kt-search-element="clear">
            <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0"><span class="path1"></span><span class="path2"></span></i>
        </span>
    </form>
    <div data-kt-search-element="content"
        class="menu menu-sub menu-sub-dropdown py-7 px-7 overflow-hidden w-300px w-md-350px">
        <div data-kt-search-element="wrapper" class="">
            <div data-kt-search-element="results" class="d-none scroll-y mh-400px">
                <div class="scroll-y mh-200px mh-lg-350px">
                    <h3 class="fs-5 text-muted m-0  pb-5" data-kt-search-element="category-title"> Search Result </h3>

                    <!-- <a href="#" class="d-flex text-gray-900 text-hover-primary align-items-center mb-5">
                        <div class="symbol symbol-40px me-4">
                            <img src="{{ asset('assets/media/avatars/300-6.jpg') }}" alt="">
                        </div>

                        <div class="d-flex flex-column justify-content-start fw-semibold">
                            <span class="fs-6 fw-semibold">Karina Clark</span>
                            <span class="fs-7 fw-semibold text-muted">Marketing Manager</span>
                        </div>
                    </a>

                    <div class="d-flex align-items-center mb-5">
                        <div class="symbol symbol-40px me-4">
                            <span class="symbol-label bg-light">
                                <i class="ki-duotone ki-laptop fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-semibold">2 BR Apartment for Rent</a>
                            <span class="fs-7 text-muted fw-semibold">Ref No# SP-R-453</span>
                        </div>
                    </div> -->


                </div>
                <!--end::Items-->
            </div>

            <div data-kt-search-element="empty" class="text-center d-none">
                <div class="pt-10 pb-10">
                    <i class="ki-duotone ki-search-list fs-4x opacity-50"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </div>
                <div class="pb-15 fw-semibold">
                    <h3 class="text-gray-600 fs-5 mb-2">No result found</h3>
                    <div class="text-muted fs-7">Please try again with a different query</div>
                </div>
            </div>

            <div data-kt-search-element="placeholder" class="text-center">
                <div class="pt-10 pb-10">
                    <i class="ki-duotone ki-search-list fs-4x opacity-50"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </div>
                <div class="pb-15 fw-semibold">
                    <h3 class="text-gray-600 fs-5 mb-2">Hint</h3>
                    <div class="text-muted fs-7">Please write atleast 2 words to get the result</div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Menu-->
</div>