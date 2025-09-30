@php
    $appName = config('app.name');
@endphp

<x-mail::message>
# You have been added as a guardian

Hello{{ $guardian->name ? ' '.$guardian->name : '' }},

An account has been created for you on {{ $appName }} because {{ $minor->name ?? 'a minor' }} listed you as their "{{ str_replace('_', ' ', $relationship) }}" during registration. This allows you to help manage their account and receive important notifications.

To activate your account, please set a password using the secure link below.

<x-mail::button :url="$resetUrl">
Set your password
</x-mail::button>

If you weren’t expecting this or believe this was a mistake, you can safely ignore this email. Your account won’t be accessible until you set a password.

Thanks,
{{ $appName }} Team

---
If the button above doesn’t work, copy and paste this URL into your browser:
{{ $resetUrl }}
</x-mail::message>
