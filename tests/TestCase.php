<?php

namespace Tests;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\app\BasicTestSuiteSetup;

abstract class TestCase extends BaseTestCase {
	use CreatesApplication;
	use MockeryPHPUnitIntegration;
	use RefreshDatabase;
	use BasicTestSuiteSetup;

	// Use this version if you're on PHP 7
	protected function disableExceptionHandling() {
		$this->app->instance( ExceptionHandler::class, new class extends Handler {
			public function __construct() {
			}

			public function report( \Exception $e ) {
				// no-op
			}

			public function render( $request, \Exception $e ) {
				throw $e;
			}
		} );
	}

}
