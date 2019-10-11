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
use App\Permission;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$staff_role       = Role::where( 'slug', 'staff' )->first();
		$admin_role       = Role::where( 'slug', 'admin' )->first();
		$super_admin_role = Role::where( 'slug', 'superadmin' )->first();

		$staff_perm       = Permission::where( 'slug', 'create-users' )->first();
		$admin_perm       = Permission::where( 'slug', 'edit-users' )->first();
		$super_admin_perm = Permission::where( 'slug', 'create-admins' )->first();


		$super_admin             = new User();
		$super_admin->first_name = 'dcp super';
		$super_admin->last_name  = 'super-admin';
		$super_admin->email      = 'super@3gca.org';
		$super_admin->password   = bcrypt( 'Admin@1234' );
		$super_admin->active     = true;
		$super_admin->loginCount = 1;
		$super_admin->agreement  = 'Agreed';
		$super_admin->save();
		$super_admin->roles()->attach( $super_admin_role );
		$super_admin->permissions()->attach( $super_admin_perm );

		$admin             = new User();
		$admin->first_name = 'dcp';
		$admin->last_name  = 'admin';
		$admin->email      = 'admin@3gca.org';
		$admin->password   = bcrypt( 'Admin@1234' );
		$admin->active     = true;
		$admin->loginCount = 1;
		$admin->agreement  = 'Agreed';
		$admin->save();
		$admin->roles()->attach( $admin_role );
		$admin->permissions()->attach( $admin_perm );

		$staff             = new User();
		$staff->first_name = 'Usama';
		$staff->last_name  = 'Muneer';
		$staff->email      = 'usama@3gca.org';
		$staff->password   = bcrypt( 'Admin@1234' );
		$staff->active     = true;
		$staff->save();
		$staff->roles()->attach( $staff_role );
		$staff->permissions()->attach( $staff_perm );

//		factory( 'App\User', 100 )->create()->each( function ( $f ) use ( $staff_role ) {
//			$f->roles()->attach( $staff_role );
//
//		} );
	}
}
