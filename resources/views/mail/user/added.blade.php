<x-mail::message>
Hi,<br>
This is a new user registered on this system.<br>

Email: {{ $user->email }}<br>
Name: {{ $user->name }}<br>
Create At: {{ $user->created_at }}<br>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
