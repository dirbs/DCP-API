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
use App\IMEI;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;
use Tests\TestCase;

class DashboardUsersActivitiesTest extends TestCase {
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
	 * Super admnin can view all users activities
	 */
	public function superAdminCanViewallUsersActivities() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'data' => [

			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );
	}


	/**
	 * @test
	 * Admin can view all users activities
	 */
	public function AdminCanViewallUsersActivities() {

		$token = \JWTAuth::fromUser( $this->admin );
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertJsonStructure( [
			     'data' => [

			     ]
		     ] )
		     ->assertSuccessful()
		     ->assertJsonCount( 1 );
	}

	/**
	 * @test
	 * Staff can not view all users activities
	 */
	public function StaffCannotViewallUsersActivities() {

		$token = \JWTAuth::fromUser( $this->staff );
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 403 )
		     ->assertJsonStructure( [
			     'error',
			     'message'
		     ] )->assertExactJson( [
				'error'   => true,
				'message' => 'You do not have enough permissions for this request, please contact system administrator for more details'
			] );
	}

	/**
	 * @test
	 * A valid token is required for viewing users activities
	 */
	public function aTokenIsRequiredforUsersActivities() {

		$this->get( '/api/get_users_activity' )
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
	 * A valid token is required for users activities
	 */
	public function aValidTokenIsRequiredforUsersActivites() {

		$this->get( '/api/get_users_activity?token=loremsomtoken' )
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
	public function responseHasMaxofFivePaginatedUsersActities() {

		$token          = \JWTAuth::fromUser( $this->superadmin );
		$imeis          = factory( 'App\IMEI', 5 )->create();
		$users_activity = IMEI::orderBy( 'id', 'desc' )->paginate( 5 );

		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $users_activity )
			->baseResponse->getData()->data;
		$this->assertEquals( count( $users_activity ), 5 );
	}

	/**
	 * @test
	 */
	public function responseshouldNtHaveMorethanFivePaginatedUsersActities() {

		$token          = \JWTAuth::fromUser( $this->superadmin );
		$imeis          = factory( 'App\IMEI', 5 )->create();
		$users_activity = IMEI::orderBy( 'id', 'desc' )->paginate( 5 );

		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $users_activity )
			->baseResponse->getData()->data;
		$this->assertNotEquals( count( $users_activity ), 6 );
	}

	/**
	 * @test
	 */
	public function responeHasValidInvalidCount() {

		$token     = \JWTAuth::fromUser( $this->superadmin );
		$imeis     = factory( 'App\IMEI', 5 )->create();
		$not_found = IMEI::orderBy( 'id', 'desc' )->where( 'result', 'Invalid' )->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $not_found )
			->baseResponse->getData()->data;
		$this->assertEquals( $not_found, 5 );
	}

	/**
	 * @test
	 */
	public function responeShouldNotExceedInvalidCount() {

		$token     = \JWTAuth::fromUser( $this->superadmin );
		$imeis     = factory( 'App\IMEI', 5 )->create();
		$not_found = IMEI::orderBy( 'id', 'desc' )->where( 'result', 'Invalid' )->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $not_found )
			->baseResponse->getData()->data;
		$this->assertNotEquals( $not_found, 6 );
	}

	/**
	 * @test
	 */
	public function responeHasvalidCountofFounfIMEI() {

		$token = \JWTAuth::fromUser( $this->superadmin );
		$imeis = factory( 'App\IMEI', 5 )->create();
		$found = IMEI::orderBy( 'id', 'desc' )->where( 'result', 'Found' )->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $found )
			->baseResponse->getData()->data;
		$this->assertEquals( $found, 0 );
	}

	/**
	 * @test
	 */
	public function responeHasCreatedAtsofIMEI() {

		$token        = \JWTAuth::fromUser( $this->superadmin );
		$imeis        = factory( 'App\IMEI', 5 )->create();
		$created_imei = IMEI::orderBy( 'id', 'desc' )->get( [ 'created_at' ] );
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $created_imei )
			->baseResponse->getData()->data->imei_created_at;
		$this->assertArrayHasKey( 'created_at', $created_imei[0] );
	}

	/**
	 * @test
	 */
	public function responesHasVisitorTrafficByMonths() {

		$token          = \JWTAuth::fromUser( $this->superadmin );
		$imeis          = factory( 'App\IMEI', 5 )->create();
		$visitorTraffic = IMEI::select( 'created_at' )
		                      ->get()
		                      ->groupBy( function ( $date ) {
			                      return Carbon::parse( $date->created_at )->format( 'm' );
		                      } );
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $visitorTraffic )
			->baseResponse->getData()->data->visitor_traffic;
		$now   = Carbon::now();
		$month = $now->month;
		$this->assertArrayHasKey( '0' . $month, $visitorTraffic );
		$this->assertArrayHasKey( 'created_at', $visitorTraffic[ "0" . $month ][0] );
	}

	/**
	 * @test
	 */
	public function responseHasCreatedAtsCounterfeitsByMonths() {

		$token          = \JWTAuth::fromUser( $this->superadmin );
		$counterfeits   = factory( CounterFiet::class, 5 )->create();
		$visitorReports = CounterFiet::createdAtFeedByMonths( $this->superadmin );

		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $visitorReports )
			->baseResponse->getData()->data->visitorReports;
		$now   = Carbon::now();
		$month = $now->month;
		$this->assertArrayHasKey( '0' . $month, $visitorReports );
		$this->assertArrayHasKey( 'created_at', $visitorReports[ "0" . $month ][0] );

	}

	/**
	 * @test
	 */
	public function responesHasVisitorTrafficByyearsinIMEISearches() {

		$token                  = \JWTAuth::fromUser( $this->superadmin );
		$imeis                  = factory( IMEI::class, 5 )->create( [
			'created_at' => '2019-01-09'
		] );
		$visitorTrafficByMonths = IMEI::createdAtFeedByDate( $this->superadmin );
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $visitorTrafficByMonths )
			->baseResponse->getData()->data->byyears;

		$this->assertArrayHasKey( '2019-01-09', $visitorTrafficByMonths );
		$this->assertArrayHasKey( 'created_at', $visitorTrafficByMonths["2019-01-09"][0] );
	}

	/**
	 * @test
	 */
	public function responseHasTotalIMEICount() {


		$token     = \JWTAuth::fromUser( $this->superadmin );
		$imeis     = factory( IMEI::class, 5 )->create();
		$totalIMEI = IMEI::all()->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $totalIMEI )
			->baseResponse->getData()->data;

		$this->assertEquals( $totalIMEI, 5 );
		$this->assertNotEquals( $totalIMEI, 0 );
	}

	/**
	 * @test
	 */
	public function responseHasTotalUsersCount() {


		$token      = \JWTAuth::fromUser( $this->superadmin );
		$users      = factory( User::class, 5 )->create();
		$totalUsers = User::all()->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $totalUsers )
			->baseResponse->getData()->data;

		$this->assertEquals( $totalUsers, 8 );
		$this->assertNotEquals( $totalUsers, 0 );
	}

	/**
	 * @test
	 */
	public function responesHasTotalCounterfeitCount() {


		$token                   = \JWTAuth::fromUser( $this->superadmin );
		$Counterfeit             = factory( CounterFiet::class, 5 )->create();
		$totalCounterfeitDevices = CounterFiet::all()->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $totalCounterfeitDevices )
			->baseResponse->getData()->data;

		$this->assertEquals( $totalCounterfeitDevices, 5 );
		$this->assertNotEquals( $totalCounterfeitDevices, 0 );
	}

	/**
	 * @test
	 */
	public function responseHasDeactivatedUserscount() {

		$token            = \JWTAuth::fromUser( $this->superadmin );
		$users            = factory( User::class, 5 )->create( [
			'active' => false
		] );
		$deactivatedUsers = $this->superadmin->deactivatedUsersCount();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $deactivatedUsers )
			->baseResponse->getData()->data;

		$this->assertEquals( $deactivatedUsers, 5 );
		$this->assertNotEquals( $deactivatedUsers, 0 );

	}

	/**
	 * @test
	 */
	public function responseHasMatchedIMEIscount() {

		$token             = \JWTAuth::fromUser( $this->superadmin );
		$imeis             = factory( IMEI::class, 5 )->create( [
			'results_matched' => 'Yes'
		] );
		$totalMatchedImeis = IMEI::where( 'results_matched', 'Yes' )->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $totalMatchedImeis )
			->baseResponse->getData()->data;

		$this->assertEquals( $totalMatchedImeis, 5 );
		$this->assertNotEquals( $totalMatchedImeis, 0 );

	}

	/**
	 * @test
	 */
	public function responseHasNotMatchedIMEIscount() {

		$token                = \JWTAuth::fromUser( $this->superadmin );
		$imeis                = factory( IMEI::class, 5 )->create( [
			'results_matched' => 'No'
		] );
		$totalNotMatchedImeis = IMEI::where( 'results_matched', 'No' )->count();
		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $totalNotMatchedImeis )
			->baseResponse->getData()->data;

		$this->assertEquals( $totalNotMatchedImeis, 5 );
		$this->assertNotEquals( $totalNotMatchedImeis, 0 );

	}


	/**
	 * @test
	 */
	public function responseHasLatLongIMEIHeatMapSearches() {

		$token                      = \JWTAuth::fromUser( $this->superadmin );
		$imeis                      = factory( IMEI::class, 5 )->create();
		$latLongIMEIHeatMapSearches = IMEI::select( 'latitude', 'longitude' )->get();

		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $latLongIMEIHeatMapSearches )
			->baseResponse->getData()->data;

		$this->assertArrayHasKey( 'latitude', $latLongIMEIHeatMapSearches[0] );
		$this->assertArrayHasKey( 'longitude', $latLongIMEIHeatMapSearches[0] );
	}

	/**
	 * @test
	 */
	public function responseHasLatLongCounterfeitHeatMapSearches() {

		$token                      = \JWTAuth::fromUser( $this->superadmin );
		$imeis                      = factory( CounterFiet::class, 5 )->create();
		$counterfeitHeatMapSearches = CounterFiet::select( 'latitude', 'longitude' )->get();

		$this->get( '/api/get_users_activity?token=' . $token )
		     ->assertStatus( 200 )
		     ->assertSee( $counterfeitHeatMapSearches )
			->baseResponse->getData()->data;

		$this->assertArrayHasKey( 'latitude', $counterfeitHeatMapSearches[0] );
		$this->assertArrayHasKey( 'longitude', $counterfeitHeatMapSearches[0] );
	}


}
