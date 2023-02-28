@component('mail::message')
Hello,

{{ $content }}

@component('mail::panel')
{{ $code }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
