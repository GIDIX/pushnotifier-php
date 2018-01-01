#!/usr/bin/env php
<?php
	require __DIR__ . '/base.php';

	use GIDIX\PushNotifier\SDK\PushNotifier;

	/*
		EXAMPLE: Logging in and sending notifications

		This is a basic example to show how the general flow works
	 */

	/**
	 * Create PushNotifier application
	 *
	 * [
	 *     'api_token'    =>  'sometokenhere',
	 *     'package'      =>  'your.package.name',
	 *     'app_token'    =>  'a user app_token', (optional)
	 * ]
	 *
	 * @var PushNotifier
	 */
	$pushNotifier = new PushNotifier([
		'api_token'			=>	API_TOKEN,
		'package'			=>	APP_PACKAGE,
	]);

	/**
	 * Log in as a user and retrieve its app_token
	 *
	 * @var AppToken
	 */
	$login = $pushNotifier->login('username', 'password', true);

	echo '=== Logged in:' . PHP_EOL;
	echo '    AppToken: ' . $pushNotifier->getAppToken()->getToken() . PHP_EOL;
	echo '    Expires: ' . $pushNotifier->getAppToken()->getExpiry()->format('d.m.Y, H:i') . PHP_EOL;

	/**
	 * Get the user's devices
	 *
	 * @var Device[]
	 */
	$devices = $pushNotifier->getDevices();

	echo PHP_EOL;
	echo '=== Devices:' . PHP_EOL;
	
	foreach ($devices as $device) {
		echo '--- ' . $device->getTitle() . ' (' . $device->getID() . ')' . PHP_EOL;
		echo '    Model: ' . $device->getModel() . PHP_EOL;
		echo '    Image: ' . $device->getImage() . PHP_EOL;
		echo PHP_EOL;
	}

	/**
	 * Push a notification with content and a URL to two devices
	 *
	 * [
	 *     "success": [...],
	 *     "error": [...]
	 * ]
	 * 
	 * @var array
	 */
	$result = $pushNotifier->sendNotification([ $devices[0], $devices[2] ], 'Example Notification', 'https://pushnotifier.de');

	print_r($result);
	echo PHP_EOL;