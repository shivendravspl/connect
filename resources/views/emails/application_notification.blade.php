<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $mailSubject }}</title>
</head>
<body>
    <h1>{{ $mailSubject }}</h1>

    <p><strong>Application ID:</strong> {{ $application->application_code }}</p>

    @if($actionType)
        <p><strong>Action Type:</strong> {{ ucfirst($actionType) }}</p>
    @endif

    @if($remarks)
        <p><strong>Remarks:</strong> {{ $remarks }}</p>
    @endif

    <p>
        <a href="{{ route('approvals.show', $application->id) }}"
		style="display:inline-block; padding:10px 20px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:4px;">
		View Application
		</a>
    </p>

    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
