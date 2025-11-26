<x-mail::message>
# Approval Required - Distributor Application

Dear **{{ $nextApprover->emp_name }}**,

A new distributor application has been forwarded to you for approval.

**Application ID:** {{ $application->application_code }}  
**Distributor:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}  
**Submitted By:** {{ $application->createdBy->emp_name ?? 'N/A' }} ({{ $application->createdBy->emp_designation ?? 'N/A' }})  
**Submission Date:** {{ $application->created_at->format('d M, Y') }}  

@if($remarks)
**Previous Remarks:** {{ $remarks }}
@endif

<x-mail::button :url="route('approvals.show', $application->id)">
Review Application
</x-mail::button>

Thank you for your prompt attention to this matter.

Thanks,  
**{{ config('app.name') }} Team**
</x-mail::message>