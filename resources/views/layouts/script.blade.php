<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
{{-- toastr js --}}
<!-- ✅ Add jQuery UI below this -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.1.8/b-3.1.2/b-html5-3.1.2/fc-5.0.4/fh-4.0.1/datatables.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Bootstrap Bundle -->
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- SimpleBar -->
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>

<!-- Node Waves -->
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

<!-- Feather Icons -->
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

<!-- Lord Icon -->
<script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App JS -->
<script src="{{ asset('assets/js/app.js') }}"></script>
<script src="https://unpkg.com/tesseract.js@5.1.0/dist/tesseract.min.js"></script>
<script>
    $(document).ready(function() {
        @if(session('success'))
        toastr.success("{{ session('success') }}");
        @endif
    });
</script>