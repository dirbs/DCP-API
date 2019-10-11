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


use App\CounterFiet;
use App\IMEI;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class UsersActivitiesController {

	/**
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws JWTException
	 */
	public function get_users_activity( JWTAuth $JWTAuth ) {

		$user = $JWTAuth->parseToken()->authenticate();
		$user = User::find( $user->id );


		$users_activity = IMEI::orderBy( 'id', 'desc' )->paginate( 5 )->toArray();


		$not_found = IMEI::orderBy( 'id', 'desc' )->where( 'result', 'Invalid' )->count();
		$found     = IMEI::orderBy( 'id', 'desc' )->where( 'result', 'Valid' )->count();

		$created_imei   = IMEI::orderBy( 'id', 'desc' )->get( [ 'created_at' ] );
		$visitorTraffic = IMEI::select( 'created_at' )
		                      ->get()
		                      ->groupBy( function ( $date ) {
			                      return Carbon::parse( $date->created_at )->format( 'm' );
		                      } );

		$visitorReports = CounterFiet::createdAtFeedByMonths();


		$visitorTrafficByMonths = IMEI::createdAtFeedByDate( $user );


		$totalIMEI               = IMEI::all()->count();
		$totalUsers              = User::all()->count();
		$totalCounterfeitDevices = CounterFiet::all()->count();
		$deactivatedUsers        = $user->deactivatedUsersCount();
		$totalNotMatchedImeis    = IMEI::where( 'results_matched', 'No' )->count();
		$totalMatchedImeis       = IMEI::where( 'results_matched', 'Yes' )->count();

		$latLongIMEIHeatMapSearches = IMEI::select( 'latitude', 'longitude' )->get();
		$counterfeitHeatMapSearches = CounterFiet::select( 'latitude', 'longitude' )->get();

		if ( request()->hasHeader( 'x-localization' ) !== null && request()->header( 'x-localization' ) === 'vi' ) {

			$vi_data = $users_activity['data'];

			$vi_activities = collect( $vi_data )->map( function ( $item ) {
				$item['result'] = trans( 'logs.imei_search.invalid' );
				if ( isset( $item['user_device'] ) ) {
					if ( $item['user_device'] === 'web' ) {
						$item['user_device'] = trans( 'logs.imei_search.web' );
					} elseif ( $item['user_device'] === 'AndroidApp' ) {
						$item['user_device'] = trans( 'logs.imei_search.android' );
					}
				}

				if ( isset( $item['checking_method'] ) ) {
					if ( $item['checking_method'] === 'manual' ) {
						$item['checking_method'] = trans( 'logs.imei_search.manual' );
					} elseif ( $item['checking_method'] === 'scan' ) {
						$item['checking_method'] = trans( 'logs.imei_search.scan' );
					}
				}


				return $item;
			} );


			$vi_col = ( new Collection( $vi_activities ) )->paginate( 5 )->toArray();


			return response()->json( [
				'data' => [
					'latLongIMEIHeatMapSearches' => $latLongIMEIHeatMapSearches,
					'counterfeitHeatMapSearches' => $counterfeitHeatMapSearches,
					'visitorReports'             => $visitorReports,
					'byyears'                    => $visitorTrafficByMonths,
					'totalIMEI'                  => $totalIMEI,
					'totalUsers'                 => $totalUsers,
					'totalCounterFeitDevices'    => $totalCounterfeitDevices,
					'totalNotMatchedImeis'       => $totalNotMatchedImeis,
					'totalMatchedImeis'          => $totalMatchedImeis,
					'deactivatedUsers'           => $deactivatedUsers,
					'found'                      => $found,
					'not_found'                  => $not_found,
					'imei_created_at'            => $created_imei,
					'visitor_traffic'            => $visitorTraffic,
					'activity'                   => $vi_col,
				]
			] );
		}

		return response()->json( [
			'data' => [
				'latLongIMEIHeatMapSearches' => $latLongIMEIHeatMapSearches,
				'counterfeitHeatMapSearches' => $counterfeitHeatMapSearches,
				'visitorReports'             => $visitorReports,
				'byyears'                    => $visitorTrafficByMonths,
				'totalIMEI'                  => $totalIMEI,
				'totalUsers'                 => $totalUsers,
				'totalCounterFeitDevices'    => $totalCounterfeitDevices,
				'totalNotMatchedImeis'       => $totalNotMatchedImeis,
				'totalMatchedImeis'          => $totalMatchedImeis,
				'deactivatedUsers'           => $deactivatedUsers,
				'activity'                   => $users_activity,
				'found'                      => $found,
				'not_found'                  => $not_found,
				'imei_created_at'            => $created_imei,
				'visitor_traffic'            => $visitorTraffic,
			]
		] );

	}


	/**
	 * @param Request $request
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function search_users_activity( Request $request, JWTAuth $JWTAuth ) {
		try {

			$user         = $JWTAuth->parseToken()->authenticate();
			$search_query = strtolower( $request->to_search );

			$users_activity = IMEI::where( 'user_id', $user->id )
			                      ->where( 'imei_number', 'ILIKE', '%' . $search_query . '%' )
			                      ->orderBy( 'id', 'desc' )->paginate( 10 );

			$vi_users_activity = IMEI::where( 'user_id', $user->id )
			                         ->where( 'imei_number', 'ILIKE', '%' . $search_query . '%' )
			                         ->orderBy( 'id', 'desc' )->get();

			if ( request()->hasHeader( 'x-localization' ) !== null && request()->header( 'x-localization' ) === 'vi' ) {

				$vi_activities = $vi_users_activity->map( function ( $item ) {
					if ( isset( $item['result'] ) ) {
						if ( $item['result'] === 'Valid' ) {
							$item['result'] = trans( 'logs.imei_search.valid' );
						} elseif ( $item['result'] === 'Invalid' ) {
							$item['result'] = trans( 'logs.imei_search.invalid' );
						}
					}
					if ( isset( $item['user_device'] ) ) {
						if ( $item['user_device'] === 'web' ) {
							$item['user_device'] = trans( 'logs.imei_search.web' );
						} elseif ( $item['user_device'] === 'AndroidApp' ) {
							$item['user_device'] = trans( 'logs.imei_search.android' );
						}
					}

					if ( isset( $item['checking_method'] ) ) {
						if ( $item['checking_method'] === 'manual' ) {
							$item['checking_method'] = trans( 'logs.imei_search.manual' );
						} elseif ( $item['checking_method'] === 'scan' ) {
							$item['checking_method'] = trans( 'logs.imei_search.scan' );
						}
					}
					if ( isset( $item['results_matched'] ) ) {
						if ( $item['results_matched'] === 'Yes' ) {
							$item['results_matched'] = trans( 'logs.imei_search.yes' );
						} elseif ( $item['results_matched'] === 'No' ) {
							$item['results_matched'] = trans( 'logs.imei_search.no' );
						}
					}

					return $item;
				} );

				$vi_c = ( new \App\Support\Collection( $vi_activities ) )
					->paginate( 10 )->toArray();

				return response()->json( [
					'activity' => $vi_c,
				] );
			}
			return response()->json( [
				'activity' => $users_activity,
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


	/**
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function users_activity( JWTAuth $JWTAuth ) {
		try {
			$user = $JWTAuth->parseToken()->authenticate();

			$users_activity = IMEI::where( 'user_id', '=', $user->id )->orderBy( 'i_m_e_is.id', 'desc' )->paginate( 10 );


			return response()->json( [
				'activity' => $users_activity,
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