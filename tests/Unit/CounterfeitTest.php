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

use App\CounterFiet;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;
use Tests\TestCase;

class CounterfeitTest extends TestCase {
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
	 * Super admin can mark device as counterfeit
	 */
	public function superAdminCanMarkDeviceAsCounterfeit() {


		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '123214132244321',
			'brand_name'  => 'ipsum',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * admin can mark device as counterfeit
	 */
	public function adminCanMarkDeviceAsCounterfeit() {


		$token = \JWTAuth::fromUser( $this->admin );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '123214132244321',
			'brand_name'  => 'ipsum',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}


	/**
	 * @test
	 * Staff can mark device as counterfeit
	 */
	public function staffCanMarkDeviceAsCounterfeit() {


		$token = \JWTAuth::fromUser( $this->staff );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '123214132244321',
			'brand_name'  => 'ipsum',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'success',
			     'message'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * A valid token is required for marking device as counterfeit
	 */
	public function aTokenIsRequiredformarkingDeviceAsCounterfeit() {

		$this->post( '/api/counterfiet' )
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
	 * A valid token is required for marking device as counterfeit
	 */
	public function aValidTokenIsRequiredformarkingDeviceAsCounterfeit() {

		$this->post( '/api/counterfiet?token=loremsomtoken' )
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
	 * IMEI Number is required to mark device as counterfeit
	 */
	public function imeiNumberisRequiredtoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/counterfiet?token=' . $token, [
			'brand_name'  => 'ipsum',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'messages'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * IMEI Number must be numeric to mark device as counterfeit
	 */
	public function imeiNumberMustBeNumerictoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => 'ipsum',
			'brand_name'  => 'ipsum',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'messages'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * IMEI Number must be numeric between 8 to 16 digits to mark device as counterfeit
	 */
	public function imeiNumberMustBeNumericBetween8to16DigitstoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '123432',
			'brand_name'  => 'ipsum',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'messages'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Brand name is required to mark device as counterfeit
	 */
	public function brandNameIsRequiredtoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '1233232432',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'messages'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * model name is required to mark device as counterfeit
	 */
	public function modelNameIsRequiredtoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '1233232432',
			'brand_name'  => 'lorem',
			'store_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'messages'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 * Store name is required to mark device as counterfeit
	 */
	public function storeNameIsRequiredtoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '1233232432',
			'model_name'  => 'lorem',
			'brand_name'  => 'lorem',
			'description' => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'messages'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}


	/**
	 * @test
	 * Description is required to mark device as counterfeit
	 */
	public function descriptionIsRequiredtoMarkDeviceAsCounterfeit() {


		$user  = factory( User::class )->create( [
			'email' => 'test@3gca.org',
		] );
		$token = \JWTAuth::fromUser( $user );
		$this->post( '/api/counterfiet?token=' . $token, [
			'imei_number' => '1233232432',
			'model_name'  => 'lorem',
			'store_name'  => 'lorem',
			'brand_name'  => 'lorem ipsum',
		] )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'error',
			     'messages'
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 2 );
	}

	/**
	 * @test
	 */
	public function superadmincanDeleteACounterfeitRecordByID() {



		$token       = \JWTAuth::fromUser( $this->superadmin );
		$counterfeit = factory( CounterFiet::class )->create();

		$result = $this->delete( '/api/counterfiet/' . $counterfeit->id . '/?token=' . $token );

		$result->assertStatus( 200 );
		$result->assertJson( [
			'success' => true,
		] );
		$result->assertSuccessful();
		$result->assertJsonCount( 1 );
	}


	/**
	 * @test
	 * A valid token is required deleting a counterfeit
	 */
	public function aTokenIsRequiredforDeleteingACounterfeit() {
		$user        = factory( User::class )->create();
		$counterfeit = factory( CounterFiet::class )->create();
		$this->delete( '/api/counterfiet/' . $counterfeit->id )
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
	 * A valid token is required for deleting a counterfeit
	 */
	public function aValidTokenIsRequiredforDeletingACounterfeit() {

		$user        = factory( User::class )->create();
		$counterfeit = factory( CounterFiet::class )->create();
		$this->delete( '/api/counterfiet/' . $counterfeit->id . '/?token=loremipsumtoken' )
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
	 * User can search counter devices by ID
	 */
	public function canSearchCounterDevicesByID() {

		$user        = factory( User::class )->create();
		$token       = \JWTAuth::fromUser( $user );
		$counterfeit = factory( CounterFiet::class )->create();

		$result  = $this->get( '/api/search_counter/' . $counterfeit->id . '/?token=' . $token );
		$counter = CounterFiet::where( 'id', '=', $counterfeit->id )->first();
		$result->assertStatus( 200 );
		$result->assertJsonStructure( [
			'data'
		] );

		$result->assertSuccessful();
		$result->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * A valid token is required searching a counterfeit
	 */
	public function aTokenIsRequiredforSearchingACounterfeit() {
		$user        = factory( User::class )->create();
		$counterfeit = factory( CounterFiet::class )->create();
		$this->get( '/api/search_counter/' . $counterfeit->id )
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
	 * A valid token is required for searching a counterfeit
	 */
	public function aValidTokenIsRequiredforSearchingACounterfeit() {

		$user        = factory( User::class )->create();
		$counterfeit = factory( CounterFiet::class )->create();
		$this->get( '/api/search_counter/' . $counterfeit->id . '/?token=loremipsumtoken' )
		     ->assertStatus( 401 )
		     ->assertJson( [
			     'errors' => [
				     'root' => 'Token is invalid'
			     ]
		     ] )
		     ->assertJsonCount( 1 );
	}


}
