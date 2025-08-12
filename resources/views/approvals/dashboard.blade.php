<!DOCTYPE html>
<html>
<head>
    <title>Approval Dashboard</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Approval Dashboard</h1>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Application Code</th>
                    <th>Entity Name</th>
                    <th>Territory</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $application)
                    <tr>
                        <td>{{ $application->application_code }}</td>
                        <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                        <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</td>
                        <td>
                            <a href="{{ route('approvals.show', $application) }}" class="btn btn-primary btn-sm">View</a>
                            @if (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'documents_pending']) && Auth::user()->hasPermissionTo('process-mis'))
                                @if ($application->status == 'mis_processing')
                                    <a href="{{ route('approvals.verify-documents', $application) }}" class="btn btn-info btn-sm">Verify Documents</a>
                                @elseif ($application->status == 'document_verified')
                                    <a href="{{ route('approvals.upload-agreement', $application) }}" class="btn btn-info btn-sm">Upload Agreement</a>
                                @elseif (in_array($application->status, ['agreement_created', 'documents_received', 'documents_pending']))
                                    <a href="{{ route('approvals.track-documents', $application) }}" class="btn btn-info btn-sm">Track Documents</a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $applications->links() }}
    </div>
</body>
</html>