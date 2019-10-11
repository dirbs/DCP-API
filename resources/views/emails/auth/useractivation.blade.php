@component('mail::message')
# {{config('app.name')}} - Account Activation Email

Hello {{$user->name}}, Your account has been successfully activated..

@component('mail::button', ['url' => $url])
Sign In
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
<hr>
@component('mail::message')
# {{config('app.name')}} - Email kích hoạt tài khoản

Hello {{$user->name}}, tài khoản của bạn đã được kích hoạt thành công..

@component('mail::button', ['url' => $vi_url])
Đăng nhập
@endcomponent

Cảm ơn,<br>
{{ config('app.name') }}
@endcomponent
<hr>
