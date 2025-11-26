<x-mail::message>
# Additional Documents Required – Distributor Application

Dear **{{ $salesPerson->emp_name }}**,

During document verification, the following documents were found missing or incomplete:

## Application Details
- **Application ID:** {{ $application->application_code }}
- **Distributor Name:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}
- **Business Unit:**  {{ $application->businessUnit?->business_unit_name ?? 'N/A' }}
- **Zone:** {{ $application->zoneDetail?->zone_name ?? 'N/A' }}

## Missing/Incomplete Documents
<x-mail::table>
| Document Name | Remarks / Requirement |
|--------------|----------------------|
@foreach($missingDocuments as $document)
| {{ $document['document_type'] }} | {{ $document['remarks'] }} |
@endforeach
</x-mail::table>

Kindly upload the required documents to proceed further.

<x-mail::button :url="route('applications.show', $application->id)" color="primary">
Upload Documents
</x-mail::button>

Regards,  
**MIS Team**  
**{{ config('app.name') }}**
</x-mail::message>