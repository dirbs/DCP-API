<?php

namespace App\Providers;

use App\Events\Auth\UserRequestedActivationEmail;
use App\Events\StaffActivatedEmail;
use App\Listeners\Auth\SendActivationEmail;
use App\Listeners\SendStaffActivatedEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {
	/**
	 * The event listener mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		UserRequestedActivationEmail::class => [
			SendActivationEmail::class,
		],
		StaffActivatedEmail::class          => [
			SendStaffActivatedEmail::class
		]
	];

	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function boot() {
		parent::boot();

		//
	}
}
