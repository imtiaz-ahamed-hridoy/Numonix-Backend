@component('mail::message')
# Hello {{ $user->name }},

Your requested data export is attached to this email as a JSON file.

Please keep this file safe - it contains your personal data from NumoniX.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
