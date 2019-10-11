@component('mail::message')
    New Account

You account has been created on DCP Vietnam by the Administrator
<br>
- Email Address: {{$user->email}}

You may reset your account password by clicking on the below button
@component('mail::button', ['url' => 'http://vietnam.dcp.smartforum.org/en/password/recover'])
Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
