@component('mail::message')
# Account Confirmation Email

You have successfully registered at DCP. System administrator needs to activate your account before you can login & use your account.

Please contact system administrator if account is not activated within 24 hours.

Once account is activated, you may login to DCP portal.

@component('mail::button', ['url' => $url])
Login
@endcomponent

Thanks,<br>
{{ config('app.name') }}  Administrator
<hr>

# Email xác nhận tài khoản

Bạn đã đăng ký thành công tại DCP. Quản trị viên hệ thống cần kích hoạt tài khoản của bạn trước khi bạn có thể đăng nhập và sử dụng tài khoản của mình.

Vui lòng liên hệ với quản trị viên hệ thống nếu tài khoản không được kích hoạt trong vòng 24 giờ.

Khi tài khoản được kích hoạt, bạn có thể đăng nhập vào cổng thông tin DCP.

@component('mail::button', ['url' => $vi_url])
Đăng nhập
@endcomponent

Cảm ơn,<br>
{{ config('app.name') }} Người quản lý
<hr>
@endcomponent

