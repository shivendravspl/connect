<x-mail::message>
# Distributor Agreement Draft Ready – {{ $application->application_code }}

Dear **{{ $creator->emp_name }}**,

The draft Distributor Agreement for the following application has been successfully created and uploaded.

## Application Details
- **Application ID:** {{ $application->application_code }}
- **Distributor Name:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}
- **Business Unit:** {{ $application->businessUnit?->business_unit_name ?? 'N/A' }}
- **Zone:** {{  $application->zoneDetail?->zone_name ?? 'N/A' }}
- **Document Verified On:** {{ $application->mis_verified_at?->format('d M, Y') }}

**Draft Agreement:** Available for download in system

Please proceed with physical document collection and execution.

<x-mail::button :url="route('applications.show', $application->id)" color="primary">
Download Agreement
</x-mail::button>

Regards,  
**MIS Team**  
**{{ config('app.name') }}**
</x-mail::message>