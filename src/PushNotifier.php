<?php
	namespace GIDIX\PushNotifier\SDK;

	use Unirest\Request\Body;
	use Unirest\Request;

	use GIDIX\PushNotifier\SDK\Exceptions\{
		PushNotifierException,
		InvalidAPITokenException,
		InvalidAppTokenException,
		DeviceNotFoundException,
		InvalidRequestException,
		InvalidCredentialsException
	};

	/**
	 * PushNotifier SDK Service
	 * A bridge for using PushNotifier
	 *
	 * @author  bluefirex
	 * @version 1.0 <January 2018>
	 */
	class PushNotifier {
		const BASE_URL = 'https://api.pushnotifier.de/v2';

		/**
		 * Authorization Header Value
		 *
		 * @var string
		 */
		protected $authorization;

		/**
		 * Currently used AppToken
		 *
		 * @var AppToken
		 */
		protected $appToken;

		/**
		 * Initialize the PushNotifier instance
		 * Takes a config array with these options:
		 *
		 * [
		 *     'api_token'         =>  'someAPIToken',       // required, can be obtained at https://pushnotifier.de/account/api
		 *     'package'           =>  'some.package.name',  // required, can be created and obtained at https://pushnotifier.de/account/api
		 *     'app_token'         =>  string|AppToken,      // optional
		 *     'app_token_expiry'  =>  DateTime|int          // optional, timestamp or date when AppToken expires
		 * ]
		 *
		 * @param array $config Config, see description
		 */
		public function __construct(array $config) {
			if (!isset($config['api_token'])) {
				throw new InvalidAPITokenException('apiToken missing.', 400);
			}

			if (!isset($config['package'])) {
				throw new InvalidAPITokenException('package missing.', 400);
			}

			if (isset($config['app_token'])) {
				if ($config['app_token'] instanceof AppToken) {
					// Is AppToken already?
					$this->appToken = $config['app_token'];
				} else if (strpos($config['app_token'], ':') !== false) {
					// Has been created by AppToken::__toString?
					$this->appToken = AppToken::fromString($config['app_token']);
				} else {
					// Is set manually?
					if (isset($config['app_token_expiry'])) {
						$expiry = $config['app_token_expiry'] instanceof \DateTime ? $config['app_token_expiry'] : (new \DateTime())->setTimestamp($config['app_token_expiry']);
					} else {
						$expiry = null;
					}

					$this->appToken = new AppToken($config['app_token'], $expiry);
				}
			}

			$this->authorization = base64_encode($config['package'] . ':' . $config['api_token']);
		}

		/**
		 * Set the AppToken that should be used
		 *
		 * @param string        $appToken AppToken
		 * @param DateTime|null $expiry   Expiration Date of the AppToken, set null to disable auto-renewal
		 *
		 * @return PushNotifier
		 */
		public function setAppToken(string $appToken, DateTime $expiry = null) {
			$this->appToken = new AppToken($appToken, $expiry);

			return $this;
		}

		/**
		 * Get the current AppToken
		 * Renews itself automatically in case it is about to expire
		 *
		 * @return string
		 */
		public function getAppToken() {
			if ($this->appToken->isAboutToExpire()) {
				$this->refreshToken(true);
			}

			return $this->appToken;
		}

		/**
		 * Log in as a user
		 *
		 * @param string       $username Username
		 * @param string       $password Password
		 * @param bool         $apply    Automatically apply the AppToken to this instance
		 *
		 * @return AppToken               
		 *
		 * @throws InvalidCredentialsException If the users couldn't be authenticated
		 * @throws PushNotifierException If an unknown error happened
		 */
		public function login(string $username, string $password, bool $apply = true): AppToken {
			$response = $this->call('POST', '/user/login', [], [
				'username'		=>	$username,
				'password'		=>	$password
			]);

			if ($response->code == 404) {
				throw new InvalidCredentialsException('Invalid credentials for ' . var_export($username, true));
			}

			$appToken = new AppToken($response->body->app_token, (new \DateTime())->setTimestamp($response->body->expires_at));

			if ($apply) {
				$this->appToken = $appToken;
			}

			return $appToken;
		}

		/**
		 * Refresh the current AppToken
		 * This can be done any time but shouldn't be done until 48 hours before it expires
		 *
		 * @param bool     $apply Automatically apply the AppToken to this instance
		 *
		 * @return AppToken
		 *
		 * @throws InvalidAppTokenException If the token had expired already
		 * @throws PushNotifierException If an unknown error happened
		 */
		public function refreshToken(bool $apply = true): AppToken {
			$response = $this->call('GET', '/user/refresh', [
				'X-AppToken'		=>	$this->appToken->getToken()
			], []);

			$appToken = new AppToken($response->body->app_token, (new \DateTime())->setTimestamp($response->body->expires_at));

			if ($apply) {
				$this->appToken = $appToken;
			}

			return $appToken;
		}

		/**
		 * Get all devices the current user has
		 *
		 * @return Device[]
		 *
		 * @throws InvalidAppTokenException If the AppToken has expired or is invalid
		 * @throws PushNotifierException If an unknown error happened
		 */
		public function getDevices(): array {
			$response = $this->call('GET', '/devices', [
				'X-AppToken'		=>	$this->getAppToken()->getToken()
			], []);

			return array_map(function($device) {
				return new Device($device->id, $device->title, $device->model, $device->image);
			}, (array) $response->body);
		}

		/**
		 * Send a text-only message
		 *
		 * @param array        $devices Devices to send to, either Device[] or string[] (IDs of the devices)
		 * @param string       $content Content to send
		 * @param bool         $silent  If true, no sound is played
		 *
		 * @return array                 [ 'success' => [ { device_id: ... }, ... ], 'error' => [ { device_id: ... }, ... ] ]
		 *
		 * @throws DeviceNotFoundException If at least one device could not be found
		 * @throws InvalidAppTokenException If the AppToken has expired or is invalid
		 * @throws PushNotifierException If an unknown error happened
		 */
		public function sendMessage(array $devices, string $content, bool $silent = false) {
			if (empty($content)) {
				throw new InvalidRequestException('Empty content.', 400);
			}

			$response = $this->call('PUT', '/notifications/text', [
				'X-AppToken'		=>	$this->getAppToken()->getToken()
			], [
				'devices'			=>	$this->mapDevices($devices),
				'content'			=>	$content,
				'silent'			=>	$silent
			]);

			if ($response->code == 404) {
				throw new DeviceNotFoundException('Could not find a device.', 404);
			}

			return (array) $response->body;
		}

		/**
		 * Send a link
		 *
		 * @param array        $devices Devices to send to, either Device[] or string[] (IDs of the devices)
		 * @param string       $url     URL to send
		 * @param bool         $silent  If true, no sound is played
		 *
		 * @return array                 [ 'success' => [ { device_id: ... }, ... ], 'error' => [ { device_id: ... }, ... ] ]
		 *
		 * @throws DeviceNotFoundException If at least one device could not be found
		 * @throws InvalidAppTokenException If the AppToken has expired or is invalid
		 * @throws PushNotifierException If an unknown error happened
		 */
		public function sendURL(array $devices, string $url, bool $silent = false) {
			if (!filter_var($url, FILTER_VALIDATE_URL)) {
				throw new InvalidRequestException('Invalid URL: ' . $url, 400);
			}

			$response = $this->call('PUT', '/notifications/url', [
				'X-AppToken'		=>	$this->getAppToken()->getToken()
			], [
				'devices'			=>	$this->mapDevices($devices),
				'url'				=>	$url,
				'silent'			=>	$silent
			]);

			if ($response->code == 404) {
				throw new DeviceNotFoundException('Could not find a device.', 404);
			}

			return (array) $response->body;
		}

		/**
		 * Send a notification with content and a URL
		 * The user will be taken to the URL after tapping on the notification
		 *
		 * @param array        $devices Devices to send to, either Device[] or string[] (IDs of the devices)
		 * @param string       $content Content to send
		 * @param string       $url     URL to send
		 * @param bool         $silent  If true, no sound is played
		 *
		 * @return array                 [ 'success' => [ { device_id: ... }, ... ], 'error' => [ { device_id: ... }, ... ] ]
		 *
		 * @throws DeviceNotFoundException If at least one device could not be found
		 * @throws InvalidAppTokenException If the AppToken has expired or is invalid
		 * @throws PushNotifierException If an unknown error happened
		 */
		public function sendNotification(array $devices, string $content, string $url, bool $silent = false) {
			if (!filter_var($url, FILTER_VALIDATE_URL)) {
				throw new InvalidRequestException('Invalid URL: ' . $url, 400);
			}

			if (empty($content)) {
				throw new InvalidRequestException('Empty content.', 400);
			}

			$response = $this->call('PUT', '/notifications/notification', [
				'X-AppToken'		=>	$this->getAppToken()->getToken()
			], [
				'devices'			=>	$this->mapDevices($devices),
				'content'			=>	$content,
				'url'				=>	$url,
				'silent'			=>	$silent
			]);

			if ($response->code == 404) {
				throw new DeviceNotFoundException('Could not find a device.', 404);
			}

			return (array) $response->body;
		}

		/**
		 * Map devices from objects to their IDs
		 *
		 * @param string[]|Device[]  $devices Devices
		 *
		 * @return string[]
		 */
		protected function mapDevices(array $devices) {
			return array_map(function($d) {
				if ($d instanceof Device) {
					return $d->getID();
				}

				return $d;
			}, $devices);
		}

		protected function call(string $method, string $url, array $headers, array $body) {
			$headers = array_merge($headers, [
				'Authorization'		=>	'Basic ' . $this->authorization,
				'Accept'			=>	'application/json',
				'Content-Type'		=>	'application/json'
			]);

			$body = Body::json($body);
			$response = Request::$method(self::BASE_URL . $url, $headers, $body);

			if ($response->code == 500) {
				throw new PushNotifierException('Unknown error: ' . $response->body->message, $response->code);
			}

			if ($response->code == 401) {
				throw new InvalidAppTokenException('Invalid app_token: ' . var_export($this->appToken, true), 401);
			}

			return $response;
		}
	}