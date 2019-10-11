<?php

return [


	"driver"   => "<MAIL_DRIVER>",
	"host"     => "<MAIL_HOST>",
	"port"     => '<MAIL_PORT>',
	"from"     => array(
		"address" => "<FROM_ADDRESS>",
		"name"    => "<FROM_NAME>"
	),
	"username" => "<USER_NAME>",
	"password" => "<PASSWORD>",
	"sendmail" => "/usr/sbin/sendmail -bs",
	"pretend"  => false,
	'markdown' => [
		'theme' => 'default',

		'paths' => [
			resource_path( 'views/vendor/mail' ),
		],
	],

];
