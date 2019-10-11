@component('mail::message')
# New Account Created on DCP

You are receiving this email because your account has been created on DCP
Vietnam by the Administrator

@component('mail::button', ['url' => $url])
        Reset Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}  Administrator
<hr>

Bạn đang nhận được email này vì tài khoản của bạn đã được tạo trên DCP
Việt Nam bởi Quản trị viên

@component('mail::button', ['url' => $vi_url])
        Đặt lại mật khẩu
@endcomponent

Cảm ơn,<br>
{{ config('app.name') }} Người quản lý
<hr>
@endcomponent

