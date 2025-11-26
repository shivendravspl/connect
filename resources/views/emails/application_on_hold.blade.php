@component('mail::message')
# ⏸ Application On Hold – {{ $app_code }}

Dear **{{ $toName }}**,

Your application has been placed **on hold** for additional clarification.

@component('mail::table')
| Field | Details |
|--------|--------|
| Application Code | {{ $app_code }} |
| Follow-up Date | {{ \Carbon\Carbon::parse($followUpDate)->format('d-m-Y') }} |
| Distributor | {{ $application->entityDetails->establishment_name ?? 'N/A' }} |
@endcomponent

### Hold Remarks
{{ $remarks }}

A follow-up reminder will be triggered automatically.

Regards,  
**System Auto-Notification**
@endcomponent
