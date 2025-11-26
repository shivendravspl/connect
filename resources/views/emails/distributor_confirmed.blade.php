<x-mail::message>
# 🎉 Distributor Successfully Confirmed!

Dear **{{ $recipient->emp_name }}**,

We're pleased to inform you that the distributor onboarding process has been successfully completed.

## Distributor Details
- **Application ID:** {{ $application->application_code }}
- **Distributor Code:** {{ $application->distributor_code }}
- **Distributor Name:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}
- **Date of Appointment:** {{ $application->date_of_appointment->format('d M, Y') }}
- **Confirmation Date:** {{ now()->format('d M, Y') }}

## ✅ Completion Status
- ✅ All approvals completed
- ✅ Documents verified
- ✅ Security deposit processed
- ✅ Distributor code assigned
- ✅ System setup completed

@if($recipient->employee_id == $application->created_by)
**Congratulations on successfully onboarding a new distributor!**

<x-mail::button :url="route('applications.show', $application->id)" color="success">
View Distributor Details
</x-mail::button>
@else
<x-mail::button :url="route('approvals.show', $application->id)">
View Distributor Details
</x-mail::button>
@endif

Thanks for your contribution to this successful onboarding process!

Best regards,  
**{{ config('app.name') }} Team**
</x-mail::message>