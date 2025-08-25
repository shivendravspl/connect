@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-sm-3">
    <div class="row justify-content-center">
        <div class="col-12 px-0 px-sm-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                    <h5 class="mb-0 small font-weight-bold">My Applications</h5>
                    @if(auth()->user()->emp_id)
                    <a href="{{ route('applications.create') }}" class="btn btn-primary py-1 px-3 fs-6">
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
                                    <th class="py-1 px-1">App ID</th>
                                    <th class="py-1 px-1">Distributor</th>
                                    <th class="py-1 px-1 d-none d-sm-table-cell">Territory</th>
                                    <th class="py-1 px-1">Status</th>
                                    <th class="py-1 px-1 d-none d-md-table-cell">Submitted</th>
                                    <th class="py-1 px-1">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($applications as $application)
                                <tr>
                                    <td class="py-1 px-1">{{ $application->application_code }}</td>
                                    <td class="py-1 px-1">{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td class="py-1 px-1 d-none d-sm-table-cell">{{ isset($application->territory) ? DB::table('core_territory')->where('id', $application->territory)->value('territory_name') ?? 'N/A' : 'N/A' }}</td>
                                    <td class="py-1 px-1">
                                        <span class="badge bg-{{ $application->status_badge }}" style="font-size: 0.65rem;">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </td>
                                    <td class="py-1 px-1 d-none d-md-table-cell">{{ $application->created_at->format('d-M-Y') }}</td>
                                    <td class="py-1 px-1">
                                        <div class="btn-group btn-group-xs" role="group" style="gap: 0.1rem;">
                                            <!-- View button -->
                                            <a href="{{ route('applications.show', $application) }}" class="btn btn-info btn-xs p-0 px-1" title="View">
                                                <i class="bx bx-show fs-10"></i>
                                            </a>

                                            @if(in_array($application->status, ['draft', 'reverted']) && $application->created_by === Auth::user()->emp_id)
                                            <!-- Edit button -->
                                            <a href="{{ route('applications.edit', $application) }}" class="btn btn-info btn-xs p-0 px-1" title="Edit">
                                                <i class="bx bx-pencil fs-10"></i>
                                            </a>
                                            @endif

                                            @if($application->status === 'draft' && $application->created_by === Auth::user()->emp_id)
                                            <!-- Delete button -->
                                            <form action="{{ route('applications.destroy', $application) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this application?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs p-0 px-1" title="Delete">
                                                    <i class="bx bx-trash fs-10"></i>
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
    .btn-xs {
        padding: 0.15rem 0.3rem;
        font-size: 0.65rem;
        line-height: 1;
        border-radius: 0.15rem;
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
    }
</style>
@endsection