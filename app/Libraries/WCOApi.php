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
namespace App\Libraries;


use GuzzleHttp\Client;

class WCOApi {


	/**
	 * @param $input
	 * @param $wco_port_name
	 * @param $wco_country
	 * @param $wco_port_type
	 *
	 * @return array
	 */
	public function wcoGetHandSetDetails( $input, $wco_port_name, $wco_country, $wco_port_type ): array {
		if ( ! \defined( 'AES_128_ECB' ) ) {
			\define( 'AES_128_ECB', 'aes-128-ecb' );
		}


		$encryption_key  = config( 'app.wco_encryption_key' );
		$iv              = openssl_random_pseudo_bytes( openssl_cipher_iv_length( AES_128_ECB ) );
		$AuthToken       = config( 'app.wco_auth_token' );
		$Password        = config( 'app.wco_auth_password' );
		$Organization_Id = config( 'app.wco_organization_id' );
		$deviceId        = $input;

		$Hashvalue = hash( 'SHA256', $AuthToken . $Password . $deviceId );
		$data      = "GSMA" . $Organization_Id . "=" . $Hashvalue;
		$encrypted = openssl_encrypt( $data, AES_128_ECB, $encryption_key, 0, $iv );

		$request  = new Client();
		$response = $request->post( config( 'app.wco_api_url' ),
			[
				'headers' => [
					'Authorisation' => $encrypted,
					'Content-Type'  => 'application/json'
				],
				'json'    => [
					'deviceId' => $input,
					'portName' => $wco_port_name,
					'country'  => $wco_country,
					'portType' => $wco_port_type
				]
			]
		);

		return array( $data, $response );
	}

}