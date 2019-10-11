<?php
/**Copyright (c) 2018-2019 Qualcomm Technologies, Inc.
All rights reserved.
Redistribution and use in source and binary forms, with or without modification, are permitted (subject to the limitations in the disclaimer below) provided that the following conditions are met:
Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of Qualcomm Technologies, Inc. nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment is required by displaying the trademark/log as per the details provided here: https://www.qualcomm.com/documents/dirbs-logo-and-brand-guidelines
Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.
This notice may not be removed or altered from any source distribution.
NO EXPRESS OR IMPLIED LICENSES TO ANY PARTY'S PATENT RIGHTS ARE GRANTED BY THIS LICENSE. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.*/
return [

	/*
	|--------------------------------------------------------------------------
	| Authentication Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines are used during authentication for various
	| messages that we need to display to the user. You are free to modify
	| these language lines according to your application's requirements.
	|
	*/

	'failed'   => 'Những thông tin không phù hợp với hồ sơ của chúng tôi.',
	'throttle' => 'Quá nhiều lần thử đăng nhập. Vui lòng thử lại trong :seconds seconds.',
	'token'    => [
		'blacklist' => 'Mã thông báo đã được liệt kê đen',
		'expired'   => 'Mã thông báo đã hết hạn',
		'invalid'   => 'Mã thông báo không hợp lệ',
		'missing'   => 'Mã thông báo bị thiếu'
	],

	'inactivated' => 'Bạn cần kích hoạt tài khoản của mình, vui lòng liên hệ với Quản trị viên hệ thống để được hỗ trợ thêm',

	'unauthorized' => 'Bạn không có đủ quyền cho yêu cầu này, vui lòng liên hệ với quản trị viên hệ thống để biết thêm chi tiết',


	'registered' => [
		'success' => 'Đã đăng ký, Vui lòng kiểm tra email của bạn để kích hoạt tài khoản của bạn'
	],
	'email'      => [
		'recover' => [
			'error'   => 'Chúng tôi không thể tìm thấy người dùng có địa chỉ email đó.',
			'success' => 'Chúng tôi đã gửi email liên kết đặt lại mật khẩu của bạn!'
		]
	]


];
