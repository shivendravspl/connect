@component('mail::message')
# Action Required – Application Reverted for Correction

Dear **{{ $toName }}**,  

Your application has been **reverted for corrections**.

**Application Details**

@component('mail::table')
| Field | Details |
|--------|---------|
| Application Code | {{ $app_code }} |
| Submitted Date | {{ $submitted_at }} |
| Distributor | {{ $application->entityDetails->establishment_name ?? 'N/A' }} |
| Territory | {{ $application->territoryDetail?->territory_name ?? 'N/A' }} |
| Region | {{ $application->regionDetail?->region_name ?? 'N/A' }} |
| Zone | {{ $application->zoneDetail?->zone_name ?? 'N/A' }} |
@endcomponent

---

### **Revert Remarks**
{{ $remarks }}

@component('mail::button', ['url' => route('applications.show', $application->id)])
Review & Update Application
@endcomponent

<br>
Regards,  
**System Auto-Notification**
@endcomponent
