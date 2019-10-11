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
namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class SetupApiCommandTest extends TestCase {
	use MockeryPHPUnitIntegration;
	use RefreshDatabase;

	/**
	 */
	public function setupApiCommandWithNoEnvAndDBConfigurations() {

		$this->artisan( 'setup:api', [
			'--wco' => 'wco'
		] )
		     ->expectsQuestion( 'Do you wish to create sample .env file?', 'no' )
		     ->expectsQuestion( 'Would you like to set the DB configurations?', 'yes' )
		     ->expectsQuestion( 'Please enter your DB_CONNECTION', 'pgsql' )
		     ->expectsQuestion( 'Do you wish to continue with the selected DB_CONNECTION?', 'yes' )
		     ->expectsQuestion( 'Please enter your DB_PORT', '5432' )
		     ->expectsQuestion( 'Do you wish to continue with the selected DB_PORT?', 'yes' )
		     ->expectsQuestion( 'Please enter your DB_DATABASE', 'dvs' )
		     ->expectsQuestion( 'Do you wish to continue with the selected DB_DATABASE?', 'yes' )
		     ->expectsQuestion( 'Please enter your DB_USERNAME', 'root' )
		     ->expectsQuestion( 'Do you wish to continue with the selected DB_USERNAME?', 'yes' )
		     ->expectsQuestion( 'Please enter your DB_PASSWORD', 'root' )
		     ->expectsQuestion( 'Do you wish to continue with the selected DB_PASSWORD?', 'yes' )
		     ->expectsQuestion( 'Please enter your WCO_PORT_NAME', 'London' )
		     ->expectsQuestion( 'Do you wish to continue with the selected WCO_PORT_NAME?', 'yes' )
		     ->expectsQuestion( 'Please enter your WCO_PORT_TYPE', 'SEA' )
		     ->expectsQuestion( 'Do you wish to continue with the selected WCO_PORT_TYPE?', 'yes' )
		     ->expectsQuestion( 'Please enter your WCO_COUNTRY', 'United Kingdom' )
		     ->expectsQuestion( 'Do you wish to continue with the selected WCO_COUNTRY?', 'yes' )
		     ->expectsOutput( 'Application cache cleared successfully' )
		     ->expectsOutput( 'Application configurations cleared successfully' )
		     ->expectsQuestion( 'This will invalidate all existing tokens. Are you sure you want to override the secret key?', 'yes' )
		     ->expectsOutput( 'Application Key generated successfully' )
		     ->expectsOutput( 'JWT Secret key generated successfully' )
		     ->execute();
	}
}
