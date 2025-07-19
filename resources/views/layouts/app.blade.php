<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">
@include('layouts.header')

<body>
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>
        <!-- Start right Content here -->
        <div class="main-content">
            <div class="page-content">
                @yield('content')
            </div>
            <!-- End Page-content -->
        </div>
        <!-- end main content-->
        @include('layouts.footer')
    </div>
    @include('layouts.customizer')
    @include('layouts.script')
    @stack('scripts') <!-- Added to include page-specific scripts -->
</body>

</html>