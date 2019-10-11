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
namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class ProfileController extends Controller {
	/**
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function showProfile( JWTAuth $JWTAuth ) {

		try {
			$user  = $JWTAuth->parseToken()->authenticate();
			$roles = $user->roles()->get();

			return response()->json( [
				'success' => true,
				'data'    => [
					'user'  => $user,
					'roles' => $roles
				]
			] );

		} catch ( TokenExpiredException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.expired' )
				]
			], 401 );
		} catch ( TokenInvalidException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.invalid' )
				]
			], 401 );

		} catch ( JWTException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.missing' )
				]
			], 401 );
		}

	}

	/**
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getProfile( JWTAuth $JWTAuth ) {
		try {
			$user  = $JWTAuth->parseToken()->authenticate();
			$roles = $user->roles()->get();

			return response()->json( [
				'success' => true,
				'data'    => [
					'user'  => $user,
					'roles' => $roles
				]
			] );

		} catch ( TokenExpiredException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.expired' )
				]
			], 401 );
		} catch ( TokenInvalidException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.invalid' )
				]
			], 401 );

		} catch ( JWTException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.missing' )
				]
			], 401 );
		}

	}

	/**
	 * @param Request $request
	 * @param $id
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function editProfile( Request $request, $id, JWTAuth $JWTAuth ) {

		try {

			$JWTAuth->parseToken()->authenticate();

			$user  = User::findOrFail( $id );
			$rules = [
				'first_name' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
				'last_name'  => 'required|regex:/^[a-zA-Z]+$/u|max:255',

			];

			$messages  = [

				'first_name.max'      => trans( 'profile.first_name.max' ),
				'last_name.max'       => trans( 'profile.last_name.max' ),
				'first_name.required' => trans( 'profile.first_name.required' ),
				'last_name.required'  => trans( 'profile.last_name.required' ),
				'first_name.regex'    => trans( 'profile.first_name.regex' ),
				'last_name.regex'     => trans( 'profile.last_name.regex' )

			];
			$validator = \Validator::make( $request->only( [
				'first_name',
				'last_name',
			] ), $rules, $messages );

			if ( $validator->fails() ) {

				return response()->json( [
					'errors' => [
						'root' => $validator->errors(),
					]
				] );
			}

			$user->update( $request->only( [ 'first_name', 'last_name' ] ) );

			return response()->json( [
				'data' => [
					'user' => $request->only( [ 'first_name', 'last_name' ] ),
				]
			] );
		} catch ( TokenExpiredException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.expired' )
				]
			], 401 );
		} catch ( TokenInvalidException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.invalid' )
				]
			], 401 );

		} catch ( JWTException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.missing' )
				]
			], 401 );
		}

	}

	/**
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getPassword( JWTAuth $JWTAuth ) {
		try {
			$user  = $JWTAuth->parseToken()->authenticate();
			$roles = $user->roles()->get();

			return response()->json( [
				'success' => true,
				'data'    => [
					'user'  => $user,
					'roles' => $roles
				]
			] );

		} catch ( TokenExpiredException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.expired' )
				]
			], 401 );
		} catch ( TokenInvalidException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.invalid' )
				]
			], 401 );

		} catch ( JWTException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.missing' )
				]
			], 401 );
		}

	}

	/**
	 * @param Request $request
	 * @param $id
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function editPassword( Request $request, $id, JWTAuth $JWTAuth ) {
		try {

			$JWTAuth->parseToken()->authenticate();

			$user = User::findOrFail( $id );
			\Validator::extend( 'old_password', function ( $attribute, $value, $parameters, $validator ) {

				return Hash::check( $value, current( $parameters ) );

			} );
			$rules     = [
				'old_password'          => 'required|old_password:' . $user->password,
				'password'              => 'required|min:8|different:old_password|strong_password',
				'password_confirmation' => 'required|same:password',
			];
			$messages  = [
				'old_password.old_password'      => trans( 'profile.password.old_password.old_password' ),
				'old_password.required'          => trans( 'profile.password.old_password.required' ),
				'password.regex'                 => trans( 'profile.password.regex' ),
				'password.required'              => trans( 'profile.password.required' ),
				'password.min'                   => trans( 'profile.password.min' ),
				'password.strong_password'       => trans( 'profile.password.strong' ),
				'password_confirmation.required' => trans( 'profile.password_confirmation.required' ),
				'password_confirmation.same'     => trans( 'profile.password_confirmation.same' ),

			];
			$validator = \Validator::make( $request->only( [
				'old_password',
				'password',
				'password_confirmation'
			] ), $rules, $messages );

			if ( $validator->fails() ) {

				return response()->json( [
					'errors' => [
						'root' => $validator->errors(),
					]
				] );
			}

			$current_password = $user->password;

			if ( Hash::check( $request['old_password'], $current_password ) ) {

				$user->password = Hash::make( $request['password'] );
				$user->update();

				return response()->json( [
					'data' => [
						'user' => 'ok'
					]
				] );
			}
		} catch ( TokenExpiredException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.expired' )
				]
			], 401 );
		} catch ( TokenInvalidException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.invalid' )
				]
			], 401 );

		} catch ( JWTException $exception ) {
			return response()->json( [
				'errors' => [
					'root' => trans( 'auth.token.missing' )
				]
			], 401 );
		}
	}
}
