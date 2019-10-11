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
header( 'Access-Control-Allow-Origin:  *' );
header( 'Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS' );
header( 'Access-Control-Max-Age: 1000' );
header( 'Access-Control-Allow-Headers: X-CSRF-Token, Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With, x-localization' );

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get( 'db-refresh', 'DBRefresh@refresh' );

Route::group( [ 'middleware' => 'localization' ], function () {


//IMEI Search
	Route::group( [ 'middleware' => 'throttle' ], function () {
		Route::post( '/lookup/{user_device}/{checking_method}', 'IMEI\ImController@lookup' );
	} );
	/*****************************************************************
	 * ********************* Auth Routes ****************************
	 ****************************************************************/

	Route::post( 'register', 'Auth\RegisterController@register' );
	Route::post( 'login', 'Auth\LoginController@login' );
	Route::post( 'logout', 'Auth\RegisterController@logout' );
	Route::post( 'recover', 'Auth\ResetPasswordController@recover' );
	Route::get( 'find/{token}', 'Auth\ResetPasswordController@find' );
	Route::post( 'reset', 'Auth\ResetPasswordController@reset' );

	/** Auth Routes */

	/*****************************************************************
	 ********************** Super Admin Routes **********************
	 ****************************************************************/

	Route::group( [ 'middleware' => 'superadmin' ], function () {

		//Activity Logs Export
		Route::get( 'activity-logs-export', 'SuperAdmin\ActivityLogsExportController@index' );

		//Counterfeit Logs Export
		Route::get( 'counterfeit-logs-export', 'SuperAdmin\CounterfeitLogsController@index' );

		//Activate/Deactivate Staff Users
		Route::put( 'activate-staff/{id}', 'SuperAdmin\SuperAdminController@activateStaff' );
		Route::put( 'deactivate-staff/{id}', 'SuperAdmin\SuperAdminController@deactivateStaff' );

//License Agreements for Staff/ Admin
		Route::get( 'license-agreements', 'SuperAdmin\LicenseAgreementController@index' );
		Route::post( 'license-agreement', 'SuperAdmin\LicenseAgreementController@store' );
		Route::get( 'license-agreement/{id}', 'SuperAdmin\LicenseAgreementController@show' );

		//User Routes for Super Admin
		Route::get( 'get-users', 'SuperAdmin\UserController@index' );
		Route::post( 'create-user', 'SuperAdmin\UserController@store' );
		Route::delete( 'delete-user/{id}', 'SuperAdmin\UserController@destroy' );
		Route::put( 'update-user/{id}', 'SuperAdmin\UserController@update' );
		Route::get( 'get-user/{id}', 'SuperAdmin\UserController@show' );

		Route::delete( '/counterfiet/{id}', 'IMEI\CounterfeitController@destroy' );
		Route::get( '/auth/activate', 'Auth\ActivationController@activate' )->name( 'auth.activate' );

	} );

	Route::get( '/get-user-info/{id}', 'Auth\LicenseController@getUserCurrentInfo' );

	Route::get( '/get-user-license/{id}', 'Auth\LicenseController@getRecentLicense' );

	Route::put( '/update-user-license/{id}', 'Auth\LicenseController@updateLicensedUser' );

	Route::put( '/update-user-app-license/{id}', 'Auth\LicenseController@updateAppLicenseUser' );


	Route::delete( '/activity/{id}', 'IMEI\ImController@destroy' );


	Route::post( '/bulk-lookup', 'IMEI\BulkLookupController@bulkLookup' );
	Route::get( '/view-counterfiet-file/{name}', 'IMEI\CounterfeitController@viewUploadedFile' );


	Route::get( '/search_counter/{id}', 'IMEI\CounterfeitController@searchCounterImages' );
	Route::get( '/users_activity', 'IMEI\UsersActivitiesController@users_activity' );
	Route::post( '/search_users_activity', 'IMEI\UsersActivitiesController@search_users_activity' );

	/** Feedback Routes */


	/** Feedback Routes */


	/*****************************************************************
	 **************** Super Admin & Admin Routes *********************
	 ****************************************************************/
	Route::group( [ 'middleware' => 'role:superadmin' ], function () {

		Route::group( [ 'middleware' => 'role:admin' ], function () {
			//All Users Activities
			Route::get( 'datatable/users-activity', 'DataTable\UserActivityController@getRecords' );

			//All Not Matched Records
			Route::get( 'datatable/not-matched-records', 'DataTable\NotMatchedRecordsController@getRecords' );

			//All Matched Records
			Route::get( 'datatable/matched-records', 'DataTable\MatchedRecordsController@getRecords' );

			//All Counterfeit Devices
			Route::get( 'datatable/counterfiet', 'DataTable\CounterFietController@getRecords' );

			//All System Feedbacks
			Route::get( 'datatable/feedback', 'DataTable\FeedbackController@getRecords' );

			//All User Licenses
			Route::get( 'datatable/licenses', 'DataTable\LicensesController@getRecords' );


			//Deactive Users Count
			Route::get( 'user-count-notify', 'SuperAdmin\UserController@getMembersCount' );
			//Feedback Counts
			Route::get( 'feedback-count-notify', 'FeedbackController@getFeedbackCount' );

			//Reset Feedback Notification
			Route::put( 'feedback-count-reset/{id}', 'FeedbackController@resetFeedbackCount' );


			Route::get( 'datatable/users', 'DataTable\UserController@getRecords' );

			Route::get( '/user-licenses/{id}', 'Auth\LicenseController@getUserLicenses' );

			Route::get( '/get_users_activity', 'IMEI\UsersActivitiesController@get_users_activity' );
		} );
	} );

	/*****************************************************************
	 ********* Common Routes e.g. Staff/Admin/SuperAdmin ************
	 ********* Common Routes e.g. Staff/Admin/SuperAdmin ************
	 ****************************************************************/

	Route::get( 'datatable/my-activity', 'DataTable\UserActivityController@myActivity' );

	Route::post( '/counterfiet', 'IMEI\CounterfeitController@counterFietDevices' );

	Route::get( 'profile', 'Profile\ProfileController@showProfile' );
	Route::get( 'profile/{id}/edit', 'Profile\ProfileController@getProfile' );
	Route::put( 'profile/{id}/edit', 'Profile\ProfileController@editProfile' );

	Route::get( 'profile/{id}/password', 'Profile\ProfileController@getPassword' );
	Route::put( 'profile/{id}/password', 'Profile\ProfileController@editPassword' );


//results_matched routes
	Route::put( 'results-matched/{imei}', 'IMEI\ImController@resultsMatch' );
	Route::put( 'results-not-matched/{imei}', 'IMEI\ImController@resultsNotMatch' );

	/*****************************************************************
	 *********************** Staff Routes ****************************
	 ****************************************************************/
	Route::group( [ 'middleware' => 'staff' ], function () {
		//Feedback Route
		Route::post( 'feedback', 'FeedbackController@giveFeedback' );
	} );

} );
