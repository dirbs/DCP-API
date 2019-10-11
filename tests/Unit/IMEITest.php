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
namespace Tests\Unit;

use App\IMEI;
use App\Libraries\WCOApi;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;
use Tests\TestCase;

class IMEITest extends TestCase {
	use MockeryPHPUnitIntegration;
	use RefreshDatabase;
	use BasicTestSuiteSetup;
	protected $superadminrole;
	protected $superadmin;
	protected $admin;
	protected $staff;

	public function setUp() {
		parent::setUp();

		DB::beginTransaction();

		factory( Role::class )->create( [
			'slug' => 'superadmin'
		] );
		$super_admin_role = Role::where( 'slug', 'superadmin' )->first();

		$this->superadmin = factory( User::class )->create( [
			'first_name' => 'dcp super',
			'last_name'  => 'super-admin',
			'email'      => 'super@3gca.org',
			'password'   => bcrypt( 'Admin@1234' ),
			'active'     => true,
			'loginCount' => 1,
			'agreement'  => 'Agreed'
		] );

		$this->superadmin->roles()->attach( $super_admin_role );


		//Admin
		factory( Role::class )->create( [
			'slug' => 'admin'
		] );
		$admin_role = Role::where( 'slug', 'admin' )->first();

		$this->admin = factory( User::class )->create( [
			'first_name' => 'dcpadmin',
			'last_name'  => 'admin',
			'email'      => 'admin@3gca.org',
			'password'   => bcrypt( 'Admin@1234' ),
			'active'     => true,
			'loginCount' => 1,
			'agreement'  => 'Agreed'
		] );

		$this->admin->roles()->attach( $admin_role );

		//Staff
		factory( Role::class )->create( [
			'slug' => 'staff'
		] );
		$staff_role = Role::where( 'slug', 'staff' )->first();

		$this->staff = factory( User::class )->create( [
			'first_name' => 'test',
			'last_name'  => 'staff',
			'email'      => 'staff@3gca.org',
			'password'   => bcrypt( 'Staff@1234' ),
			'active'     => true,
			'loginCount' => 1,
			'agreement'  => 'Agreed'
		] );

		$this->staff->roles()->attach( $staff_role );
		DB::commit();

	}

	/**
	 * @test
	 */
	public function canLookupIMEITestWhengsmaApprovedTacisNo() {

		$token  = \JWTAuth::fromUser( $this->superadmin );
		$result = $this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => '123214132244321',
			'ip'   => '192.168.100.60'
		] );

		$input = '123214132244321';

		$wco_port_name = env( 'WCO_PORT_NAME' );
		$wco_port_type = env( 'WCO_PORT_TYPE' );
		$wco_country   = env( 'WCO_COUNTRY' );
		$ip            = '192.168.100.60';
		$locDetails    = \geoip()->getLocation( $ip );
		$lat           = $locDetails->lat;
		$lon           = $locDetails->lon;
		$country       = $locDetails->country;
		$city          = $locDetails->city;
		$state         = $locDetails->state;
		$state_name    = $locDetails->state_name;

		list( $data, $response ) = ( new WCOApi() )->wcoGetHandSetDetails( $input, $wco_port_name, $wco_country, $wco_port_type );

		$data = \GuzzleHttp\json_decode( $response->getBody() );
		if ( isset( $data ) && $data->statusCode === 200 ) {

			if ( $data->gsmaApprovedTac === "No" ) {
				IMEI::insert( [
					'user_device'     => 'web',
					'checking_method' => 'manual',
					'imei_number'     => $input,
					'result'          => 'Invalid',
					'visitor_ip'      => $ip,
					'user_id'         => $this->superadmin->id,
					'user_name'       => $this->superadmin->first_name,
					'created_at'      => new \DateTime(),
					'latitude'        => $lat,
					'longitude'       => $lon,
					'city'            => $city,
					'country'         => $country,
					'state'           => $state,
					'state_name'      => $state_name
				] );
			}

			$result->assertStatus( 200 )
			       ->assertJsonStructure( [
				       'error',
				       'success',
				       'data'
			       ] )
			       ->assertSuccessful()
			       ->assertJsonCount( 3 );
		}


	}

	/**
	 * @test
	 */
	public function canLookupIMEITestWhengsmaApprovedTacisYes() {


		$token  = \JWTAuth::fromUser( $this->superadmin );
		$result = $this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => '35258307000000',
			'ip'   => '192.168.100.60'
		] );

		$input = '123214132244321';

		$wco_port_name = env( 'WCO_PORT_NAME' );
		$wco_port_type = env( 'WCO_PORT_TYPE' );
		$wco_country   = env( 'WCO_COUNTRY' );
		$ip            = '192.168.100.60';
		$locDetails    = \geoip()->getLocation( $ip );
		$lat           = $locDetails->lat;
		$lon           = $locDetails->lon;
		$country       = $locDetails->country;
		$city          = $locDetails->city;
		$state         = $locDetails->state;
		$state_name    = $locDetails->state_name;

		list( $data, $response ) = ( new WCOApi() )->wcoGetHandSetDetails( $input, $wco_port_name, $wco_country, $wco_port_type );

		$data = \GuzzleHttp\json_decode( $response->getBody() );
		if ( isset( $data ) && $data->statusCode === 200 ) {

			IMEI::insert( [
				'user_device'     => 'web',
				'checking_method' => 'manual',
				'imei_number'     => $input,
				'result'          => 'Invalid',
				'visitor_ip'      => $ip,
				'user_id'         => $this->superadmin->id,
				'user_name'       => $this->superadmin->first_name,
				'created_at'      => new \DateTime(),
				'latitude'        => $lat,
				'longitude'       => $lon,
				'city'            => $city,
				'country'         => $country,
				'state'           => $state,
				'state_name'      => $state_name
			] );


			$result->assertStatus( 200 )
			       ->assertJsonStructure( [
				       'error',
				       'success',
				       'data'
			       ] )
			       ->assertSuccessful()
			       ->assertJsonCount( 3 );
		}


	}

	/**
	 * @test
	 */
	public function cannotLookupIMEIwhenParamsMissing() {
		$token  = \JWTAuth::fromUser( $this->superadmin );
		$result = $this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => '123214132244321',
			'ip'   => '192.168.100.60'
		] );

		$input = '123214132244321';


		list( $data, $response ) = ( new WCOApi() )->wcoGetHandSetDetails( $input, $wco_port_name = null, $wco_country = null, $wco_port_type = null );


		$data = \GuzzleHttp\json_decode( $response->getBody() );


		if ( $data->statusCode === 100 || $data->statusCode === 101 ) {

			$result->assertStatus( 200 );

		}


	}


	/**
	 * @test
	 * Super admin can lookup IMEIs
	 */
	public function superAdminCanLookupIMEI() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => '123214132244321',
			'ip'   => '192.168.100.60'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'success',
			     'data'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}

	/**
	 * @test
	 * admin can lookup IMEIs
	 */
	public function AdminCanLookupIMEI() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => '123214132244321',
			'ip'   => '192.168.100.60'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'success',
			     'data'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}

	/**
	 * @test
	 * staff can lookup IMEIs
	 */
	public function staffCanLookupIMEI() {


		$token = \JWTAuth::fromUser( $this->staff );
		$this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => '123214132244321',
			'ip'   => '192.168.100.60'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'success',
			     'data'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}


	/**
	 * @test
	 * A valid token is required for IMEI LOOkup
	 */
	public function aTokenIsRequiredforIMEILookup() {

		$this->post( '/api/lookup/web/manual', [
			'imei' => '3213232323213',
			'ip'   => '192.168.100.60'
		] )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is missing'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * A valid token is required for IMEI LOOkup
	 */
	public function aValidTokenIsRequiredforIMEILookup() {

		$this->post( '/api/lookup/web/manual?token=loremsipjmstoken', [
			'imei' => '3213232323213',
			'ip'   => '192.168.100.60'
		] )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is invalid'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * IMEI Number is required to lookup IMEI
	 */
	public function imeiNumberisRequiredtoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/lookup/web/manual?token=' . $token, [
			'ip' => '192.168.100.60'
		] )
		     ->assertStatus( 302 );
	}

	/**
	 * @test
	 * IMEI Number must be numeric to lookup IMEI
	 */
	public function imeiNumberMustBeNumerictoLookupIMEI() {


		$user  = factory( User::class )->create( [
			'email' => 'test1@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => 'ipsum',
			'ip'   => '192.168.40.100'
		] )
		     ->assertStatus( 302 );
	}

	/**
	 * @test
	 * IMEI Number must be numeric between 8 to 16 digits to lookup imei
	 */
	public function imeiNumberMustBeNumericBetween8to16DigitstoLookupIMEI() {


		$user  = factory( User::class )->create( [
			'email' => 'test2@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/lookup/web/manual?token=' . $token, [
			'imei' => '12334',
			'ip'   => '192.168.40.100'
		] )
		     ->assertStatus( 302 );
	}

	/**
	 * @test
	 * Super admin can mark devices as matched
	 */
	public function superadminCanMarkDevicesasMatched() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->put( '/api/results-matched/123214132244321?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'status',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}

	/**
	 * @test
	 * admin can mark devices as matched
	 */
	public function AdminCanMarkDevicesasMatched() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->put( '/api/results-matched/123214132244321?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'status',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}


	/**
	 * @test
	 * Staff can mark devices as matched
	 */
	public function staffCanMarkDevicesasMatched() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/results-matched/123214132244321?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'status',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}

	/**
	 * @test
	 * A valid token is required for marking  IMEI as matched
	 */
	public function aTokenIsRequiredformarkingIMEIasMatched() {

		$this->put( '/api/results-matched/123214132244321' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is missing'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * A valid token is required for marking  IMEI as matched
	 */
	public function aValidTokenIsRequiredformarkingIMEIasMatched() {

		$this->put( '/api/results-matched/123214132244321?token=loremsipmtokn' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is invalid'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * Super admin can mark devices as mis-matched
	 */
	public function superadminCanMarkDevicesasMisMatched() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->put( '/api/results-not-matched/123214132244321?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'status',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}

	/**
	 * @test
	 * admin can mark devices as mismatched
	 */
	public function AdminCanMarkDevicesasMisMatched() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->put( '/api/results-not-matched/123214132244321?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'status',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}


	/**
	 * @test
	 * Staff can mark devices as Mis matched
	 */
	public function staffCanMarkDevicesasMisMatched() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/results-not-matched/123214132244321?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'status',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 3 );
	}

	/**
	 * @test
	 * A valid token is required for marking  IMEI as mis matched
	 */
	public function aTokenIsRequiredformarkingIMEIasMisMatched() {

		$this->put( '/api/results-not-matched/123214132244321' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is missing'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * A valid token is required for marking  IMEI as mis matched
	 */
	public function aValidTokenIsRequiredformarkingIMEIasMisMatched() {

		$this->put( '/api/results-not-matched/123214132244321?token=loremsiomtoken' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is invalid'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}


}
