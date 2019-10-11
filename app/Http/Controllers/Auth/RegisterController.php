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

use App\Events\Auth\UserRequestedActivationEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Role;
use App\User;
use Tymon\JWTAuth\JWTAuth;

class RegisterController extends Controller {

	/**
	 * @var JWTAuth
	 */
	protected $auth;

	/**
	 * RegisterController constructor.
	 *
	 * @param JWTAuth $JWTAuth
	 */
	public function __construct( JWTAuth $JWTAuth ) {
		$this->auth = $JWTAuth;
	}

	/**
	 * @param RegisterRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function register( RegisterRequest $request ) {

		$request->validated();
		$staff_role = Role::where( 'slug', 'staff' )->first();

		$user = User::create( [
			'first_name'       => $request->first_name,
			'last_name'        => $request->last_name,
			'email'            => $request->email,
			'password'         => bcrypt( $request->password ),
			'active'           => false,
			'activation_token' => str_random( 100 ),

		] );

		$user->roles()->attach( $staff_role );
		$token = $this->auth->attempt( $request->only( 'email', 'password' ) );

		\Session::put( 'signup-token', $token );

		return response()->json( [
			'data'       => $user,
			'meta'       => [
				'token' => $token
			],
			'registered' => $this->registered( $user )
		], 200 );

	}


	/**
	 * @return bool
	 */
	public function logout() {

		\Session::forget( 'user' );
		\Session::forget( 'signup-token' );
		\Session::flush();

		return true;
	}

	/**
	 * @param $user
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function registered( $user ) {

		event( new UserRequestedActivationEmail( $user ) );

		$this->logout();

		return response()->json( [
			'redirect' => true,
			'success'  => true,
			'message'  => trans( 'auth.registered.success' )
		] );
	}


}
