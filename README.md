## Laravel-saml2
A Laravel package for Saml2 integration as a SP (service provider) based on OneLogin toolkit, which is much 'simple' than 'simple'samlphp

The aim of this library is to be as simple as possible. We won't even mess with Laravel users or session, what would be the point of coupling those functionalities? But wait, that's easy to do in Laravel, and it will adapt to many use cases out of the box! (i.e I'm using it without users in my projects) 

### Usage
When you want your user to login, just call `Saml2Auth::login()`. Just remember that it does not use any storage, so if you call it you got it. For example, check if user is not logged in Laravel before.
```
    if(Auth::check()){
        return Response::make('hello' . Auth::getUser()->getAuthIdentifier());
    }else {
        Saml2Auth::login();
    }
```

Only if you want to know, that will redirect the user to the IDP, and will came back to an endpoint the library adds at /saml2/acs. That will process the response and fire an event when is ready. So, next step is for you to handle the response.

```
Event::listen('saml2.loginRequestReceived', function(Saml2User $user)
{
    echo "welcome . " . print_r($user->getAttributes(), true);
    echo "<br/>Your id is . " . print_r($user->getUserId(), true);
    echo "<br/>Your assertion is . " . print_r(base64_decode($user->getRawSamlAssertion()), true);
});
```
In most cases you will just log the user into Laravel and redirect.
```
   $user = User::find ... //use $user->getUserId() or some attribute;
   Auth::login($user);
   //if it does not exist create it or show a message
```
### Log out
Now there are two ways to log out. When the user logs out in your app you may send a Logout request to the IDP (which will in turn broadcast the logout to all other service providers), or when the users logs out in another service provider. For the first case just call `Saml2Auth::logout();` or redirect use the route 'saml_logout' which just does that. Do not close session inmediately as you need to receive a response from the IDP. For the latter case (and after calling logout()) you'll want to listen to the other event we fire.

```
Event::listen('saml2.logoutRequestReceived', function()
{
    Auth::logout();
    //if you logged out locally you can redirect here, if not Auth::logout() will redirect first
    echo "bye, we logged out.";
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


TO BE CONTINUED...
## Exposed SAML2 Endpoints

Take a look at the [routes](https://github.com/aacotroneo/laravel-saml2/blob/master/src/routes.php) this module add to laravel. You don't need to touch them
### 





$metadata['http://localhost:8000/usuarios/be/saml/metadata'] = array(
    'AssertionConsumerService' => 'http://localhost:8000/usuarios/be/saml/acs',
    'SingleLogoutService' => 'http://localhost:8000/usuarios/be/saml/sls',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    'simplesaml.nameidattribute' => 'uid'
);




