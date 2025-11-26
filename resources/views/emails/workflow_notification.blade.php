@component('mail::message')
# Application {{ ucfirst($action) }}

Application ID: **{{ $application->id }}**

**Remarks:** {{ $remarks }}

@component('mail::button', ['url' => url('/application/'.$application->id)])
View Application
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
