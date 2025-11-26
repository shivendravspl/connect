<x-mail::message>
# Physical Document Pending Status Update – {{ $application->application_code }}

Dear **{{ $creator->emp_name }}**,

Physical documents are still pending for the following application:

## Application Details
- **Application ID:** {{ $application->application_code }}
- **Distributor Name:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}
- **Business Unit:** {{ $application->businessUnit?->business_unit_name ?? 'N/A' }}
- **Zone:** {{  $application->zoneDetail?->zone_name ?? 'N/A' }}
- **Status Update Date:** {{ now()->format('d M, Y') }}

## Pending Documents Status
<x-mail::table>
| Document Type | Status | Remarks |
|--------------|--------|---------|
@foreach($pendingDocuments as $document)
| {{ ucfirst(str_replace('_', ' ', $document['type'])) }} | {{ $document['received'] ? 'Received' : 'Not Received' }} | {{ $document['reason'] ?? 'No remarks' }} |
@endforeach
</x-mail::table>

Kindly dispatch at the earliest to avoid delay.

<x-mail::button :url="route('applications.show', $application->id)" color="primary">
View Application
</x-mail::button>

Regards,  
**MIS Team**  
**{{ config('app.name') }}**
</x-mail::message>