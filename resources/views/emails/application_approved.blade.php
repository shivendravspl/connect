<x-mail::message>
# Application Approved - Distributor Application

Dear **{{ $recipient->emp_name }}**,

@if($recipient->employee_id == $application->created_by)
## 🎉 Congratulations! Your application has been approved.

**Application ID:** {{ $application->application_code }}  
**Distributor:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}  
**Approval Date:** {{ now()->format('d M, Y') }}  

@if($remarks)
**Approval Remarks:** {{ $remarks }}
@endif

The application will now proceed to MIS processing for final setup.

@else
## 📋 Application Status Update

The distributor application has been fully approved.

**Application ID:** {{ $application->application_code }}  
**Distributor:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}  
**Initiator:** {{ $application->createdBy->emp_name ?? 'N/A' }}  
**Approval Date:** {{ now()->format('d M, Y') }}  

@if($remarks)
**Remarks:** {{ $remarks }}
@endif

@endif

<x-mail::button :url="route('approvals.show', $application->id)">
View Application Details
</x-mail::button>

@if($recipient->employee_id == $application->created_by)
**Next Steps:** The MIS team will contact you shortly for further processing.
@endif

Thanks,  
**{{ config('app.name') }} Team**
</x-mail::message>