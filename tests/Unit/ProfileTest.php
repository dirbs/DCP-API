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

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;
use Tests\TestCase;

class ProfileTest extends TestCase {
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
	 * Super admin can view their profile
	 */
	public function superAdminCanViewtheirProfile() {


		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->get( '/api/profile?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 *  admin can view their profile
	 */
	public function AdminCanViewtheirProfile() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->get( '/api/profile?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Staff can view their profile
	 */
	public function StaffCanViewtheirProfile() {
		$token = \JWTAuth::fromUser( $this->staff );
		$this->get( '/api/profile?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}


	/**
	 * @test
	 * A valid token is required for viewing profile
	 */
	public function aTokenIsRequiredforViewingProfile() {

		$this->get( '/api/profile' )
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
	 * A valid token is required for viewing profile
	 */
	public function aValidTokenIsRequiredforViewingProfile() {

		$this->get( '/api/profile?token=sdasfasdsad' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is invalid'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}


	/** @test
	 * Super Admin can get their profile data
	 */
	public function superAdminCanGetTheirProfileData() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->get( '/api/profile/' . $this->superadmin->id . '/edit?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/** @test
	 * Admin can get their profile data
	 */
	public function adminCanGetTheirProfileData() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->get( '/api/profile/' . $this->admin->id . '/edit?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/** @test
	 * Staff can get their profile data
	 */
	public function staffCanGetTheirProfileData() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->get( '/api/profile/' . $this->staff->id . '/edit?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * A valid token is required for getting profile data
	 */
	public function aTokenIsRequiredforProfileData() {

		$this->get( '/api/profile/1/edit' )
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
	 * A valid token is required for getting profile data
	 */
	public function aValidTokenIsRequiredforProfileData() {

		$this->get( '/api/profile/1/edit?token=sadsafasdsad' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is invalid'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}


	/** @test
	 * Super Admin can get their profile password data
	 */
	public function superAdminCanGetTheirProfilePasswordData() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->get( '/api/profile/' . $this->superadmin->id . '/password?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/** @test
	 * Admin can get their profile password data
	 */
	public function adminCanGetTheirProfilePasswordData() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->get( '/api/profile/' . $this->admin->id . '/password?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/** @test
	 * Staff can get their profile password data
	 */
	public function staffCanGetTheirProfilePasswordData() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->get( '/api/profile/' . $this->staff->id . '/password?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'data' => [
				     'user',
				     'roles'
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * A valid token is required for getting profile password data
	 */
	public function aTokenIsRequiredforProfilePasswordData() {

		$this->get( '/api/profile/1/edit' )
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
	 * A valid token is required for getting profile password data
	 */
	public function aValidTokenIsRequiredforProfilePasswordData() {

		$this->get( '/api/profile/1/edit?token=sadasfsd' )
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
	 *
	 * Super Admin can edit their Passwords
	 */
	public function superAdminCanEditTheirPasswords() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->put( '/api/profile/' . $this->superadmin->id . '/password?token=' . $token, [
			'old_password'          => 'Admin@1234',
			'password'              => 'Admin@123',
			'password_confirmation' => 'Admin@123'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'data' => [
				     'user',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}


	/**
	 * @test
	 *
	 * Admin can edit their Passwords
	 */
	public function AdminCanEditTheirPasswords() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->put( '/api/profile/' . $this->admin->id . '/password?token=' . $token, [
			'old_password'          => 'Admin@1234',
			'password'              => 'Admin@123',
			'password_confirmation' => 'Admin@123'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'data' => [
				     'user',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}

	/**
	 * @test
	 *
	 * Staff can edit their Passwords
	 */
	public function StaffCanEditTheirPasswords() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/profile/' . $this->staff->id . '/password?token=' . $token, [
			'old_password'          => 'Staff@1234',
			'password'              => 'Admin@123',
			'password_confirmation' => 'Admin@123'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'data' => [
				     'user',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}


	/**
	 * @test
	 * A valid token is required for profile password edit
	 */
	public function aTokenIsRequiredforProfilePasswordEdit() {

		$this->put( '/api/profile/1/password', [
			'old_password'          => 'Admin@1234',
			'password'              => 'Admin@123',
			'password_confirmation' => 'Admin@123'
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
	 * A valid token is required for profile password edit
	 */
	public function aValidTokenIsRequiredforProfilePasswordEdit() {

		$this->put( '/api/profile/1/password?token=jsajjjsdjjs', [
			'old_password'          => 'Admin@1234',
			'password'              => 'Admin@123',
			'password_confirmation' => 'Admin@123'
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
	 *
	 * Valid Data required for editting passwords
	 */
	public function validDataIsRequiredForEdittingPassword() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/profile/' . $this->staff->id . '/password?token=' . $token, [
			'old_password'          => 'Staff@',
			'password'              => 'Admin@123',
			'password_confirmation' => 'Admin@123'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'root',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}

	/**
	 * @test
	 *
	 * Strong passwords required for editting passwords
	 */
	public function strongPasswordsRequiredForEdittingPassword() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->put( '/api/profile/' . $this->superadmin->id . '/password?token=' . $token, [
			'old_password'          => 'Admin@1234',
			'password'              => 'secret',
			'password_confirmation' => 'secret'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'root',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}

	/**
	 * @test
	 *
	 * password should match confirmation password for editting passwords
	 */
	public function passwordShouldMatchConfirmationPasswordForEdittingPassword() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->put( '/api/profile/' . $this->admin->id . '/password?token=' . $token, [
			'old_password'          => 'Admin@1234',
			'password'              => 'Admin@123',
			'password_confirmation' => 'secret'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'root',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );
	}


	/**
	 * @test
	 *
	 * Super Admin can edit their Profile
	 */
	public function superAdminCanEditTheirProfile() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->put( '/api/profile/' . $this->superadmin->id . '/edit?token=' . $token, [
			'first_name' => 'admin',
			'last_name'  => 'super',
			'email'      => 'newsuper@3gca.org'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'data' => [
				     'user',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}


	/**
	 * @test
	 *
	 * Admin can edit their Profile
	 */
	public function AdminCanEditTheirProfile() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->put( '/api/profile/' . $this->admin->id . '/edit?token=' . $token, [
			'first_name' => 'admin',
			'last_name'  => 'new',
			'email'      => 'newadmin@3gca.org'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'data' => [
				     'user',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}

	/**
	 * @test
	 *
	 * Staff can edit their Profile
	 */
	public function StaffCanEditTheirProfile() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/profile/' . $this->staff->id . '/edit?token=' . $token, [
			'first_name' => 'staff',
			'last_name'  => 'new',
			'email'      => 'newstaff@3gca.org'
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'data' => [
				     'user',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );

	}


	/**
	 * @test
	 * A valid token is required for profile edit
	 */
	public function aTokenIsRequiredforProfileEdit() {

		$this->put( '/api/profile/1/edit', [
			'first_name' => 'admin',
			'last_name'  => 'super',
			'email'      => 'newsuper@3gca.org'
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
	 * A valid token is required for profile edit
	 */
	public function avalidTokenIsRequiredforProfileEdit() {

		$this->put( '/api/profile/1/edit?token=dassdasdasd', [
			'first_name' => 'admin',
			'last_name'  => 'super',
			'email'      => 'newsuper@3gca.org'
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
	 *
	 * email is required for editing profile
	 */
	public function emailIsREquiredForEdittingProfile() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/profile/' . $this->staff->id . '/edit?token=' . $token, [
			'first_name' => 'lorem',
			'last_name'  => 'ipsum',

		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'root',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );
	}


	/**
	 * @test
	 *
	 * First name is required for editing profile
	 */
	public function firstNameIsREquiredForEdittingProfile() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/profile/' . $this->staff->id . '/edit?token=' . $token, [

			'last_name' => 'ipsum',
			'email'     => 'staff@3gca.org'

		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'root',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 *
	 * last name is required for editing profile
	 */
	public function lastNameIsREquiredForEdittingProfile() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/profile/' . $this->staff->id . '/edit?token=' . $token, [
			'first_name' => 'lorem',
			'email'      => 'test@3gca.org'

		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'root',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );
	}


	/**
	 * @test
	 *
	 * vald email is required for editing profile
	 */
	public function validEmailIsREquiredForEdittingProfile() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->put( '/api/profile/' . $this->staff->id . '/edit?token=' . $token, [
			'first_name' => 'lorem',
			'last_name'  => 'ipsum',
			'email'      => 'lorem@gmail.com'

		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'root',
			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );
	}
}
