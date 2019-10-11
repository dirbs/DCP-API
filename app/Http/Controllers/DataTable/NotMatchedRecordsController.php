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
namespace App\Http\Controllers\DataTable;

use App\Http\Controllers\Controller;
use App\IMEI;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class NotMatchedRecordsController extends Controller {


	/**
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getRecords( Request $request ) {
		$search_query        = $request->searchTerm;
		$perPage             = $request->per_page;
		$not_matched_records = IMEI::orderBy( 'id', 'desc' )
		                           ->where( 'results_matched', 'No' )
		                           ->where( 'imei_number', 'ILIKE', '%' . $search_query . '%' )
		                           ->paginate( $perPage )
		                           ->toArray();

		$vi_not_matched_records = IMEI::orderBy( 'id', 'desc' )
		                              ->where( 'results_matched', 'No' )
		                              ->where( 'imei_number', 'ILIKE', '%' . $search_query . '%' )
		                              ->get();

		if ( request()->hasHeader( 'x-localization' ) !== null && request()->header( 'x-localization' ) === 'vi' ) {
			$vi_activities = $vi_not_matched_records->map( function ( $item ) {
				if ( isset( $item['result'] ) ) {
					if ( $item['result'] === 'Valid' ) {
						$item['result'] = trans( 'logs.imei_search.valid' );
					} elseif ( $item['result'] === 'Invalid' ) {
						$item['result'] = trans( 'logs.imei_search.invalid' );
					}
				}
				$item['results_matched'] = trans( 'logs.imei_search.not_found' );
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

			if ( $search_query ) {
				$vi_col['searchTerm'] = $search_query ?: '';
			} else {
				$vi_col['searchTerm'] = $search_query ? null : '';
			}
			$vi_col = ( new Collection( $vi_activities ) )->paginate( 10 )->toArray();

			return response()->json( [
				'activity' => $vi_col,
			] );

		}

		if ( $search_query ) {
			$not_matched_records['searchTerm'] = $search_query ?: '';
		} else {
			$not_matched_records['searchTerm'] = $search_query ? null : '';
		}

		return response()->json( [
			'activity' => $not_matched_records,
		] );

	}

}