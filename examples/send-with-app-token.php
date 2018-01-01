#!/usr/bin/env php
<?php
	require __DIR__ . '/base.php';

	use GIDIX\PushNotifier\SDK\PushNotifier;

	/*
		EXAMPLE: Sending with an AppToken only

		This is an example to show how to log in and send notifications
		after having retrieved the AppToken at some point.
	 */

	/**
	 * Create PushNotifier application
	 *
	 * [
	 *     'api_token'           =>  'sometokenhere',
	 *     'package'             =>  'your.package.name',
	 *     'app_token'           =>  'a user app_token', (optional)
	 *     'app_token_expiry'    =>  DateTime|int (optional)
	 * ]
	 *
	 * @var PushNotifier
	 */
	$pushNotifier = new PushNotifier([
		'api_token'			=>	API_TOKEN,
		'package'			=>	APP_PACKAGE,
		'app_token'			=>	'someAppToken',
		'app_token_expiry'	=>	new \DateTime('27.12.2018, 22:50') // can be a timestamp, too
	]);

	echo '=== Logged in:' . PHP_EOL;
	echo '    AppToken: ' . $pushNotifier->getAppToken()->getToken() . PHP_EOL;
	echo '    Expires: ' . $pushNotifier->getAppToken()->getExpiry()->format('d.m.Y, H:i') . PHP_EOL;

	/**
	 * Push a notification with content and a URL to one device
	 *
	 * [
	 *     "success": [...],
	 *     "error": [...]
	 * ]
	 * 
	 * @var array
	 */
	$result = $pushNotifier->sendNotification([ 'z0q' ], 'Example Notification', 'https://pushnotifier.de');

	print_r($result);
	echo PHP_EOL;