<!DOCTYPE html>
<html>
<head>
    <title>New Application Submitted</title>
</head>
<body>
    <h1>New Application Submitted</h1>
    <p>Dear {{ $approver_name }},</p>
    <p>A new application (ID: {{ $application_id }}) has been submitted by {{ $user_name }} and requires your approval.</p>
    <p>Please review the application at: <a href="{{ route('applications.show', $application_id) }}">View Application</a></p>
    <p>Thank you,</p>
    <p>Your Application Team</p>
</body>
</html>