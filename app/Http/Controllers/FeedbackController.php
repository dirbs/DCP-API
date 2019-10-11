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
namespace App\Http\Controllers;

use App\Feedback;
use App\Http\Requests\GiveFeedbackRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Validator;

class FeedbackController extends Controller {


	/**
	 * @param GiveFeedbackRequest $request
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Tymon\JWTAuth\Exceptions\JWTException
	 */
	public function giveFeedback( GiveFeedbackRequest $request, JWTAuth $JWTAuth ) {


		$user = $JWTAuth->parseToken()->authenticate();
		$request->validated();
		$user_name = $user->first_name;

		Feedback::create( [
			'user_name' => $user_name,
			'message'   => $request->message
		] );

		return response()->json( [
			'success' => true,
			'message' => trans( 'responses.feedbacks.success' )
		], 200 );
	}

	/**
	 * @param JWTAuth $JWTAuth
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getFeedbackCount() {


		$feedback_count = Feedback::where( 'is_read', false )->count();

		return response()->json( [
			'data' => [
				'is_read'        => false,
				'feedback_count' => $feedback_count
			]
		] );

	}

	/**
	 * @param $id
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function resetFeedbackCount( $id ) {


		$feedback = Feedback::findOrFail( $id );

		if ( ! $feedback->is_read ) {
			Feedback::where( 'id', $id )->update( [
				'is_read' => true
			] );
		}

		$updated_feedback_count = Feedback::where( 'is_read', false )->count();

		return response()->json( [
			'data' => [
				'is_read'        => false,
				'feedback_count' => $updated_feedback_count
			]
		] );


	}

}
