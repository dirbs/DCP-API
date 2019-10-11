<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define( App\User::class, function ( Faker $faker ) {
	static $password;

	return [
		'first_name'     => $faker->name,
		'last_name'      => $faker->name,
		'email'          => $faker->unique()->safeEmail,
		'password'       => $password ?: $password = bcrypt( 'secret' ),
		'remember_token' => str_random( 10 ),
		'active'         => true,
		'is_verified'    => true,
		'agreement'      => 'Agreed'
	];
} );

$factory->define( App\Role::class, function ( Faker $faker ) {
	$name = $faker->word;

	return [
		'slug' => $name,
		'name' => $name
	];
} );

$factory->define( App\IMEI::class, function ( Faker $faker ) {

	return [
		'user_device'     => $faker->domainWord,
		'checking_method' => 'scan',
		'imei_number'     => $faker->creditCardNumber(),
		'result'          => 'Invalid',
		'user_id'         => 1,
		'results_matched' => 'No',
		'user_name'       => 'dcp',
		'visitor_ip'      => $faker->ipv4,
		'latitude'        => $faker->latitude,
		'longitude'       => $faker->longitude,
		'country'         => $faker->country,
		'city'            => $faker->city,
		'state'           => $faker->stateAbbr,
		'state_name'      => $faker->state
	];
} );

$factory->define( App\CounterFiet::class, function ( Faker $faker ) {

//	$faker->addProvider( new \Faker\Provider\vi_VN\Address( $faker ) );
	return [
		'imei_number' => $faker->creditCardNumber(),
		'result'      => 'Matched',
		'user_id'     => 1,
		'user_name'   => 'dcp',
		'brand_name'  => $faker->company,
		'model_name'  => $faker->company,
		'description' => $faker->word,
		'address'     => $faker->address,
		'store_name'  => $faker->company,
		'status'      => 'Found',

		'latitude'   => $faker->latitude,
		'longitude'  => $faker->longitude,
		'country'    => $faker->country,
		'city'       => $faker->city,
		'state'      => $faker->stateAbbr,
		'state_name' => $faker->state


	];
} );

$factory->define( App\LicenseAgreement::class, function ( Faker $faker ) {
	return [
		'content'   => $faker->paragraph,
		'version'   => $faker->randomFloat(),
		'user_id'   => App\User::inRandomOrder()->first()->id,
		'user_name' => App\User::inRandomOrder()->first()->first_name,
	];
} );


$factory->define( App\Feedback::class, function ( Faker $faker ) {
	return [
		'user_name' => App\User::inRandomOrder()->first()->first_name,
		'message'   => $faker->paragraph,
		'is_read'   => false,
	];
} );
