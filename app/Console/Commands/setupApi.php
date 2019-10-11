<?php
/**
 * Copyright (c) 2018-2019 Qualcomm Technologies, Inc.
All rights reserved.
Redistribution and use in source and binary forms, with or without modification, are permitted (subject to the limitations in the disclaimer below) provided that the following conditions are met:
Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
Neither the name of Qualcomm Technologies, Inc. nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
The origin of this software must not be misrepresented; you must not claim that you wrote the original software. If you use this software in a product, an acknowledgment is required by displaying the trademark/log as per the details provided here: https://www.qualcomm.com/documents/dirbs-logo-and-brand-guidelines
Altered source versions must be plainly marked as such, and must not be misrepresented as being the original software.
This notice may not be removed or altered from any source distribution.
NO EXPRESS OR IMPLIED LICENSES TO ANY PARTY'S PATENT RIGHTS ARE GRANTED BY THIS LICENSE. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class setupApi extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'setup:api {--wco}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup basics for application';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {


		$this->info( 'Hey, Welcome to DCP API!' );
		$this->line( '--------------------------------------------------------------------------------------------------------------' );
		$this->line( '--------------------------------------------------------------------------------------------------------------' );


		if ( $this->confirm( 'Do you wish to create sample .env file?' ) ) {
			$this->comment( 'You can update configurations in your .env later.' );
			$this->line( '--------------------------------------------------------------------------------------------------------------' );
			if ( file_exists( '.env.example' ) ) {
				shell_exec( 'mv .env.example .env' );
			}
		}


		$db_setup = $this->confirm( 'Would you like to set the DB configurations?' );
		if ( $db_setup ) {
			$db_connection = $this->ask( 'Please enter your DB_CONNECTION' );
			if ( $this->confirm( 'Do you wish to continue with the selected DB_CONNECTION?' ) ) {
				static::envUpdate( 'DB_CONNECTION', $db_connection );
			}
			$this->line( '--------------------------------------------------------------------------------------------------------------' );

			$db_port = $this->ask( 'Please enter your DB_PORT' );
			if ( $this->confirm( 'Do you wish to continue with the selected DB_PORT?' ) ) {
				static::envUpdate( 'DB_PORT', $db_port );
			}
			$this->line( '--------------------------------------------------------------------------------------------------------------' );


			$db_database = $this->ask( 'Please enter your DB_DATABASE' );
			if ( $this->confirm( 'Do you wish to continue with the selected DB_DATABASE?' ) ) {
				static::envUpdate( 'DB_DATABASE', $db_database );
			}
			$this->line( '--------------------------------------------------------------------------------------------------------------' );

			$db_username = $this->ask( 'Please enter your DB_USERNAME' );
			if ( $this->confirm( 'Do you wish to continue with the selected DB_USERNAME?' ) ) {
				static::envUpdate( 'DB_USERNAME', $db_username );
			}
			$this->line( '--------------------------------------------------------------------------------------------------------------' );

			$db_password = $this->ask( 'Please enter your DB_PASSWORD' );
			if ( $this->confirm( 'Do you wish to continue with the selected DB_PASSWORD?' ) ) {
				static::envUpdate( 'DB_PASSWORD', $db_password );
			}
			$this->line( '--------------------------------------------------------------------------------------------------------------' );

		}


		$wco = $this->option( 'wco' );

		if ( $wco ) {
			$this->comment( 'Initializing WCO Configuration' );
			$this->line( '--------------------------------------------------------------------------------------------------------------' );
			$wco_port_name = $this->ask( 'Please enter your WCO_PORT_NAME' );
			if ( $this->confirm( 'Do you wish to continue with the selected WCO_PORT_NAME?' ) ) {
				static::envUpdate( 'WCO_PORT_NAME', $wco_port_name );
			}

			$wco_port_type = $this->ask( 'Please enter your WCO_PORT_TYPE' );
			if ( $this->confirm( 'Do you wish to continue with the selected WCO_PORT_TYPE?' ) ) {
				static::envUpdate( 'WCO_PORT_TYPE', $wco_port_type );
			}

			$wco_country = $this->ask( 'Please enter your WCO_COUNTRY' );
			if ( $this->confirm( 'Do you wish to continue with the selected WCO_COUNTRY?' ) ) {
				static::envUpdate( 'WCO_COUNTRY', $wco_country );
			}
			$this->line( '--------------------------------------------------------------------------------------------------------------' );
		}

//		$this->comment( 'Composer Auto-loading files are being re-generated' );
//		shell_exec( 'composer du' );
//		$this->comment( 'AutoLoading files Re-generated successfully' );
//		$this->line( '--------------------------------------------------------------------------------------------------------------' );

		$this->call( 'cache:clear' );
		$this->comment( 'Application cache cleared successfully' );
		$this->line( '--------------------------------------------------------------------------------------------------------------' );

		$this->call( 'config:clear' );
		$this->comment( 'Application configurations cleared successfully' );
		$this->line( '--------------------------------------------------------------------------------------------------------------' );

		$this->call( 'key:generate' );
		$this->comment( 'Application Key generated successfully' );
		$this->line( '--------------------------------------------------------------------------------------------------------------' );

		$this->call( 'jwt:secret' );
		$this->comment( 'JWT Secret key generated successfully' );
		$this->line( '--------------------------------------------------------------------------------------------------------------' );


	}

	/**
	 * Update Laravel Env file Key's Value
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected static function envUpdate( $key, $value ) {
		$path = base_path( '.env' );

		if ( file_exists( $path ) ) {

			file_put_contents( $path, str_replace(
				$key . '=' . env( $key ), $key . '=' . $value, file_get_contents( $path )
			) );
		}


	}
}
