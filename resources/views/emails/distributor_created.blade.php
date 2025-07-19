@component('mail::message')
# Distributor Appointed

A new distributor has been appointed.

**Application ID:** {{ $application->application_code }}  
**Distributor Code:** {{ $distributor->distributor_code }}  
**Name:** {{ $distributor->name }}

The distributorship has been finalized.

@component('mail::button', ['url' => route('approvals.show', $application)])
View Application
@endcomponent

@endcomponent