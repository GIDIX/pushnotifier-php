#!/usr/bin/env php
<?php
	require __DIR__ . '/base.php';

	/*
		EXAMPLE: Storing App Token
		
		This is an example to show how to save the AppToken somewhere
		to use it again later.
	 */

	use GIDIX\PushNotifier\SDK\PushNotifier;

	/**
	 * Create PushNotifier application without AppToken
	 *
	 * @var PushNotifier
	 */
	$pushNotifier = new PushNotifier([
		'api_token'			=>	API_TOKEN,
		'package'			=>	APP_PACKAGE
	]);

	$appToken = $pushNotifier->login('username', 'password', true);

	// Save the AppToken somewhere, i.e. a file
	file_put_contents('appToken.txt', (string) $appToken);

	/**
	 * Create PushNotifier application with the stored AppToken
	 *
	 * @var PushNotifier
	 */
	$pushNotifier = new PushNotifier([
		'api_token'			=>	API_TOKEN,
		'package'			=>	APP_PACKAGE,
		'app_token'			=>	file_get_contents('appToken.txt')
	]);

	print_r($pushNotifier->getDevices());