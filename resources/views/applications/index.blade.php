@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h5 class="mb-0">My Applications</h5>
                    @if(auth()->user()->emp_id)
                    <a href="{{ route('applications.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">New Application</span>
                    </a>
                    @endif
                </div>

                <div class="card-body p-2">
                    @if($applications->isEmpty())
                        <div class="alert alert-info py-2 px-3 mb-2">You haven't submitted any applications yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-2">
                                <thead>
                                    <tr>
                                        <th class="py-1">App ID</th>
                                        <th class="py-1">Distributor</th>
                                        <th class="py-1 d-none d-sm-table-cell">Territory</th>
                                        <th class="py-1">Status</th>
                                        <th class="py-1 d-none d-md-table-cell">Submitted</th>
                                        <th class="py-1">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applications as $application)
                                    <tr>
                                        <td class="py-1">{{ $application->application_code }}</td>
                                        <td class="py-1">{{ $application->establishment_name ?? 'N/A' }}</td>
                                        <td class="py-1 d-none d-sm-table-cell">{{ $application->territory }}</td>
                                        <td class="py-1">
                                            <span class="badge bg-{{ $application->status_badge }}" style="font-size: 0.75rem;">
                                                {{ ucfirst($application->status) }}
                                            </span>
                                        </td>
                                        <td class="py-1 d-none d-md-table-cell">{{ $application->created_at->format('d-M-Y') }}</td>
                                        <td class="py-1">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('applications.show', $application) }}" class="btn btn-info btn-sm px-1 px-sm-2" title="View">
                                                    <i class="bx bx-show fs-12"></i>
                                                </a>
                                                @if(in_array($application->status, ['draft', 'reverted']))
                                                <a href="{{ route('applications.edit', $application) }}" class="btn btn-info btn-sm px-1 px-sm-2 edit-user" title="Edit">
                                                    <i class="bx bx-pencil fs-12"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-2">
                            {{ $applications->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection