Forked from aacotroneo/laravel-saml2. Idea is to enhance the existing code to support configuration for multiple IDPs.

## Laravel 5 - Saml2

[![Build Status](https://travis-ci.org/nirajp/laravel-saml2.svg)](https://travis-ci.org/nirajp/laravel-saml2)

A Laravel package for Saml2 integration as a SP (service provider) based on  [OneLogin](https://github.com/onelogin/php-saml) toolkit, which is much lighter and easier to install than simplesamlphp SP. It doesn't need separate routes or session storage to work!

The aim of this library is to be as simple as possible. We won't mess with Laravel users, auth, session...  We prefer to limit ourselves to a concrete task. Ask the user to authenticate at the IDP and process the response. Same case for SLO requests.


## Installation - Composer

You can install the package via composer:

```
composer require nirajp/laravel-saml2
```
Or manually add this to your composer.json:

```json
"nirajp/laravel-saml2": "*"
```

If you are using Laravel 5.5 and up, the service provider will automatically get registered.

For older versions of Laravel (<5.5), you have to add the service provider and alias to config/app.php:

```php
'providers' => [
        ...
    	Aacotroneo\Saml2\Saml2ServiceProvider::class,
]

'alias' => [
        ...
        'Saml2' => Aacotroneo\Saml2\Facades\Saml2Auth::class,
]
```

Then publish the config files with `php artisan vendor:publish --provider="Aacotroneo\Saml2\Saml2ServiceProvider"`. This will add the files `app/config/saml2_settings.php` & `app/config/saml2/test_idp_settings.php`.

The test_idp_settings.php config is handled almost directly by  [OneLogin](https://github.com/onelogin/php-saml) so you may get further references there, but will cover here what's really necessary. There are some other config about routes you may want to check, they are pretty strightforward.

### Configuration

#### Define the IDPs
Define names of all the IDPs you want to configure in saml2_settings.php. Optionally keep 'test' as the first IDP if you want to use the simplesamlphp demo, and add real IDPs after that.

```php
    'idpNames' => ['test', 'myidp1', 'myidp2'],
```

#### Configure laravel-saml2 to know about each IDP

You will need to create a separate configuration file for each IDP under `app/config/saml2/` folder. e.g. `myidp1_idp_settings.php`. You can use `test_idp_settings.php` as the starting point; just copy it to `app/config/saml2/` and rename it.

Configuration options are note explained in this project as they come from the [OneLogin project](https://github.com/onelogin/php-saml), please refer there for details.

The only real difference between this config and the one that OneLogin uses, is that the SP entityId, assertionConsumerService url and singleLogoutService URL are injected by the library. If you don't specify those URLs in the corresponding IDP config optional values, this library provides defaults values, the routes that this library creates for each IDP, which are given route names "{$idpName}_metadata", "{$idpName}_acs" and "{$idpName}_sls".

If you want to define values in ENV vars instead of the \*\_idp_settings file, you'll see in there that the ENV values follow a naming pattern. For example, if in myipd1_idp_settings.php you set `$this_idp_env_id = 'MYIDP1';`, and in myidp2_idp_settings.php you set it to `'SECONDIDP'`, then you can set ENV vars starting with `SAML2_MYDP1_` and `SAML2_SECONDIDP_`, e.g.
```env
SAML2_MYIDP1_SP_x509="..."
SAML2_MYIDP1_SP_PRIVATEKEY="..."
// Other  SAML2_MYIDP1_* values

SAML2_SECONDIDP_SP_x509="..."
SAML2_SECONDIDP_SP_PRIVATEKEY="..."
// Other SAML2_SECONDIDP_* values
```

#### URLs To Pass to The IDP configuration
You don't need to implement the SP entityId, assertionConsumerService url and singleLogoutService routes, because Saml2Controller already does, but you'll need to provide them to the configuration of your actual IDP, i.e. the 3rd party you are asking to authenticate users.

You can check the actual routes in the metadata, by navigating to 'http://laravel_url/myidp1/metadata' / 'https://laravel_url/myidp1/metadata', which incidentally will be the entityId for this SP.

If you configure the optional `routesPrefix` setting in saml2_settings.php, then all idp routes will be prefixed by that value, so you'll need to adjust the metadata url accordingly. For example, if you configure routesPrefix to be `'single_sign_on'`, then your IDP metadata for myidp1 will be found at http://laravel_url/single_sign_on/myidp1/metadata.

#### Exampl: simplesamlphp IDP configuration
For example, if you use simplesamlphp, and your metadata url is `http://laravel_url/myidp1/metadata`, add the following to /metadata/sp-remote.php to inform the IDP of your laravel-saml2 SP identity:

```php
$metadata['http://laravel_url/myidp1/metadata'] = array(
    'AssertionConsumerService' => 'http://laravel_url/myidp1/acs',
    'SingleLogoutService' => 'http://laravel_url/myidp1/sls',
    //the following two affect what the $Saml2user->getUserId() will return
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    'simplesaml.nameidattribute' => 'uid' 
);
```


### Usage

When you want your user to login, just call `Saml2Auth::login()` or redirect to route 'saml2_login'. Just remember that it does not use any session storage, so if you ask it to login it will redirect to the IDP whether the user is logged in or not. For example, you can change your authentication middleware.
```php
	public function handle($request, Closure $next)
	{
		if ($this->auth->guest())
		{
			if ($request->ajax())
			{
				return response('Unauthorized.', 401);
			}
			else
			{
        			 return Saml2::login(URL::full());
                		 //return redirect()->guest('auth/login');
			}
		}

		return $next($request);
	}
```

Since Laravel 5.3, you can change your unauthenticated method in ```app/Exceptions/Handler.php```.
```php
protected function unauthenticated($request, AuthenticationException $exception)
{
	if ($request->expectsJson())
        {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return Saml2Auth::login();
}
```

The Saml2::login will redirect the user to the IDP and will came back to an endpoint the library serves at /myidp1/acs (or routesPrefix/myidp1/acs). That will process the response and fire an event when ready. The next step for you is to handle that event. You just need to login the user or refuse.

```php

 Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function (Saml2LoginEvent $event) {
            $messageId = $event->getSaml2Auth()->getLastMessageId();
            // Add your own code preventing reuse of a $messageId to stop replay attacks

            $user = $event->getSaml2User();
            $userData = [
                'id' => $user->getUserId(),
                'attributes' => $user->getAttributes(),
                'assertion' => $user->getRawSamlAssertion()
            ];
             $laravelUser = //find user by ID or attribute
             //if it does not exist create it and go on  or show an error message
             Auth::login($laravelUser);
        });

```
### Auth persistence

Becarefull about necessary Laravel middleware for Auth persistence in Session.

For exemple, it can be:

```
# in App\Http\Kernel
protected $middlewareGroups = [
        'web' => [
	    ...
	],
	'api' => [
            ...
        ],
        'saml' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ],

```

And in `config/saml2_settings.php` :
```
    /**
     * which middleware group to use for the saml routes
     * Laravel 5.2 will need a group which includes StartSession
     */
    'routesMiddleware' => ['saml'],
```

### Log out
Now there are two ways the user can log out.
 + 1 - By logging out in your app: In this case you 'should' notify the IDP first so it closes global session.
 + 2 - By logging out of the global SSO Session. In this case the IDP will notify you on /myidp1/slo endpoint (already provided), if the IDP supports SLO

For case 1 call `Saml2Auth::logout();` or redirect the user to the logout route, e.g. 'myidp1_logout' which does just that. Do not close the session immediately as you need to receive a response confirmation from the IDP (redirection). That response will be handled by the library at /myidp1/sls and will fire an event for you to complete the operation.

For case 2 you will only receive the event. Both cases 1 and 2 receive the same event. 

Note that for case 2, you may have to manually save your session to make the logout stick (as the session is saved by middleware, but the OneLogin library will redirect back to your IDP before that happens)

```php
        Event::listen('Aacotroneo\Saml2\Events\Saml2LogoutEvent', function ($event) {
            Auth::logout();
            Session::save();
        });
```


That's it. Feel free to ask any questions, make PR or suggestions, or open Issues.
