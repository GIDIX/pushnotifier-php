# PushNotifier (v2) for PHP

A pretty convenient library to use PushNotifier in PHP projects.

## Installation

Add this to `composer.json`:

```json
"require": {
	"gidix/pushnotifier-php": "^2.0.0"
}
```

...or run `composer require gidix/pushnotifier-php`

## Usage

You only have to use one class: `GIDIX\PushNotifier\SDK\PushNotifier`. Everything is derived from there.

### Creating your Application

Before you can actually do anything, you have to create your application. It consists of an API Token and a package name. Both can be configured at [pushnotifier.de/account/api](https://pushnotifier.de/account/api).

Then you can create your instance:

```php
    $app = new GIDIX\PushNotifier\SDK\PushNotifier([
        'api_token'     =>  'YOUR_API_TOKEN',
        'package'       =>  'YOUR.PACKAGE.NAME'
    ]);
```

### Login

When authenticating as a user you have to log them in, then use their AppToken for all further communication:

```php
    $appToken = $app->login('username', 'password');
```

You can store `$appToken` anywhere you like by converting it to a string before storing (see `examples/storing-app-token.php`).

Afterwards you can use the AppToken to authenticate:

```php
    $app = new GIDIX\PushNotifier\SDK\PushNotifier([
        'api_token'     =>  'YOUR_API_TOKEN',
        'package'       =>  'YOUR.PACKAGE.NAME',
        'app_token'     =>  $appToken
    ]);
```

### Retrieving Devices

```php
    $devices = $app->getDevices();
```

will retrieve an array of `GIDIX\PushNotifier\SDK\Device` objects, containing an ID, a title, a model and a link to an image of the device. Device IDs do *not* change over time and serve as a unique identifier.


### Sending Texts

```php
    $result = $app->sendMessage($devices, 'Some Content');
```

`$devices` has to be an array of either `Device` objecs or device ID strings, i.e. `['abc', 'xyz']`.

### Sending URLs

```php
    $result = $app->sendURL($devices, 'https://some.example.org/with/path.html');
```

`$devices` has to be an array of either `Device` objecs or device ID strings, i.e. `['abc', 'xyz']`.

### Sending Notifications

```php
    $result = $app->sendNotification($devices, 'Some Content', https://some.example.org/with/path.html');
```

## Exceptions

- **DeviceNotFoundException**: When a device couldn't be found
- **InvalidAPITokenException**: When the api_token or package couldn't be verified
- **InvalidAppTokenException**: When the AppToken couldn't be verified or has expired
- **InvalidCredentialsException**: When login credentials were incorrect
- **InvalidRequestException**: When some request data was malformatted, i.e. malformatted URL for notifications
- **PushNotifierException**: Base Exception for all these, only thrown in case of an unknown error (500)

`$devices` has to be an array of either `Device` objecs or device ID strings, i.e. `['abc', 'xyz']`.

## Examples

Examples can be found in `examples/` of this repository.
