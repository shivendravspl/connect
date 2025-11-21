@if($applications->isEmpty())
<div class="alert alert-info no-data-message">You haven't submitted or acted on any applications yet.</div>
@else
<div class="table-responsive">
    <table class="table table-sm compact-table table-hover">
        <thead class="table-light table-striped">
            <tr>
                <th>Sr. No</th>
                <th>Date of Submission</th>
                <th>Distributor Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($applications as $index => $application)
            <tr>
                <td>
                    <div class="sr-no-with-toggle">
                        <button class="toggle-timeline" 
                                data-application-id="{{ $application->id }}"
                                title="Show Approval Timeline">
                            <i class="ri-add-circle-line"></i>
                        </button>
                        <span>{{ $applications->firstItem() + $index }}</span>
                    </div>
                </td>
                <td>{{ $application->created_at->format('d M Y') }}</td>
                <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-{{ $application->status_badge ?? 'secondary' }}">
                        {{ ucwords(str_replace('_', ' ', $application->status ?? 'N/A')) }}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" title="View">
                            <i class="ri-eye-line"></i>
                        </a>
 					  @if(($application->status !== 'draft') && $application->created_by === Auth::user()->emp_id)
						  <a href="{{ route('dispatch.show', $application->id) }}" 
                           class="btn btn-sm btn-info" 
                           title="Fill Dispatch Details">
                            <i class="ri-truck-line"></i>Dispatch
                        </a>
						@endif
                        @if(($application->status === 'reverted' || $application->status === 'draft') && $application->created_by === Auth::user()->emp_id)
                        <a href="{{ route('applications.edit', $application->id) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="ri-edit-line"></i>
                        </a>
                        @endif
                        @if($application->status === 'distributorship_created')
                        <span class="badge bg-success p-2" title="Application Finalized">
                            <i class="ri-checkbox-circle-fill"></i> Finalized
                        </span>
                        @endif
                    </div>
                </td>
            </tr>
            {{-- Timeline row --}}
            <tr class="timeline-row" id="timeline-{{ $application->id }}">
                <td colspan="5" class="p-3">
                    @include('applications.partials.timeline', ['application' => $application])
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $applications->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
</div>
@endif