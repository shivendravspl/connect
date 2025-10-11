<!-- resources/views/approvals/show.blade.php -->
@extends('layouts.app')
@push('styles')
    <!-- Reuse the styles from application.show, but override with approvals.show styles for consistency -->
    <style>
        .form-section {
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }

        .table th,
        .table td {
            vertical-align: middle;
            font-size: 0.6rem !important;
            padding: 0.5rem;
        }

        .document-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .document-link a:hover {
            text-decoration: underline;
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .modal-content {
            font-size: 0.8rem;
        }

        .modal-body iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .container {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            h2 {
                font-size: 1.4rem;
            }
            
            h5 {
                font-size: 1.1rem;
            }
            
            .card {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
                border-radius: 0;
            }
            
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .modal-content {
                margin: 0.5rem;
            }
            
            .modal-body {
                padding: 0.75rem;
            }
            
            .modal-footer .btn {
                margin-bottom: 0.5rem;
            }

            .modal-body iframe {
                height: 300px;
            }
        }

        /* Additional styles from approvals.show to ensure consistency */
        .compact-table {
            font-size: 0.85rem;
            line-height: 1.2;
        }
        .compact-table th, .compact-table td {
            padding: 0.5rem;
            vertical-align: middle;
        }
        .compact-table .btn-sm {
            font-size: 0.75rem;
            padding: 0.2rem 0.4rem;
        }
        .compact-table .badge {
            font-size: 0.7rem;
            padding: 0.3rem 0.5rem;
        }
        .modal-body .document-preview {
            max-height: 400px;
            overflow-y: auto;
        }
        .modal-body embed, .modal-body img {
            max-width: 100%;
            height: 400px;
            margin-bottom: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <!-- Include application.show -->
@include('applications.show')
    <!-- Include the Take Action section -->
    @include('approvals.partials.take-action')
@endsection

@push('scripts')
<script>
    document.getElementById('approve-form')?.addEventListener('submit', function() {
        document.querySelectorAll('.form-section button').forEach(button => {
            button.disabled = true;
        });
    });
</script>
@endpush