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
namespace App\Http\Controllers\IMEI;

use App\Http\Controllers\Controller;
use App\Http\Requests\IMEISearchRequest;
use App\IMEI;
use App\Libraries\WCOApi;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;


/**
 * @property Client client
 */
class ImController extends Controller {


	/**
	 * @return mixed
	 */
	public function getRealIpAddr() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) //to check ip passed from proxy
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = @$_SERVER['REMOTE_ADDR'];

		}

		return $ip;
	}


	/**
	 * @param Request $request
	 * @param JWTAuth $JWTAuth
	 * @param $user_device
	 * @param $checking_method
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function lookup( IMEISearchRequest $request, JWTAuth $JWTAuth, $user_device, $checking_method ) {
		try {
			$user = $JWTAuth->parseToken()->authenticate();

			$request->validated();

			$input_search = Input::get( 'imei' );

			$wco_port_name = env( 'WCO_PORT_NAME' );
			$wco_port_type = env( 'WCO_PORT_TYPE' );
			$wco_country   = env( 'WCO_COUNTRY' );
			$input         = substr( $input_search, 0, 8 );

			if ( $user_device === 'web' ) {
				$ip = $request->get( 'ip' );
			} else {
				$ip = $this->getRealIpAddr();
			}

			$locDetails = \geoip()->getLocation( $ip );
			$lat        = $locDetails->lat;
			$lon        = $locDetails->lon;
			$country    = $locDetails->country;
			$city       = $locDetails->city;
			$state      = $locDetails->state;
			$state_name = $locDetails->state_name;
			list( $data, $response ) = ( new WCOApi() )->wcoGetHandSetDetails( $input, $wco_port_name, $wco_country, $wco_port_type );

			try {
				$data = \GuzzleHttp\json_decode( $response->getBody() );


				if ( isset( $data ) && $data->statusCode === 200 ) {

					if ( $data->gsmaApprovedTac === "No" ) {

						IMEI::insert( [
							'user_device'     => $user_device,
							'checking_method' => $checking_method,
							'imei_number'     => $input_search,
							'result'          => 'Invalid',
							'visitor_ip'      => $ip,
							'user_id'         => $user->id,
							'user_name'       => $user->first_name,
							'created_at'      => new \DateTime(),
							'latitude'        => $lat,
							'longitude'       => $lon,
							'city'            => $city,
							'country'         => $country,
							'state'           => $state,
							'state_name'      => $state_name
						] );
					} 
					else {

						IMEI::insert( [
							'user_device'     => $user_device,
							'checking_method' => $checking_method,
							'imei_number'     => $input_search,
							'result'          => 'Valid',
							'visitor_ip'      => $ip,
							'user_id'         => $user->id,
							'user_name'       => $user->first_name,
							'created_at'      => new \DateTime(),
							'latitude'        => $lat,
							'longitude'       => $lon,
							'city'            => $city,
							'country'         => $country,
							'state'           => $state,
							'state_name'      => $state_name
						] );
					}


					return response()->json( [
						'error'   => false,
						'success' => true,
						'data'    => $data
					] );

				} elseif ( isset( $data ) && ( $data->statusCode === 100 || $data->statusCode === 101 ) ) {
					return response()->json( [
						'error'       => true,
						'status_code' => 100,
						'message'     => trans( 'wco.responses.100' )
					] );
				} elseif ( isset( $data ) && $data->statusCode === 102 ) {
					return response()->json( [
						'error'       => true,
						'status_code' => 102,
						'message'     => trans( 'wco.responses.102' )
					] );
				} elseif ( isset( $data ) && $data->statusCode === 400 ) {
					return response()->json( [
						'error'       => true,
						'status_code' => 400,
						'message'     => trans( 'wco.responses.400' )
					] );
				} elseif ( isset( $data ) && $data->statusCode === 401 ) {
					return response()->json( [
						'error'       => true,
						'status_code' => 401,
						'message'     => trans( 'wco.responses.401' )
					] );
				}


			} catch ( \Exception $ex ) {
				return response()->json( [
					'errors' => true,
				], $ex->getCode() );
			}

		}
		 catch ( TokenExpiredException $exception ) {
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
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function resultsMatch( JWTAuth $JWTAuth, Request $request ) {

		try {

			$JWTAuth->parseToken()->authenticate();

			IMEI::where( 'imei_number', $request->imei )
			    ->update( [
				    'results_matched' => 'Yes'
			    ] );

			return response()->json( [
				'success' => true,
				'status'  => 'results_matched',
				'message' => trans( 'responses.imei.matched' )
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
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function resultsNotMatch( JWTAuth $JWTAuth, Request $request ) {
		try {
			$JWTAuth->parseToken()->authenticate();
			IMEI::where( 'imei_number', $request->imei )
			    ->update( [
				    'results_matched' => 'No'
			    ] );


			return response()->json( [
				'success' => false,
				'status'  => 'results_not_matched',
				'message' => trans( 'responses.imei.not_matched' )
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


}