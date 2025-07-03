<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Set data-bs-theme from sessionStorage, default to 'light' if not set
        var storedTheme = sessionStorage.getItem("data-bs-theme") || "light";
        document.documentElement.setAttribute("data-bs-theme", storedTheme);
    });
</script>
<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
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