<?php
	/**
	 * API Token
	 * Get it from https://pushnotifier.de/account/api
	 */
	const API_TOKEN = 'YOUR_API_TOKEN_HERE';

	/**
	 * Your application's package name
	 * Create one at https://pushnotifier.de/account/api
	 */
	const APP_PACKAGE = 'YOUR.PACKAGE.HERE';

	require __DIR__ . '/../vendor/autoload.php';

	set_exception_handler(function($t) {
		echo PHP_EOL;
		echo "\033[1;31m";
		echo '[EXCEPTION]: ' . get_class($t) . ' (' . $t->getCode() . ')' . PHP_EOL;

		echo "\033[0;31m";
		echo $t->getMessage() . PHP_EOL;

		echo "\033[0m";
		echo "\033[2m";
		echo 'in ' . $t->getFile() . ' on line ' . $t->getLine() . PHP_EOL;

		echo "\033[0m";
		echo PHP_EOL;
	});