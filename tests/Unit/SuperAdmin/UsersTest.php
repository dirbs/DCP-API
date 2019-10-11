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
namespace Tests\Unit\SuperAdmin;

use App\Mail\Auth\SendPasswordSetMail;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;
use Tests\TestCase;

class UsersTest extends TestCase {
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
	 * Super Admin can view all users
	 */
	public function superAdminCanViewAllUsers() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$users = User::with( 'roles', 'permissions', 'licenses' )->paginate( 10 )->toArray();
		$this->get( '/api/get-users?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSuccessful()
		     ->assertJson(
			     [
				     'data' => [
					     'users' => [
						     'data' => []
					     ]
				     ]
			     ]
		     )
		     ->assertJsonStructure( [
			     'data' => [
				     'users'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * Admin canNOT view all users
	 */
	public function AdminCannotViewAllUsers() {

		$token = \JWTAuth::fromUser( $this->admin );

		$this->get( '/api/get-users?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Staff canNOT view all users
	 */
	public function StaffCannotViewAllUsers() {

		$token = \JWTAuth::fromUser( $this->staff );

		$this->get( '/api/get-users?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * A valid token is required for viewing all users
	 */
	public function aTokenIsRequiredforViewingUsers() {

		$this->get( '/api/get-users' )
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
	 * A valid token is required for viewing all users
	 */
	public function aValidTokenIsRequiredforViewingUsers() {

		$this->get( '/api/get-users?token=loremippsumtoken' )
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
	 * A valid token is required for creating newusers
	 */
	public function aTokenIsRequiredforCreatingUsers() {

		$this->post( '/api/create-user' )
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
	 * A valid token is required for creating newusers
	 */
	public function aValidTokenIsRequiredforCreatingUsers() {

		$this->post( '/api/create-user?token=loremsipmtoken' )
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
	 * Super Admin can create users
	 */
	public function SuperAdminCanCreateUserss() {
		Mail::fake();

		$token    = \JWTAuth::fromUser( $this->superadmin );
		$new_user = [
			'first_name'            => 'lorem',
			'last_name'             => 'ipsum',
			'email'                 => 'test@3gca.org',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'staff'
		];
		$role     = Role::where( 'slug', 'staff' )->first();

		$response = $this->post( '/api/create-user?token=' . $token, $new_user,
			[
				'Accept' => 'application/json'
			]
		);
		$user     = User::create( [
			'first_name' => 'lorem',
			'last_name'  => 'ipsum',
			'email'      => 'test1@3gca.org',
			'password'   => bcrypt( 'Staff@123' ),
			'active'     => true,
		] );

		$user->roles()->attach( $role );
		$mailable = new SendPasswordSetMail( $user );
		Mail::send( $mailable );
		Mail::assertSent( SendPasswordSetMail::class );
		Mail::to( $new_user['email'] )->send( new SendPasswordSetMail( $this->superadmin ) );
		$response->assertJsonStructure( [
			'data' => [
				'user'
			]
		] );

	}

	/**
	 * @test
	 * First NAme required to create users
	 */
	public function firstNameIsRequredToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->post( '/api/create-user?token=' . $token, [
			'last_name'             => 'ipsum',
			'email'                 => 'test@3gca.org',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'first_name',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Last Name is required to create users
	 */
	public function lastNameIsRequredToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'email'                 => 'test@3gca.org',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'last_name',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Email is required to create users
	 */
	public function EmailIsRequredToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'email',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Email mustbe unique to to create users
	 */
	public function EmailshouldBeUniqueToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'email'                 => $this->superadmin->email,
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'email',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Email mustbe unique to to create users
	 */
	public function EmailshouldBeOf3gDomainToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'email'                 => 'test@gmail.com',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'email',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Password is required to create users
	 */
	public function PasswordIsREquiredToCreateUsers() {
		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'email'                 => 'test@gmail.com',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [

			     'errors' => [
				     'password',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Password is required to create users
	 */
	public function PasswordMustBeStrongToCreateUsers() {

		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'email'                 => 'test@gmail.com',
			'password'              => 'secret',
			'password_confirmation' => 'secret',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'password',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Password is required to create users
	 */
	public function PasswordConfirmationISRequiredToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name' => 'ipsum',
			'last_name'  => 'ipsum',
			'email'      => 'test@gmail.com',
			'password'   => 'Staff@123',
			'role_slug'  => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'password_confirmation',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Password & Passwrod Confirmation should match to create users
	 */
	public function PasswordConfirmationAndPasswordShouldMatchToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'email'                 => 'test@gmail.com',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@1234',
			'role_slug'             => 'staff'
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'password',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Roel Slug is required to create users
	 */
	public function RoleSLugIsRequiredToCreateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'email'                 => 'test@gmail.com',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'role_slug',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Roel Slug is required to create users
	 */
	public function RoleSLugShouldHaveValidKeywordToCreateUsers() {

		$token = \JWTAuth::fromUser( $this->superadmin );

		$this->post( '/api/create-user?token=' . $token, [
			'first_name'            => 'ipsum',
			'last_name'             => 'ipsum',
			'email'                 => 'test@gmail.com',
			'password'              => 'Staff@123',
			'password_confirmation' => 'Staff@123',
			'role_slug'             => 'somenewrole',
		], [
			'Accept' => 'application/json'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'role_slug',
			     ]
		     ] );
	}

	/**
	 * @test
	 * Admin canNOT create users
	 */
	public function AdminCannotCreateUsers() {

		$token = \JWTAuth::fromUser( $this->admin );

		$this->post( '/api/create-user?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Staff canNOT create users
	 */
	public function StaffCannotCreateUsers() {

		$token = \JWTAuth::fromUser( $this->staff );

		$this->post( '/api/create-user?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}


	/**
	 * @test
	 * A valid token is required for deleteing users
	 */
	public function aTokenIsRequiredforDeletingUsers() {

		$user = factory( User::class )->create();
		$this->delete( '/api/delete-user/' . $user->id )
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
	 * A valid token is required for deleteing users
	 */
	public function aValidTokenIsRequiredforDeletingUsers() {
		$user = factory( User::class )->create();
		$this->delete( '/api/delete-user/' . $user->id . '?token=loremipsumtoken' )
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
	 * Admin canNOT delete users
	 */
	public function AdminCannotDeleteUsers() {

		$token = \JWTAuth::fromUser( $this->admin );
		$user  = factory( User::class )->create();
		$this->delete( '/api/delete-user/' . $user->id . '?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Staff canNOT delete users
	 */
	public function StaffCannotDeleteUsers() {

		$token = \JWTAuth::fromUser( $this->staff );
		$user  = factory( User::class )->create();
		$this->delete( '/api/delete-user/' . $user->id . '?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 */
	public function superAdminCanDeleteUsers() {
		$token = \JWTAuth::fromUser( $this->superadmin );
		factory( Role::class )->create( [
			'slug' => 'staff'
		] );
		$staff_role = Role::where( 'slug', 'staff' )->first();

		$staff = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );

		$staff->roles()->attach( $staff_role );

		$response = $this->delete( '/api/delete-user/' . $staff->id . '?token=' . $token );

		$staff->delete();
		$response->assertStatus( 200 );
		$response->assertJsonStructure( [
			'success',
		] );
		$response->assertExactJson( [
			'success' => true
		] );
		$response->assertJsonCount( 1 );

	}

	/**
	 * @test
	 */
	public function superAdminCannotDeleteUsersIfSuperAdmin() {
		$token = \JWTAuth::fromUser( $this->superadmin );
		factory( Role::class )->create( [
			'slug' => 'superadmin'
		] );
		$super_role = Role::where( 'slug', 'superadmin' )->first();

		$super = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );

		$super->roles()->attach( $super_role );

		$response = $this->delete( '/api/delete-user/' . $super->id . '?token=' . $token );


		if ( $super->roles[0]->slug == 'superadmin' ) {
			$response->assertStatus( 401 );
			$response->assertJsonStructure( [
				'error',
				'message'
			] );
			$response->assertJson( [
				'error'   => true,
				'message' => 'Couldnt be deleted'
			] );
		}


	}

	/**
	 * @test
	 * A valid token is required for updating users
	 */
	public function aTokenIsRequiredforUpdatingUsers() {
		$user = factory( User::class )->create();
		$this->put( '/api/update-user/' . $user->id )
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
	 * A valid token is required for updating users
	 */
	public function aValidTokenIsRequiredforUpdatingUsers() {
		$user = factory( User::class )->create();
		$this->put( '/api/update-user/' . $user->id . '?token=loremsipmstoken' )
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
	 * Super Admin can update users
	 */
	public function SuperAdminCanUpdateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$new_user = factory( User::class )->create();


		$response = $this->put( '/api/update-user/' . $new_user->id . '?token=' . $token, [
			'first_name' => 'lorem',
			'last_name'  => 'ipsum',
			'email'      => 'test@3gca.org',
			'role_slug'  => 'staff',
		], [
			'Accept'       => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded'
		] );
		$user     = User::where( 'id', $new_user->id )->with( 'roles' )->first();
		$role     = Role::where( 'slug', 'staff' )->first();

		$response->assertStatus( 200 );
		$user->roles()->sync( $role );
		$response->assertJsonStructure( [
			'success',
			'message'
		] );
		$response->assertSuccessful();
		$response->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Super Admin can update users
	 */
	public function firstNameIsREquiredToUpdateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$new_user = factory( User::class )->create();


		$this->put( '/api/update-user/' . $new_user->id . '?token=' . $token, [
			'last_name' => 'ipsum',
			'email'     => 'test@3gca.org',
			'role_slug' => 'staff',
		], [
			'Accept'       => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'first_name'
			     ]
		     ] );
	}

	/**
	 * @test
	 * Super Admin can update users
	 */
	public function LastNameIsREquiredToUpdateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$new_user = factory( User::class )->create();


		$this->put( '/api/update-user/' . $new_user->id . '?token=' . $token, [
			'first_name' => 'ipsum',
			'email'      => 'test@3gca.org',
			'role_slug'  => 'staff',
		], [
			'Accept'       => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'last_name'
			     ]
		     ] );
	}

	/**
	 * @test
	 * Super Admin can update users
	 */
	public function emailIsREquiredToUpdateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$new_user = factory( User::class )->create();


		$this->put( '/api/update-user/' . $new_user->id . '?token=' . $token, [
			'first_name' => 'ipsum',
			'last_name'  => 'ipsum',
			'role_slug'  => 'staff',
		], [
			'Accept'       => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'email'
			     ]
		     ] );
	}

	/**
	 * @test
	 * Super Admin can update users
	 */
	public function roleSlugIsREquiredToUpdateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$new_user = factory( User::class )->create();


		$this->put( '/api/update-user/' . $new_user->id . '?token=' . $token, [
			'first_name' => 'ipsum',
			'last_name'  => 'ipsum',
			'email'      => 'test@3gca.org'
		], [
			'Accept'       => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'role_slug'
			     ]
		     ] );
	}

	/**
	 * @test
	 * Super Admin can update users
	 */
	public function roleSlugShouldBeValidToUpdateUsers() {


		$token = \JWTAuth::fromUser( $this->superadmin );

		$new_user = factory( User::class )->create();


		$this->put( '/api/update-user/' . $new_user->id . '?token=' . $token, [
			'first_name' => 'ipsum',
			'last_name'  => 'ipsum',
			'email'      => 'test@3gca.org',
			'role_slug'  => 'somenewrole'
		], [
			'Accept'       => 'application/json',
			'Content-Type' => 'application/x-www-form-urlencoded'
		] )
		     ->assertStatus( 422 )
		     ->assertJsonStructure( [
			     'errors' => [
				     'role_slug'
			     ]
		     ] );
	}


	/**
	 * @test
	 * Admin canNOT update users
	 */
	public function AdminCannotUpdateUsers() {

		$token = \JWTAuth::fromUser( $this->admin );
		$user  = factory( User::class )->create();
		$this->put( '/api/update-user/' . $user->id . '?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Staff canNOT update users
	 */
	public function StaffCannotUpdateUsers() {

		$token = \JWTAuth::fromUser( $this->staff );
		$user  = factory( User::class )->create();
		$this->put( '/api/update-user/' . $user->id . '?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}


	/**
	 * @test
	 * A valid token is required for viewing single user
	 */
	public function aTokenIsRequiredforviewingSingleUser() {
		$user = factory( User::class )->create();
		$this->get( '/api/get-user/' . $user->id )
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
	 * A valid token is required for viewing single user
	 */
	public function aValidTokenIsRequiredforviewingSingleUser() {
		$user = factory( User::class )->create();
		$this->get( '/api/get-user/' . $user->id . '?token=loremousmtoken' )
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
	 * Admin canNOT view single user
	 */
	public function AdminCannotViewSingleUser() {

		$token = \JWTAuth::fromUser( $this->admin );
		$user  = factory( User::class )->create();
		$this->get( '/api/get-user/' . $user->id . '?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Staff canNOT view single user
	 */
	public function StaffCannotViewSingleUser() {

		$token = \JWTAuth::fromUser( $this->staff );
		$user  = factory( User::class )->create();
		$this->get( '/api/get-user/' . $user->id . '?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] )
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 */
	public function superAdminCanViewSingleUSer() {
		$token = \JWTAuth::fromUser( $this->superadmin );

		$response = $this->get( '/api/get-user/3?token=' . $token );
		$user     = User::where( 'id', 3 )->with( 'roles' )->first();
		$response->assertStatus( 200 );

		$response->assertJsonStructure( [
			'success',
			'user'
		] )
		         ->assertJsonCount( 2 );

	}


}
