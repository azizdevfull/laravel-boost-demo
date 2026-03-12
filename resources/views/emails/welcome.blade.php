<x-mail::message>
# Welcome, {{ $user->name }}!

Welcome to our platform. We are happy to have you here.

<x-mail::button :url="config('app.url')">
Visit Website
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
