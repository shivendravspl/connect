@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Organization Functions</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">Function List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Function Records</h5>
                    <div>
                        <a href="{{ route('org-functions.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap mb-0" id="org-functions-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Function Name</th>
                                    <th>Function Code</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($functions as $function)
                                <tr>
                                    <td>{{ $function->id }}</td>
                                    <td>{{ $function->function_name }}</td>
                                    <td>{{ $function->function_code }}</td>
                                    <td>{{ \Carbon\Carbon::parse($function->effective_date)->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $function->is_active ? 'success' : 'danger' }}">
                                            {{ $function->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $functions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #org-functions-table {
        font-size: 0.82rem;
    }

    #org-functions-table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f3f6f9;
        color: #333;
    }

    #org-functions-table tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
    }

    #org-functions-table .badge {
        font-size: 0.68rem;
        padding: 0.3em 0.6em;
    }

    .card {
        border: none;
        box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
        border-radius: 0.5rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
        padding: 1rem 1.5rem;
    }

    #org-functions-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    #org-functions-table tbody tr {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }

    #org-functions-table tbody tr:hover {
        background-color: rgba(70, 127, 207, 0.05);
    }
</style>
@endpush