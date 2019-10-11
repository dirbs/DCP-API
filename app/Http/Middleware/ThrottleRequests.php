<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\JsonResponse;

class ThrottleRequests extends \Illuminate\Routing\Middleware\ThrottleRequests {

	/**
	 * @param \Illuminate\Http\Request $request
	 * @param Closure $next
	 * @param int $maxAttempts
	 * @param int $decayMinutes
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function handle( $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1 ) {
		$key = $this->resolveRequestSignature( $request );

		if ( $this->limiter->tooManyAttempts( $key, $maxAttempts, $decayMinutes ) ) {

			return $this->buildJsonResponse( $key, $maxAttempts );

		}

		$this->limiter->hit( $key, $decayMinutes );

		$response = $next( $request );

		return $this->addHeaders(
			$response, $maxAttempts,
			$this->calculateRemainingAttempts( $key, $maxAttempts )
		);
	}

	/**
	 * Create a 'too many attempts' JSON response.
	 *
	 * @param  string $key
	 * @param  int $maxAttempts
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function buildJsonResponse( $key, $maxAttempts ) {
		$response = new JsonResponse( [
			'error' => [
				'code'    => 429,
				'message' => 'Too Many Attempts, Please try again after later',
			],
		], 429 );

		$retryAfter = $this->limiter->availableIn( $key );

		return $this->addHeaders(
			$response, $maxAttempts,
			$this->calculateRemainingAttempts( $key, $maxAttempts, $retryAfter ),
			$retryAfter
		);
	}
}
