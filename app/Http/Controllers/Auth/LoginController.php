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
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\LicenseAgreement;
use App\User;
use Tymon\JWTAuth\JWTAuth;

class LoginController extends Controller {

	/**
	 * @var JWTAuth
	 */
	protected $auth;

	/**
	 * LoginController constructor.
	 *
	 * @param JWTAuth $JWTAuth
	 */
	public function __construct( JWTAuth $JWTAuth ) {
		$this->auth = $JWTAuth;
	}

	/**
	 *
	 * @param LoginRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function login( LoginRequest $request ) {

		//check if the user has credentials

		$check_actve = User::where( 'email', $request->email )->where( 'active', false )->first();
		if ( ! $check_actve ) {
			$request->validated();
		} else {
			return response()->json( [
				'errors'  => true,
				'message' => trans( 'auth.inactivated' ),
				'is_ie'   => true,
			], 401 );
		}
		if ( ! $token = $this->auth->attempt( $request->only( 'email', 'password' ) ) ) {
			return response()->json( [
				'errors'  => true,
				'message' => trans( 'auth.failed' ),
				'is_ie'   => true,
			], 401 );
		}

		return response()->json( [
			'data'                   => $request->user(),
			'roles'                  => $request->user()->roles,
			'licenses'               => $request->user()->licenses,
			'current_active_license' => LicenseAgreement::latest()->first(),
			'meta'                   => [
				'token' => $token
			],
		], 200 );
	}
}
