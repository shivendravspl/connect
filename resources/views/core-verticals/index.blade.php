@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Verticals</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">Vertical List</li>
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
                    <h5 class="card-title mb-0">Vertical Records</h5>
                    <div>
                        <a href="{{ route('verticals.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap mb-0" id="verticals-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Vertical Name</th>
                                    <th>Vertical Code</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($verticals as $vertical)
                                <tr>
                                    <td>{{ $vertical->id }}</td>
                                    <td>{{ $vertical->vertical_name }}</td>
                                    <td>{{ $vertical->vertical_code }}</td>
                                    <td>{{ $vertical->effective_date ? \Carbon\Carbon::parse($vertical->effective_date)->format('Y-m-d') : '' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $vertical->is_active ? 'success' : 'danger' }}">
                                            {{ $vertical->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $verticals->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #verticals-table {
        font-size: 0.82rem;
    }

    #verticals-table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f3f6f9;
        color: #333;
    }

    #verticals-table tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
    }

    #verticals-table .badge {
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

    #verticals-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    #verticals-table tbody tr {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }

    #verticals-table tbody tr:hover {
        background-color: rgba(70, 127, 207, 0.05);
    }
</style>
@endpush