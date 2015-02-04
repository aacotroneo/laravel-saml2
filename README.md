## Laravel 4 - Saml2
A Laravel package for Saml2 integration as a SP (service provider) based on OneLogin toolkit, which is much lighter and easier to install than simplesamlphp SP. It doesn't need separate routes or session storage to work!

The aim of this library is to be as simple as possible. We won't mess with Laravel users, auth, session...  We prefer to limit ourselves to a concrete task. Ask the user to authenticate at the IDP and process the response. Same case for SLO requests.

### Usage

When you want your user to login, just call `Saml2Auth::login()`. Just remember that it does not use any session storage, so if you ask it to login it will redirect to the IDP wheather the user is logged in or not. For example, you can change the auth filter.
```php
Route::filter('auth', function()
{
	if (Auth::guest())
	{ 
		return SAML2::login(URL::full()); //url is saved in RelayState
		
	}
});
```

Only if you want to know, that will redirect the user to the IDP, and will came back to an endpoint the library serves at /saml2/acs. That will process the response and fire an event when is ready. So, next step for you is to handle the response.

```php

Event::listen('saml2.loginRequestReceived', function(Saml2User $user)
{
    //$user->getAttributes();
    //$user->getUserId();
    //base64_decode($user->getRawSamlAssertion();
    $laravelUser = //find user by ID or attribute
    //if it does not exist create it and go on  or show an error message
    Auth::login($laravelUser);
    $redirectUrl = $user->getIntendedUrl(); //this is URL::full() in our example
    if($redirectUrl !== null){
        Redirect::to($redirectUrl);    
    }else {
        Redirect::to('/');
    }
    
});
```
### Log out
Now there are two ways the user can log out.
 + 1 - By logging out in your app: In this case you 'should' notify the IDP first so it closes global session.
 + 2 - By logging out of the global SSO Session. In this case the IDP will notify you on /saml2/slo enpoint (already provided)

For case 1 call `Saml2Auth::logout();` or redirect the user to the route 'saml_logout' which does just that. Do not close session inmediately as you need to receive a response confirmation from the IDP (redirection). That response will be handled by the library at /saml2/sls and will fire an event for you to complete the operation.

For case 2 you will only receive the event. Both cases 1 and 2 receive the same event. 

```
Event::listen('saml2.logoutRequestReceived', function()
{
    Auth::logout();
    //echo "bye, we logged out.";
    //For case 2, logout() will redirect somewhere else. If we are here, it's case 1, so we can redirect elsewhere
    Redirect::to('/public');
});
```

## Installation - Composer

To install Saml2 as a Composer package to be used with Laravel 4, simply add this to your composer.json:

```json
"aacotroneo/laravel-saml2": "0.0.1"
```

..and run `composer update`.  Once it's installed, you can register the service provider in `app/config/app.php` in the `providers` array:

```php
'providers' => array(
    		'Aacotroneo\Saml2\Saml2ServiceProvider',
)
```

Then publish the config file with `php artisan config:publish aacotroneo/laravel-saml2`. This will add the file `app/config/packages/aacotroneo/laravel-saml2/saml_settings.php`. This config is handled almost directly by  [one login](https://github.com/onelogin/php-saml) so you may get further references there, but will cover here what's really necessary.

### Configuration

Once you publish your saml_settings.php to your own files, you need to configure your sp and IDP (remote server). The only real difference between this config and the one that OneLogin uses, is that the SP entityId, assertionConsumerService url and singleLogoutService URL are inyected by the library. They are taken from routes 'saml_metadata', 'saml_acs' and 'saml_sls' respectively.

Remember that you don't need to implement those routes, but you'll need to add them to your IDP configuration. For example, if you use simplesamlphp, add the following to /metadata/sp-remote.php

```php
$metadata['http://laravel_url/saml/metadata'] = array(
    'AssertionConsumerService' => 'http://laravel_url/saml/acs',
    'SingleLogoutService' => 'http://laravel_url/saml/sls',
    //the following two affect what the $Saml2user->getUserId() will return
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    'simplesaml.nameidattribute' => 'uid' 
);
```
You can check that metadata if you actually navigate to 'http://laravel_url/saml/metadata'



That's it. Feel free to ask any questions, make PR or suggestions, or open Issues.





