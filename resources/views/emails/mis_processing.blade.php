<x-mail::message>
# MIS Processing Required - Distributor Application

Dear **MIS Team**,

A distributor application has been fully approved and requires MIS processing.

## Application Details
- **Application ID:** {{ $application->application_code }}
- **Distributor Name:** {{ $application->entityDetails->establishment_name ?? 'N/A' }}
- **Business Unit:** {{ $application->businessUnit?->business_unit_name ?? 'N/A' }}
- **Zone:** {{ $application->zoneDetail?->zone_name ?? 'N/A' }}
- **Approval Date:** {{ now()->format('d M, Y') }}

## Required MIS Actions
1. Verify all submitted documents
2. Process security deposit details
3. Generate distributor code
4. Complete system setup

<x-mail::button :url="route('mis.applications')" color="success">
Process in MIS System
</x-mail::button>

Please complete the MIS processing within 48 hours.

Thanks,  
**{{ config('app.name') }} Team**
</x-mail::message>