@component('mail::layout')
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    # {{ $subject }}

    **Application ID:** {{ $application->id }}  
    **Distributor Name:** {{ $application->entityDetails->name ?? 'N/A' }}  
    **Current Status:** {{ ucfirst(str_replace('_', ' ', $application->status)) }}

    @if($remarks)
    **Remarks:**  
    {{ $remarks }}
    @endif

    @component('mail::button', ['url' => route('applications.show', $application->id)])
        View Application
    @endcomponent

    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent