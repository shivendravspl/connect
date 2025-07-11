<x-mail::message>
# Hello from {{ $data['app_name'] }}

This is a test email sent at {{ $data['time'] }}.

<x-mail::button :url="'https://example.com'">
Visit Our Site
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
