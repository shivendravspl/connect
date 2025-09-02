@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-sm-3">
    <div class="row justify-content-center">
        <div class="col-12 px-0 px-sm-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                    <h5 class="mb-0 small font-weight-bold">My Applications</h5>
                    @if(auth()->user()->emp_id && auth()->user()->hasAnyRole(['Admin', 'Super Admin','Mis User']))
                    <a href="{{ route('applications.create') }}" class="btn btn-sm btn-primary py-1 px-3 fs-6">
                        <i class="fas fa-plus fa-sm"></i> <span class="d-none d-sm-inline">New</span>
                    </a>
                    @endif
                </div>

                <div class="card-body p-1 p-sm-2">
                    @if($applications->isEmpty())
                    <div class="alert alert-info py-1 px-2 mb-1 small">You haven't submitted any applications yet.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-1 small">
                            <thead class="small">
                                <tr>
                                    <th class="py-1 px-1 text-center">S.No</th>
                                    <th class="py-1 px-1">App ID</th>
                                    <th class="py-1 px-1">Distributor</th>
                                    <th class="py-1 px-1 d-none d-sm-table-cell">Territory</th>
                                    <th class="py-1 px-1">Status</th>
                                    <th class="py-1 px-1 d-none d-md-table-cell">Submitted</th>
                                    <th class="py-1 px-1 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($applications as $index => $application)
                                <tr>
                                    <td class="py-1 px-1 align-middle text-center">
                                        {{ ($applications->currentPage() - 1) * $applications->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="py-1 px-1 align-middle">{{ $application->application_code }}</td>
                                    <td class="py-1 px-1 align-middle">{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td class="py-1 px-1 d-none d-sm-table-cell align-middle">{{ isset($application->territory) ? DB::table('core_territory')->where('id', $application->territory)->value('territory_name') ?? 'N/A' : 'N/A' }}</td>
                                    <td class="py-1 px-1 align-middle">
                                        <span class="badge bg-{{ $application->status_badge }}" style="font-size: 0.65rem;">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </td>
                                    <td class="py-1 px-1 d-none d-md-table-cell align-middle">{{ $application->created_at->format('d-M-Y') }}</td>
                                    <td class="py-1 px-1 align-middle">
                                        <div class="d-flex justify-content-center" style="gap: 0.25rem;">
                                            <!-- View button -->
                                            <a href="{{ route('applications.show', $application) }}" class="btn btn-info btn-action p-0" title="View">
                                                <i class="bx bx-show fs-10 d-flex justify-content-center align-items-center"></i>
                                            </a>

                                            @if(in_array($application->status, ['draft', 'reverted']) && ($application->created_by === Auth::user()->emp_id || auth()->user()->hasAnyRole(['Admin', 'Super Admin','Mis Admin'])))
                                            <!-- Edit button -->
                                            <a href="{{ route('applications.edit', $application) }}" class="btn btn-info btn-action p-0" title="Edit">
                                                <i class="bx bx-pencil fs-10 d-flex justify-content-center align-items-center"></i>
                                            </a>
                                            @endif

                                            @if($application->status === 'draft' && ($application->created_by === Auth::user()->emp_id || auth()->user()->hasAnyRole(['Admin', 'Super Admin','Mis Admin']) ) )
                                            <!-- Delete button -->
                                            <form action="{{ route('applications.destroy', $application) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this application?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-action p-0" title="Delete">
                                                    <i class="bx bx-trash fs-10 d-flex justify-content-center align-items-center"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-1">
                        {{ $applications->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom small styles */
    .btn-action {
        width: 22px;
        height: 22px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .btn-action:hover {
        transform: scale(1.1);
        opacity: 0.9;
    }

    .fs-10 {
        font-size: 10px;
    }

    .small {
        font-size: 0.75rem;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.3rem;
    }

    .card {
        border-radius: 0.25rem;
    }

    .card-header {
        padding: 0.5rem 0.75rem;
    }

    .alert {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }

        .card-body {
            padding: 0.25rem;
        }

        .table td,
        .table th {
            white-space: nowrap;
        }
        
        .btn-action {
            width: 20px;
            height: 20px;
        }
        
        /* Hide S.No on very small screens if needed */
        .table-responsive th:nth-child(1),
        .table-responsive td:nth-child(1) {
            display: none;
        }
        
        /* Adjust other columns to fill space */
        .table-responsive th:nth-child(2),
        .table-responsive td:nth-child(2) {
            padding-left: 0.5rem;
        }
    }
</style>
@endsection