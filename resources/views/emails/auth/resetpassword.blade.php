@component('mail::message')
# Password Reset Request

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $url])
        Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}  Administrator
<hr>

Bạn đang nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.

@component('mail::button', ['url' => $vi_url])
        Đặt lại mật khẩu
@endcomponent

Nếu bạn không yêu cầu đặt lại mật khẩu, không cần thực hiện thêm hành động nào.

Cảm ơn,<br>
{{ config('app.name') }} Người quản lý
<hr>
@endcomponent

