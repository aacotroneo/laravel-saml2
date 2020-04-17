## Laravel 5 - Saml2

[![Build Status](https://travis-ci.org/aacotroneo/laravel-saml2.svg)](https://travis-ci.org/aacotroneo/laravel-saml2)

A Laravel package for Saml2 integration as a SP (service provider) based on  [OneLogin](https://github.com/onelogin/php-saml) toolkit, which is much lighter and easier to install than simplesamlphp SP. It doesn't need separate routes or session storage to work!

The aim of this library is to be as simple as possible. We won't mess with Laravel users, auth, session...  We prefer to limit ourselves to a concrete task. Ask the user to authenticate at the IDP and process the response. Same case for SLO (Single Logout) requests.

## How to upgrade 1.x to 2.x
* Backup existing `config/saml2_settings.php`
* Delete existing `config/saml2_settings.php`
* Edit `config/app.php`:
```php
'providers' => [
        ...
    	Aacotroneo\Saml2\Saml2ServiceProvider::class,
]
```
* Republish the config files with `php artisan vendor:publish --provider="Aacotroneo\Saml2\Saml2ServiceProvider"`.
* Edit `app/Http/Middleware/VerifyCsrfToken.php`
```php
protected $except = [
    ...
    '/saml2/*'
];
```
* Modify `app/Exceptions/Handler.php`
```php
protected function unauthenticated($request, AuthenticationException $exception)
{
    if ($request->expectsJson())
    {
        return response()->json(['error' => 'Unauthenticated.'], 401); // Or, return a response that causes client side js to redirect to '/routesPrefix/myIdp1/login'
    }

    $saml2Auth = new Saml2Auth(Saml2Auth::loadOneLoginAuthFromIpdConfig('mytestidp1'));
    return $saml2Auth->login('/my/redirect/path');
}
```
* If you are using only "web" and "api" routeMiddleware's then in your config/saml2_settings.php need to be:
 ```php
    ...
    'routesMiddleware' => ['web'],
    ...
 ```

## Installation - Composer

You can install the package via composer:

```
composer require aacotroneo/laravel-saml2
```
Or manually add this to your composer.json:

**composer.json**
```json
"aacotroneo/laravel-saml2": "*"
```

If you are using Laravel 5.5 and up, the service provider will automatically get registered.

For older versions of Laravel (<5.5), you have to add the service provider:

**config/app.php**
```php
'providers' => [
        ...
    	Aacotroneo\Saml2\Saml2ServiceProvider::class,
]
```

Then publish the config files with `php artisan vendor:publish --provider="Aacotroneo\Saml2\Saml2ServiceProvider"`. This will add the files `app/config/saml2_settings.php` & `app/config/saml2/mytestidp1_idp_settings.php`, which you will need to customize.

The `mytestidp1_idp_settings.php` config is handled almost directly by  [OneLogin](https://github.com/onelogin/php-saml) so you should refer to that for full details, but we'll cover here what's really necessary. There are some other config about routes you may want to check, they are pretty strightforward.

### Configuration

#### Define the IDPs
Define names of all the IDPs you want to configure in `saml2_settings.php`. Optionally keep `mytestidp1` (case-sensitive) as the first IDP if you want to use the simplesamlphp demo, and add real IDPs after that. The name of the IDP will show up in the URL used by the Saml2 routes this library makes, as well as internally in the filename for each IDP's config.

**config/saml2_settings.php**
```php
    'idpNames' => ['mytestidp1', 'test', 'myidp2'],
```

#### Configure laravel-saml2 to know about each IDP

You will need to create a separate configuration file for each IDP under `app/config/saml2/` folder. e.g. `test_idp_settings.php`. You can use `mytestidp1_idp_settings.php` as the starting point; just copy it and rename it.

Configuration options are not explained in this project as they come from the [OneLogin project](https://github.com/onelogin/php-saml), please refer there for details.

The only real difference between this config and the one that OneLogin uses, is that the SP `entityId`, `assertionConsumerService` URL and `singleLogoutService` URL are injected by the library. 

If you don't specify URLs in the corresponding IDP config optional values, this library provides defaults values. The library creates the `metadata`, `acs`, and `sls` routes for each IDP. If you specify different values in the config, note that the `acs` and `sls` URLs should correspond to actual routes that you set up that are directed to the corresponding Saml2Controller function.

If you want to optionally define values in ENV vars instead of the `{idpName}_idp_settings` file, you'll see in there that there is a naming pattern you can follow for ENV values. For example, if in `mytestipd1_idp_settings.php` you set `$this_idp_env_id = 'mytestidp1';`, and in `myidp2_idp_settings.php` you set `$this_idp_env_id = 'myidp2'`, then you can set ENV vars starting with `SAML2_mytestidp1_` and `SAML2_myidp2_` respectively.

For example, it can be:

**.env**
```env
SAML2_mytestidp1_SP_x509="..."
SAML2_mytestidp1_SP_PRIVATEKEY="..."
// Other  SAML2_mytestidp1_* values

SAML2_myidp2_SP_x509="..."
SAML2_myidp2_SP_PRIVATEKEY="..."
// Other SAML2_myidp2_* values
```

#### URLs To Pass to The IDP configuration
As mentioned above, you don't need to implement the SP `entityId`, `assertionConsumerService` URL and `singleLogoutService` URL routes, because Saml2Controller already does by default. But you need to know these routes, to provide them to the configuration of your actual IDP, i.e. the 3rd party you are asking to authenticate users.

You can check the actual routes in the metadata, by navigating to `http(s)://{laravel_url}/{idpName}/metadata`, e.g. `http(s)://{laravel_url}/mytestidp1/metadata` which incidentally will be the default entityId for this SP.

If you configure the optional `routesPrefix` setting in `saml2_settings.php`, then all idp routes will be prefixed by that value, so you'll need to adjust the metadata url accordingly. For example, if you configure `routesPrefix` to be `'single_sign_on'`, then your IDP metadata for `mytestidp1` will be found at `http(s)://{laravel_url}/single_sign_on/mytestidp1/metadata`.

The routes automatically created by the library for each IDP are:
- `{routesPrefix}/{idpName}/logout`
- `{routesPrefix}/{idpName}/login`
- `{routesPrefix}/{idpName}/metadata`
- `{routesPrefix}/{idpName}/acs`
- `{routesPrefix}/{idpName}/sls`

#### Example: simplesamlphp IDP configuration
If you use simplesamlphp as a test IDP, and your SP metadata url is `http(s)://{laravel_url}/mytestidp1/metadata`, add the following to `/metadata/sp-remote.php` to inform the IDP of your laravel-saml2 SP identity.

For example, it can be:

**/metadata/sp-remote.php**
```php
$metadata['http(s)://{laravel_url}/mytestidp1/metadata'] = array(
    'AssertionConsumerService' => 'http(s)://{laravel_url}/mytestidp1/acs',
    'SingleLogoutService' => 'http(s)://{laravel_url}/mytestidp1/sls',
    //the following two affect what the $Saml2user->getUserId() will return
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    'simplesaml.nameidattribute' => 'uid' 
);
```

### Usage

When you want your user to login, just redirect to the login route configured for the particular IDP, `route('saml2_login', 'mytestidp1')`. You can also instantiate a `Saml2Auth` for the desired IDP using the `Saml2Auth::loadOneLoginAuthFromIpdConfig('mytestidp1')` function to load the config and construct the OneLogin auth argment; just remember that it does not use any session storage, so if you ask it to login it will redirect to the IDP whether the user is already logged in or not. For example, you can change your authentication middleware.

For example, it can be:

**App/Http/Middleware/RedirectIfAuthenticated.php**
```php
public function handle($request, Closure $next)
{
    if ($this->auth->guest())
    {
        if ($request->ajax())
        {
            return response('Unauthorized.', 401); // Or, return a response that causes client side js to redirect to '/routesPrefix/myIdp1/login'
        }
        else
        {
            $saml2Auth = new Saml2Auth(Saml2Auth::loadOneLoginAuthFromIpdConfig('mytestidp1'));
            return $saml2Auth->login(URL::full());
        }
    }

    return $next($request);
}
```

Since Laravel 5.3, you can change your unauthenticated method.

For example, it can be:

**App/Exceptions/Handler.php**
```php
protected function unauthenticated($request, AuthenticationException $exception)
{
    if ($request->expectsJson())
    {
        return response()->json(['error' => 'Unauthenticated.'], 401); // Or, return a response that causes client side js to redirect to '/routesPrefix/myIdp1/login'
    }

    $saml2Auth = new Saml2Auth(Saml2Auth::loadOneLoginAuthFromIpdConfig('mytestidp1'));
    return $saml2Auth->login('/my/redirect/path');
}
```

For login requests that come through redirects to the login route, `{routesPrefix}/mytestidp1/login`, the default login call does not pass a redirect URL to the Saml login request. That login argument is useful because the ACS handler can gets that value (passed back from the IDP as RelayPath) and by default will redirect there. To pass the redirect URL from the controller login, extend the Saml2Controller class and implement your own `login()` function. Set the `config/saml2_settings.php` value `saml2_controller` to be your extended class so that the routes will direct requests to your controller instead of the default.  

For example, it can be:

**config/saml_settings.php**
```
    'saml2_controller' => 'App\Http\Controllers\MyNamespace\MySaml2Controller'
```
**App/Http/Controllers/MyNamespace/MySaml2Controller.php**
```php
use Aacotroneo\Saml2\Http\Controllers\Saml2Controller;

class MySaml2Controller extends Saml2Controller
{
    public function login()
    {
        $loginRedirect = '...'; // Determine redirect URL
        $this->saml2Auth->login($loginRedirect);
    }
}
```

After login is called, the user will be redirected to the IDP login page. Then the IDP, which you have configured with an endpoint the library serves, will call back, e.g. `/mytestidp1/acs` or `/{routesPrefix}/mytestidp1/acs`. That will process the response and fire an event when ready. The next step for you is to handle that event. You just need to login the user or refuse.

For example, it can be:

**App/Providers/MyEventServiceProvider.php**
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

Be careful about necessary Laravel middleware for Auth persistence in Session.

For example, it can be:

**App/Http/Kernel.php**
```php
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
**config/saml2_settings.php**
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
 + 2 - By logging out of the global SSO Session. In this case the IDP will notify you on `/mytestidp1/slo` endpoint (already provided), if the IDP supports SLO

For case 1, call `Saml2Auth::logout();` or redirect the user to the logout route, e.g. `mytestidp1_logout` which does just that. Do not close the session immediately as you need to receive a response confirmation from the IDP (redirection). That response will be handled by the library at `/mytestidp1/sls` and will fire an event for you to complete the operation.

For case 2, you will only receive the event. Both cases 1 and 2 receive the same event. 


Note that for case 2, you may have to manually save your session to make the logout stick (as the session is saved by middleware, but the OneLogin library will redirect back to your IDP before that happens)

For example, it can be:

**App/Providers/MyEventServiceProvider.php**
```php
Event::listen('Aacotroneo\Saml2\Events\Saml2LogoutEvent', function ($event) {
    Auth::logout();
    Session::save();
});
```

That's it. Feel free to ask any questions, make PR or suggestions, or open Issues.
