<x-mail::message>
# Distributor Appointed Successfully – {{ $application->application_code }}

Dear Team,

We are pleased to inform you that the distributor appointment process is successfully completed.

## Distributor Details
- **Application ID:** {{ $application->application_code }}
- **Distributor Name:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}
- **Distributor Code:** {{ $application->distributor_code ?? 'N/A' }}
- **Business Unit:** {{ $application->businessUnit?->business_unit_name ?? 'N/A' }}
- **Zone:** {{ $application->zoneDetail?->zone_name ?? 'N/A' }}
- **Date of Creation:** {{ $application->distributorship_confirmed_at?->format('d M, Y') ?? 'N/A' }}
- **Date of Appointment:** {{ $application->date_of_appointment?->format('d M, Y') ?? 'N/A' }}

The distributor account is now active in FOCUS/ESS and can begin commercial operations.

<x-mail::button :url="route('applications.show', $application->id)" color="success">
View Distributor Details
</x-mail::button>

Regards,  
**MIS Team**  
**{{ config('app.name') }}**
</x-mail::message>