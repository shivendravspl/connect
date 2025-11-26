<x-mail::message>
# Approval Required - Distributor Application

Dear **{{ $approver->emp_name }}**,

A distributor application requires your review and approval.

## Application Details
- **Application ID:** {{ $application->application_code }}
- **Distributor Name:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}
- **Submitted By:** {{ $application->createdBy->emp_name ?? 'N/A' }} ({{ $application->createdBy->emp_designation ?? 'N/A' }})
- **Submission Date:** {{ $application->created_at->format('d M, Y') }}
- **Current Status:** {{ ucwords(str_replace('_', ' ', $application->status)) }}

@if($remarks)
## Previous Remarks
> "{{ $remarks }}"
@endif

<x-mail::button :url="route('approvals.show', $application->id)">
Review Application
</x-mail::button>

**Urgency:** Please review this application within 24 hours to ensure timely processing.

Thanks,  
**{{ config('app.name') }} Team**
</x-mail::message>