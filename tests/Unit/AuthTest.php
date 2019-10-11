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

use App\Events\Auth\UserRequestedActivationEmail;
use App\Notifications\PasswordResetRequest;
use App\PasswordReset;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;
use Tests\TestCase;

class AuthTest extends TestCase {

	use MockeryPHPUnitIntegration;
	use RefreshDatabase;
	use BasicTestSuiteSetup;

	/**
	 * @test
	 * A User with Valid Email Address can login
	 */
	public function userwithValidEmailCanLogin() {
		$user     = factory( User::class )->create( [ 'email' => 'test@3gca.org' ] );
		$response = $this->post( '/api/login', [
			'email'    => $user->email,
			'password' => 'secret'
		] );
		$response->assertStatus( 200 );
		$response->json( 'data' );
		$this->assertAuthenticatedAs( $user );
	}

	/**
	 * @test
	 * * A User with InValid Email Address cannot login
	 */
	public function userwithInValidEmailCannotLogin() {

		$response = $this->post( '/api/login', [
			'email'    => 'test@gmail.com',
			'password' => 'secret'
		] );

		$response->assertStatus( 401 );
		$response->assertJsonStructure( [
			'errors',
			'message'
		] );
		$response->assertJson( [
			"errors"  => true,
			"message" => "These credentials do not match our records."
		] );
		$this->assertGuest();
	}

	/**
	 * A valid user can be registered.
	 *
	 * @return void
	 */
	public function testRegistersAValidUser() {
		$user     = factory( User::class )->make( [ 'email' => 'test@3gca.org' ] );
		$response = $this->post( '/api/register', [
			'first_name'            => $user->first_name,
			'last_name'             => $user->last_name,
			'email'                 => $user->email,
			'password'              => 'secret',
			'password_confirmation' => 'secret',
			'active'                => false,
			'activation_token'      => str_random( 100 ),
		] );
		$response->assertStatus( 302 );
		$this->assertGuest();
	}

	/**
	 * @test
	 */
	public function StaffcanRegister() {
		Mail::fake();
		$response = $this->post( '/api/register', [
			'first_name'            => 'test',
			'last_name'             => 'name',
			'email'                 => 'test@3gca.org',
			'password'              => 'Admin@1234',
			'password_confirmation' => 'Admin@1234'
		] );
		$user     = User::get()->last();
		\Event::fire( new UserRequestedActivationEmail( $user ) );
		$response->assertStatus( 200 );
		$response->assertJsonStructure( [
			'data',
			'meta',
			'registered'
		] );

	}


	/**
	 * An invalid user is not registered.
	 *
	 * @return void
	 */
	public function testDoesNotRegisterAnInvalidUser() {
		$user     = factory( User::class )->make();
		$response = $this->post( '/api/register', [
			'first_name'            => $user->first_name,
			'last_name'             => $user->last_name,
			'email'                 => $user->email,
			'password'              => 'secret',
			'password_confirmation' => 'invalid'
		] );
		$response->assertSessionHasErrors();
		$this->assertGuest();
	}


	/**
	 * @test
	 */
	public function registersAUser() {
		$staff_role = Role::where( 'slug', 'staff' )->first();
		$user       = factory( User::class )->create( [
			'first_name'       => 'staff',
			'last_name'        => 'test',
			'email'            => 'teststaff@3gca.org',
			'active'           => false,
			'activation_token' => str_random( 100 )
		] );
		$response   = $this->post( '/api/register', [
			'first_name'            => $user->first_name,
			'last_name'             => $user->last_name,
			'email'                 => $user->email,
			'password'              => 'secret',
			'password_confirmation' => 'secret'
		] );
		$user->roles()->attach( $staff_role );

		$token = \JWTAuth::fromUser( $user );
		\Session::put( 'signup-token', $token );
		$response->assertSessionHas( 'signup-token' );

		$this->assertGuest();

	}

	/**
	 * Sends the password reset email when the user exists.
	 *
	 * @return void
	 */
	public function testSendsPasswordResetEmail() {
		$user = factory( User::class )->create( [ 'email' => 'test@3gca.org' ] );
		
		$this->expectsNotification( $user, PasswordResetRequest::class );
		$response = $this->post( '/api/recover', [ 'email' => $user->email ] );
		$response->assertStatus( 200 );
		$response->assertJsonStructure( [
			'error',
			'message'
		] );
	}

	/**
	 * Does not send a password reset email when the user does not exist.
	 *
	 * @return void
	 */
	public function testDoesNotSendPasswordResetEmail() {
		$this->doesntExpectJobs( PasswordResetRequest::class );
		$this->post( '/api/recover', [ 'email' => 'invalid@email.com' ] );
	}

	/**
	 * Allows a user to reset their password.
	 *
	 * @return void
	 */
	public function testChangesAUsersPassword() {
		$user          = factory( User::class )->create( [
			'email' => 'test@3gca.org'
		] );
		$passwordReset = PasswordReset::updateOrCreate(
			[ 'email' => $user->email ],
			[
				'email' => $user->email,
				'token' => str_random( 60 )
			]
		);

		$passwordReset->where( [
			[ 'token', Password::createToken( $user ) ],
			[ 'email', $user->email ]
		] )->first();

		$response = $this->post( '/api/reset', [
			'token'                 => $passwordReset['token'],
			'email'                 => $user->email,
			'password'              => 'Admin@1234',
			'password_confirmation' => 'Admin@1234'
		] );
		$passwordReset->delete();
//		$this->expectsNotification( $user, PasswordResetSuccess::class );
		$this->assertFalse( Hash::check( 'password', $user->fresh()->password ) );
		$response->json( 'data' );

	}
}
