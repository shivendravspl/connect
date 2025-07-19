@component('mail::message')
# {{ $subject }}

**Application ID:** {{ $application->application_code }}

@if($remarks)
**Remarks:**  
{{ $remarks }}
@endif

@component('mail::button', ['url' => route('applications.show', $application)])
View Application
@endcomponent

@endcomponent