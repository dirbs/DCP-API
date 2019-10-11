<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		\Validator::extend( 'strong_password', function ( $attribute, $value, $parameters, $validator ) {
			// Contain at least one uppercase/lowercase letters, one number and one special char
			return preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*(_|[^\w])).+$/', (string) $value );
		}, trans( 'profile.password.strong' ) );

		/**
		 * Paginate a standard Laravel Collection.
		 *
		 * @param int $perPage
		 * @param int $total
		 * @param int $page
		 * @param string $pageName
		 *
		 * @return array
		 */
		Collection::macro( 'paginate', function ( $perPage, $total = null, $page = null, $pageName = 'page' ) {
			$page = $page ?: LengthAwarePaginator::resolveCurrentPage( $pageName );

			return new LengthAwarePaginator(
				$this->forPage( $page, $perPage ),
				$total ?: $this->count(),
				$perPage,
				$page,
				[
					'path'     => LengthAwarePaginator::resolveCurrentPath(),
					'pageName' => $pageName,
				]
			);
		} );
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}
}
