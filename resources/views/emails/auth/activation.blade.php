@component('mail::message')
# Activate account

New User Sign up at DCP, Please review and activate account.
<br>
- First Name: {{ $user->first_name }}
- Last Name: {{ $user->last_name }}
- Email Address: {{ $user->email }}

@component('mail::button', ['url' => route('auth.activate',['token'=> $user->activation_token,'email'=>$user->email])])
Activate
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
