@component('mail::message')
# Dear MIS Team,

The following distributor appointment application has been approved at Sales-level and requires action at your end.

### **Application Details**
@component('mail::table')
| Field | Value |
|-------|--------|
| Application ID | {{ $application->id }} |
| Distributor Name | {{ $application->distributor_name }} |
| Location | {{ $application->location }} |
| Created By | {{ $application->creator->emp_name ?? '-' }} |
| Approved Date | {{ now()->format('d M Y H:i') }} |
@endcomponent

Please proceed with the next steps in MIS processing.

Thanks & Regards,<br>
**System Notification**

@endcomponent
