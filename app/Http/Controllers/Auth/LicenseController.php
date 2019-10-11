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
use App\LicenseAgreement;
use App\User;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class LicenseController extends Controller {
	/**
	 * @param $id
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getRecentLicense( $id, JWTAuth $JWTAuth ) {
		try {
			$JWTAuth->parseToken()->authenticate();
			$user    = User::findOrFail( $id );
			$license = LicenseAgreement::latest()->first();

			return response()->json( [
				'user'    => $user,
				'roles'   => $user->roles(),
				'license' => $license,
				'success' => true,
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
	 * @param $id
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateLicensedUser( $id, JWTAuth $JWTAuth ) {

		try {
			$JWTAuth->parseToken()->authenticate();
			$user = User::where( 'id', $id )->first();

			//if user role is staff, as licenses are only for staff members.
			if ( isset( $user ) && $user->roles[0]->slug === 'staff' ) {
				//Get the latest license agreement by admin
				$license = LicenseAgreement::latest()->first();
				if ( $user->loginCount == 0 ) {
					$user->licenses()->attach( $license->id, [ 'type' => 'login' ] );
					$user->agreement = 'Agreed';
					$user->update();

					return response()->json( [
						'user'     => $user,
						'roles'    => $user->roles,
						'licenses' => $user->licenses,
						'success'  => true,
						'status'   => 'agreed'
					] );

				} elseif ( $user->licenses->last()->version != $license->version ) {

					$user->licenses()->attach( $license->id, [ 'type' => 'login' ] );
					$user->agreement = 'Agreed';
					$user->update();

					return response()->json( [
						'user'     => $user,
						'roles'    => $user->roles,
						'licenses' => $user->licenses,
						'success'  => true,
						'status'   => 'agreed'
					] );
				}
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


	/**
	 * @param $id
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function updateAppLicenseUser( $id, JWTAuth $JWTAuth ) {

		try {

			$JWTAuth->parseToken()->authenticate();

			/** @var $user - Get the User */
			$user = User::findOrFail( $id );

			//Get the latest license agreement by admin
			$license = LicenseAgreement::latest()->first();

			if ( $user->licenses()->wherePivot( 'type', '=', 'app' )->exists() && $license->version == $user->licenses()->wherePivot( 'type', 'app' )->get()->last()->version ) {

				$user->licenses()->wherePivot( 'type', 'app' )->updateExistingPivot( $license->id, [ 'updated_at' => Carbon::now() ] );

				return response()->json( [
					'success' => true,
					'status'  => 'updated',
					'license' => $user->licenses
				] );

			} else {
				$user->licenses()->attach( $license->id, [ 'type' => 'app' ] );

				return response()->json( [
					'success' => true,
					'status'  => 'agreed'
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


	/**
	 * @param $id
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getUserLicenses( $id ) {


		$user = User::where( 'id', $id )->with( 'licenses' )->first();

		return response()->json( [
			'success' => true,
			'user'    => $user,
		] );
	}

	public function getUserCurrentInfo( $id ) {
		$user = User::where( 'id', $id )->with( 'licenses','roles' )->first();

		return response()->json( [
			'user' => $user
		] );
	}
}