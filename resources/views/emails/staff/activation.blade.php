@component('mail::message')
# Account Activated Successfully

Your account has been successfully activated.

You can now login to DCP.

@component('mail::button', ['url' => $url])
Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}  Administrator
<hr>

# Tài khoản được kích hoạt thành công

Tài khoản của bạn đã được kích hoạt thành công.

Bây giờ bạn có thể đăng nhập vào DCP.

@component('mail::button', ['url' => $vi_url])
Login
@endcomponent
Cảm ơn,<br>
{{ config('app.name') }} Người quản lý
<hr>
@endcomponent

