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
namespace App\Http\Controllers\SuperAdmin;


use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Notifications\PasswordResetRequest;
use App\Notifications\SendPasswordSetMailForNewUser;
use App\PasswordReset;
use App\Role;
use App\User;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller {


	/**
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index() {

		$users = User::with( 'roles', 'permissions', 'licenses' )->paginate( 10 )->toArray();

		return response()->json( [
			'data' => [
				'users' => $users
			]
		] );
	}

	/**
	 * @param CreateUserRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store( CreateUserRequest $request ) {

		$request->validated();

		$role = Role::where( 'slug', $request->role_slug )->first();
		$user = User::create( [
			'first_name' => $request['first_name'],
			'last_name'  => $request['last_name'],
			'email'      => $request['email'],
			'password'   => bcrypt( $request['password'] ),
			'active'     => true,
		] );

		$user->roles()->attach( $role );

		$passwordReset = PasswordReset::updateOrCreate(
			[ 'email' => $user->email ],
			[
				'email' => $user->email,
				'token' => str_random( 60 )
			]
		);
		$user->notify(
			new SendPasswordSetMailForNewUser( $passwordReset->token )
		);

		return response()->json( [
			'data' => [
				'user' => $user,
			]
		], 200 );


	}

	/**
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy( $id ) {

		$user = User::findOrFail( $id );


		if ( $user->roles[0]->slug == 'superadmin' ) {
			return response()->json( [
				'error'   => true,
				'message' => 'Couldnt be deleted'
			], 401 );
		} else {
			$user->delete();

			return response()->json( [
				'success' => true,
			], 200 );
		}


	}

	/**
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show( $id ) {

		$user = User::where( 'id', $id )->with( 'roles' )->first();

		return response()->json( [
			'success' => true,
			'user'    => $user
		] );


	}

	/**
	 * @param $id
	 * @param CreateUserRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update( $id, CreateUserRequest $request ) {


		$request->validated();

		$user = User::where( 'id', $id )->with( 'roles' )->first();
		$role = Role::where( 'slug', $request->role_slug )->first();
		$user->update( [
			'first_name' => $request['first_name'],
			'last_name'  => $request['last_name'],
			'email'      => $request['email'],
		] );

		$user->roles()->sync( $role );

		return response()->json( [
			'success' => true,
			'message' => 'User updated successfully',

		] );

	}

	/**
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getMembersCount() {


		$inactive_users = User::where( 'active', false )->where( 'agreement', 'Not Agreed' )->count();

		return response()->json( [
			'data' => [
				'inactive_count' => $inactive_users
			]
		] );


	}

}