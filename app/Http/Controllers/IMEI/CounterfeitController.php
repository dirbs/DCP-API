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
use App\Libraries\WCOApi;
use Illuminate\Http\Request;
use Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class CounterfeitController {

	/**
	 * @var array
	 */
	protected $allowedFileExtensions = [
		'png',
		'jpg',
		'jpeg',
		'gif',
	];


	/**
	 * @param Request $request
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function searchCounterImages( Request $request, JWTAuth $JWTAuth ) {

		try {
			$JWTAuth->parseToken()->authenticate();

			$counter = CounterFiet::where( 'id', '=', $request->id )->first();
			$images  = array();

			if ( env( 'IS_AWS' ) === false ) {
				foreach ( explode( '|', $counter->image_path ) as $image ) {
					if ( file_exists( ( storage_path( 'app/' . $image ) ) ) ) {
						$images[] = base64_encode( file_get_contents( storage_path( 'app/' . $image ) ) );
					} else {
						break;
					}
				}
				if ( request()->hasHeader( 'x-localization' ) !== null && request()->header( 'x-localization' ) === 'vi' ) {

					if ( isset( $counter->status ) ) {
						if ( $counter->status === 'Found' ) {
							$counter->status = trans( 'logs.imei_search.found' );
						} elseif ( $counter->status === 'Not Found' ) {
							$counter->status = trans( 'logs.imei_search.not_found' );
						} elseif ( $counter->status === 'Not Matched' ) {
							$counter->status = trans( 'logs.imei_search.not_matched' );
						} elseif ( $counter->status === 'Matched' ) {
							$counter->status = trans( 'logs.imei_search.matched' );
						}
					}

					return response()->json( [
						'data'   => $counter,
						'images' => $images
					] );
				}

				return response()->json( [
					'data'   => $counter,
					'images' => $images
				] );
			} else {
				if ( request()->hasHeader( 'x-localization' ) !== null && request()->header( 'x-localization' ) === 'vi' ) {

					if ( isset( $counter->status ) ) {
						if ( $counter->status === 'Found' ) {
							$counter->status = trans( 'logs.imei_search.found' );
						} elseif ( $counter->status === 'Not Found' ) {
							$counter->status = trans( 'logs.imei_search.not_found' );
						} elseif ( $counter->status === 'Not Matched' ) {
							$counter->status = trans( 'logs.imei_search.not_matched' );
						} elseif ( $counter->status === 'Matched' ) {
							$counter->status = trans( 'logs.imei_search.matched' );
						}
					}

					return response()->json( [
						'data' => $counter,
					] );
				}

				return response()->json( [
					'data' => $counter
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
	 * @param $image
	 *
	 * @return bool
	 */
	protected function is_image( $image ) {
		$image_type = exif_imagetype( $image );
		if ( in_array( $image_type, [
			IMAGETYPE_GIF,
			IMAGETYPE_JPEG,
			IMAGETYPE_PNG,
			IMAGETYPE_BMP
		], true ) ) {
			return true;
		}

		return false;
	}


	/**
	 * @param Request $request
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function counterFietDevices( Request $request, JWTAuth $JWTAuth ) {
		try {
			$user = $JWTAuth->parseToken()->authenticate();

			if ( isset( $request->counterImage ) && count( $request->counterImage ) ) {
				$image = $this->createImage( $request->counterImage );
			}
			if ( isset( $image ) && ! $image ) {
				return response()->json( [
					'error'    => true,
					'messages' => trans( 'logs.counterfeit.image.bad_format' )
				], 205 );
			}

			$validator = \Validator::make( $request->only( 'imei_number', 'brand_name', 'model_name', 'description', 'address', 'store_name' ), [
				'imei_number' => 'required|numeric|digits_between:8,16',
				'brand_name'  => 'required',
				'model_name'  => 'required',
				'store_name'  => 'required',
				'description' => 'required',
				'address'     => 'required'
			] );


			if ( $validator->fails() ) {
				return response()->json( [
					'error'    => true,
					'messages' => $validator->errors()
				] );
			}
			$brand_name   = $request->brand_name;
			$model_name   = $request->model_name;
			$description  = $request->description;
			$input_search = $request->imei_number;
			$address      = $request->address;
			$store_name   = $request->store_name;

			$wco_port_name = env( 'WCO_PORT_NAME' );
			$wco_port_type = env( 'WCO_PORT_TYPE' );
			$wco_country   = env( 'WCO_COUNTRY' );
			$input         = substr( $input_search, 0, 8 );
			$ip            = config( 'app.get_public_ip' );


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

						if ( isset( $image ) ) {
							CounterFiet::insert( [
								'imei_number' => $input_search,
								'result'      => 'Not Found',
								'brand_name'  => $brand_name,
								'model_name'  => $model_name,
								'description' => $description,
								'store_name'  => $store_name,
								'address'     => $address,
								'user_id'     => $user->id,
								'status'      => 'Not Found',
								'user_name'   => $user->first_name,
								'created_at'  => new \DateTime(),
								'image_path'  => $image,
								'latitude'    => $lat,
								'longitude'   => $lon,
								'city'        => $city,
								'country'     => $country,
								'state'       => $state,
								'state_name'  => $state_name
							] );
						} elseif ( ! isset( $image ) ) {
							CounterFiet::insert( [
								'imei_number' => $input_search,
								'result'      => 'Not Found',
								'brand_name'  => $brand_name,
								'model_name'  => $model_name,
								'description' => $description,
								'store_name'  => $store_name,
								'address'     => $address,
								'user_id'     => $user->id,
								'status'      => 'Not Found',
								'user_name'   => $user->first_name,
								'created_at'  => new \DateTime(),
								'latitude'    => $lat,
								'longitude'   => $lon,
								'city'        => $city,
								'country'     => $country,
								'state'       => $state,
								'state_name'  => $state_name
							] );
						}

						return response()->json( [
							'success' => true,
							'message' => trans( 'responses.counterfeit.success' )
						] );

					} else {

						if ( isset( $image ) ) {

							CounterFiet::insert( [
								'imei_number' => $input_search,
								'result'      => 'Not Found',
								'brand_name'  => $brand_name,
								'model_name'  => $model_name,
								'description' => $description,
								'store_name'  => $store_name,
								'address'     => $address,
								'user_id'     => $user->id,
								'user_name'   => $user->first_name,
								'status'      => 'Not Matched',
								'created_at'  => new \DateTime(),
								'image_path'  => $image,
								'latitude'    => $lat,
								'longitude'   => $lon,
								'city'        => $city,
								'country'     => $country,
								'state'       => $state,
								'state_name'  => $state_name
							] );
						} elseif ( ! isset( $image ) ) {
							CounterFiet::insert( [
								'imei_number' => $input_search,
								'result'      => 'Not Found',
								'brand_name'  => $brand_name,
								'model_name'  => $model_name,
								'description' => $description,
								'store_name'  => $store_name,
								'address'     => $address,
								'user_id'     => $user->id,
								'user_name'   => $user->first_name,
								'status'      => 'Not Matched',
								'created_at'  => new \DateTime(),
								'latitude'    => $lat,
								'longitude'   => $lon,
								'city'        => $city,
								'country'     => $country,
								'state'       => $state,
								'state_name'  => $state_name
							] );
						}


						return response()->json( [
							'success' => true,
							'message' => trans( 'responses.counterfeit.success' )

						] );
					}

				}

			} catch ( \Exception $ex ) {
				return response()->json( [
					'errors' => true,
				], $ex->getCode() );
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
	 * @param Request $request
	 *
	 * @return bool|string
	 */
	public function createImage( $counterImage ) {


		if ( isset( $counterImage ) && count( $counterImage ) <= 5 ) {

			try {
				$images = array();
				foreach ( $counterImage as $value ) {
					if ( $this->is_image( $value ) === false ) {
						return false;
					}

					if ( $this->isAllowedFile( $value ) === false ) {
						return false;
					}

					$imageName = uniqid() . '.' . $value->getClientOriginalExtension();

					if ( ( env( 'IS_AWS' ) ) === false ) {

						$s3 = \Storage::disk( 'local' );
						if ( $s3->put( $imageName, file_get_contents( $value ) ) ) {
							$images[] = $imageName;
							$url      = \Storage::url( $imageName );

						} else {
							return false;
						}
					} else {
						$s3 = \Storage::disk( 's3' );
						if ( $s3->put( $imageName, file_get_contents( $value ) ) ) {
							$images[] = $imageName;
						} else {
							return false;
						}
					}

				}

				$images = implode( '|', $images );

				return $images;

			} catch ( \Exception $e ) {
				return false;
			}

		} else {
			return false;
		}


	}


	/**
	 * @param UploadedFile $file
	 *
	 * @return bool
	 */
	protected function isAllowedFile( UploadedFile $file ) {

		return in_array(
			$file->getClientOriginalExtension(),
			$this->allowedFileExtensions
		);
	}


	/**
	 * @param $id
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy( $id ) {

		$counter = CounterFiet::findOrFail( $id );

		$counter->delete();

		return response()->json( [
			'success' => true,
		] );

	}


}