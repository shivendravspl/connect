@component('mail::message')
# ❌ Application Rejected – {{ $app_code }}

Dear **{{ $toName }}**,  

Your application has been **rejected** by the approver and the process is now closed.

@component('mail::table')
| Field | Details |
|--------|---------|
| Application Code | {{ $app_code }} |
| Submission Date | {{ $submitted_at }} |
| Distributor | {{ $application->entityDetails->establishment_name ?? 'N/A' }} |
@endcomponent

### Rejection Remarks  
{{ $remarks }}

No further action is required.

Regards,  
**System Auto-Notification**
@endcomponent
