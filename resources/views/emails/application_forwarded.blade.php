@component('mail::message')
# Application Forwarded for Review – {{ $app_code }}

Dear Sir,

The following application has been **approved by {{ $current_name }} ({{ $current_role }})** and is forwarded for your review:

**Application Details:**

@component('mail::table')
| Field            | Details |
|------------------|---------|
| Application ID   | {{ $application_id }} |
| App Code         | {{ $app_code }} |
| Submitted By     | {{ $application->createdBy?->emp_name ?? 'N/A' }} |
| Submission Date  | {{ $submitted_at }} |
| Distributor Name | {{ $application->entityDetails->establishment_name ?? 'N/A' }} |
| Territory        | {{ $application->territoryDetail?->territory_name ?? 'N/A' }} |
| Region           | {{ $application->regionDetail?->region_name ?? 'N/A' }} |
| Zone             | {{ $application->zoneDetail?->zone_name ?? 'N/A' }} |
@endcomponent

@if($remarks)
**Remarks from {{ $current_role }}:**  
{{ $remarks }}
@endif

@php
    $url = route('applications.index', $application_id);
@endphp

@if($hasAction)
### **You are the current approver.**
Please take action on this application.

@component('mail::button', ['url' => $url])
Review & Take Action (Approve / Reject / Hold)
@endcomponent
@else
### **For your information only.**
Action required from: **{{ $application->currentApprover?->emp_name ?? 'Next Approver' }}**

@component('mail::button', ['url' => $url, 'color' => 'gray'])
View Application (Reference Only)
@endcomponent
@endif

<br>
Regards,  
**System Auto-Alert**
@endcomponent
