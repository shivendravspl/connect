@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Upload Distributor Agreement</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Upload Agreement</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">Application: {{ $application->application_code ?? 'N/A' }}</h4>
                </div>
                <div class="card-body">
                    <div id="upload-message"></div>

                    <form id="agreement-form" action="{{ route('approvals.generate-agreement', $application) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="agreement_file" class="form-label">Upload Agreement (PDF, max 2MB)</label>
                            <input type="file" class="form-control" id="agreement_file" name="agreement_file" accept=".pdf" required>
                            @error('agreement_file')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <h5>Application Details</h5>
                            <p><strong>Territory:</strong> {{ $application->territoryDetail->territory_name ?? 'N/A' }}</p>
                            <p><strong>Legal Name:</strong> {{ $application->entityDetails->establishment_name ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $application->status)) }}</p>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload Agreement</button>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#agreement-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const formData = new FormData(form[0]);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#upload-message').html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.message || 'Agreement uploaded successfully.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `).show();
                    setTimeout(function() {
                        window.location.href = response.next_step?.url || '{{ route("dashboard") }}';
                    }, 1500);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || { message: 'An error occurred while uploading the agreement.' };
                    const errorMessage = Object.values(errors).flat().join('<br>');
                    $('#upload-message').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `).show();
                }
            });
        });
    });
</script>
@endpush