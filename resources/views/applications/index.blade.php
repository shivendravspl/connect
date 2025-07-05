<!-- resources/views/applications/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Applications</h4>
                    @if(auth()->user()->emp_id)
                    <a href="{{ route('applications.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New Application
                    </a>
                    @endif
                </div>

                <div class="card-body">
                    @if($applications->isEmpty())
                        <div class="alert alert-info">You haven't submitted any applications yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>App ID</th>
                                        <th>Distributor Name</th>
                                        <th>Territory</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applications as $application)
                                    <tr>
                                        <td>{{ $application->application_code }}</td>
                                        <td>{{ $application->establishment_name ?? 'N/A' }}</td>
                                        <td>{{ $application->territory }}</td>
                                        <td>
                                            <span class="badge bg-{{ $application->status_badge }}">
                                                {{ ucfirst($application->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $application->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            <a href="{{ route('applications.show', $application) }}" class="btn btn-lg btn-info">
                                                <i class="bx bx-show fs-14"></i>
                                            </a>
                                            @if(in_array($application->status, ['draft', 'reverted']))
                                            <a href="{{ route('applications.edit', $application) }}" class="btn btn-sm btn-info edit-user">
                                                <i class="bx bx-pencil fs-14"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $applications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection