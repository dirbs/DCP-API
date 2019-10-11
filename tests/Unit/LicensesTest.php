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

use App\LicenseAgreement;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;
use Tests\CreatesApplication;
use Tests\TestCase;

class LicensesTest extends TestCase {
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
	 * Super admin can view counterfeit devices
	 */
	public function userCangettheirRecentUserLicense() {



		$token   = \JWTAuth::fromUser( $this->staff );
		$license = factory( LicenseAgreement::class )->create();
		$this->get( '/api/get-user-license/' . $this->staff->id . '?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'user',
			     'roles',
			     'license',
			     'success',
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 4 );
	}

	/**
	 * @test
	 */
	public function aTokenIsRequiredToGetUserLicense() {

		$this->get( '/api/get-user-license/1' )
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
	 */
	public function aValidTokenIsRequiredToGetUserLicense() {

		$this->get( '/api/get-user-license/1?token=loremipsumtoken' )
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
	 */
	public function userCanUpdateLicensedUser() {
		$role       = factory( Role::class )->create( [
			'slug' => 'staff'
		] );
		$staff_role = $role->where( 'slug', 'staff' )->first();

		$user = factory( User::class )->create( [
			'first_name' => 'staff',
			'last_name'  => 'test',
			'email'      => 'tst@3gca.org',
			'password'   => bcrypt( 'Satff@1234' ),
			'active'     => true,
			'loginCount' => 0,
			'agreement'  => 'Agreed'
		] );


		$user->roles()->attach( $staff_role );
		$token   = \JWTAuth::fromUser( $user );
		$license = factory( LicenseAgreement::class )->create();
		$user->licenses()->attach( $license->id, [ 'type' => 'login' ] );
		if ( $user->loginCount == 0 ) {

			$this->put( '/api/update-user-license/' . $user->id . '?token=' . $token )
			     ->assertStatus( 200 )
			     ->assertJsonStructure( [
				     'user',
				     'roles',
				     'licenses',
				     'success',
				     'status',
			     ] )
			     ->assertSuccessful()
			     ->assertJsonCount( 5 );

			$user->agreement = 'Agreed';
			$user->update();

		}

	}


	/**
	 * @test
	 */
	public function userCanUpdateLicensedUserIfAlreadyHaveAPreviousLicense() {
		$role       = factory( Role::class )->create( [
			'slug' => 'staff'
		] );
		$staff_role = $role->where( 'slug', 'staff' )->first();

		$user = factory( User::class )->create( [
			'first_name' => 'staff',
			'last_name'  => 'test',
			'email'      => 'tst1@3gca.org',
			'password'   => bcrypt( 'Satff@1234' ),
			'active'     => true,
			'loginCount' => 0,
			'agreement'  => 'Agreed'
		] );


		$user->roles()->attach( $staff_role );
		$token   = \JWTAuth::fromUser( $user );
		$license = factory( LicenseAgreement::class, 2 )->create();

		$user->licenses()->attach( $license[0]['id'], [ 'type' => 'login' ] );

		if ( $user->licenses->last()->version != $license[1]['version'] ) {

			$this->put( '/api/update-user-license/' . $user->id . '?token=' . $token )
			     ->assertStatus( 200 )
			     ->assertJsonStructure( [
				     'user',
				     'roles',
				     'licenses',
				     'success',
				     'status',
			     ] )
			     ->assertSuccessful()
			     ->assertJsonCount( 5 );

			$user->licenses()->attach( $license[1]['id'], [ 'type' => 'login' ] );
			$user->agreement = 'Agreed';
			$user->update();

		}

	}


	/**
	 * @test
	 */
	public function aTokenIsRequiredToUpdateUserLicense() {

		$this->put( '/api/update-user-license/1' )
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
	 */
	public function aValidTokenIsRequiredToUpdateUserLicense() {

		$this->put( '/api/update-user-license/1?token=loremipsumtoken' )
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
	 */
	public function userCanUpdateAppDownloadLicense() {
		$role       = factory( Role::class )->create( [
			'slug' => 'staff'
		] );
		$staff_role = $role->where( 'slug', 'staff' )->first();

		$user = factory( User::class )->create( [
			'first_name' => 'staff',
			'last_name'  => 'test',
			'email'      => 'tstt@3gca.org',
			'password'   => bcrypt( 'Satff@1234' ),
			'active'     => true,
			'loginCount' => 0,
			'agreement'  => 'Agreed'
		] );


		$user->roles()->attach( $staff_role );
		$token   = \JWTAuth::fromUser( $user );
		$license = factory( LicenseAgreement::class )->create();

		$user->licenses()->attach( $license->id, [ 'type' => 'app' ] );
		if ( $user->licenses()->wherePivot( 'type', '=', 'app' )->exists() && $license->version == $user->licenses()->wherePivot( 'type', 'app' )->get()->last()->version ) {

			$this->put( '/api/update-user-app-license/' . $user->id . '?token=' . $token )
			     ->assertStatus( 200 )
			     ->assertJsonStructure( [
				     'license',
				     'success',
				     'status',
			     ] )
			     ->assertSuccessful()
			     ->assertJsonCount( 3 );


			$user->licenses()->wherePivot( 'type', 'app' )->updateExistingPivot( $license->id, [ 'updated_at' => Carbon::now() ] );
		}

	}

	/**
	 * @test
	 */
	public function userCanUpdateAppDownloadLicenseIfNotAlreadySignedPreviousLicense() {
		$role       = factory( Role::class )->create( [
			'slug' => 'staff'
		] );
		$staff_role = $role->where( 'slug', 'staff' )->first();

		$user = factory( User::class )->create( [
			'first_name' => 'staff',
			'last_name'  => 'test',
			'email'      => 'tst3@3gca.org',
			'password'   => bcrypt( 'Satff@1234' ),
			'active'     => true,
			'loginCount' => 0,
			'agreement'  => 'Agreed'
		] );


		$user->roles()->attach( $staff_role );
		$token   = \JWTAuth::fromUser( $user );
		$license = factory( LicenseAgreement::class )->create();


		$this->put( '/api/update-user-app-license/' . $user->id . '?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'status',
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );

		$user->licenses()->attach( $license->id, [ 'type' => 'app' ] );


	}

	/**
	 * @test
	 */
	public function aTokenIsRequiredToUpdateUserAppLicense() {

		$this->put( '/api/update-user-app-license/1' )
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
	 */
	public function aValidTokenIsRequiredToUpdateUserAppLicense() {

		$this->put( '/api/update-user-app-license/1?token=loremipsumtoken' )
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
	 */
	public
	function SuperAdminCanGetUserLicensesByTheirIDS() {

		$token = \JWTAuth::fromUser( $this->superadmin );

		$user = factory( User::class )->create();

		$user = User::where( 'id', $user->id )->with( 'licenses' )->first();
		$this->get( '/api/user-licenses/' . $user->id . '?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'user',

		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 */
	public
	function AdminCanGetUserLicensesByTheirIDS() {

		$token = \JWTAuth::fromUser( $this->admin );

		$user = factory( User::class )->create();

		$user = User::where( 'id', $user->id )->with( 'licenses' )->first();
		$this->get( '/api/user-licenses/' . $user->id . '?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'user',

		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 */
	public function aTokenIsRequiredToGetUserLicensesByTheirIDS() {

		$this->get( '/api/user-licenses/1' )
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
	 */
	public function aValidTokenIsRequiredToGetUserLicensesByTheirIDS() {


		$this->get( '/api/user-licenses/1?token=loremipsumtoken' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is invalid'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}


}
