<?php

$router = app('router');

$router->group([
        'middleware' => config('saml2_settings.routesMiddleware'),
        'prefix' => config('saml2_settings.routesPrefix').'/{idpName}',
    ],
    function() use($router) {

    $saml2_controller = config('saml2_settings.saml2_controller', Aacotroneo\Saml2\Http\Controllers\Saml2Controller::class);

    $router->get('/logout', array(
        'as' => 'saml2_logout',
        'uses' => $saml2_controller.'@logout',
    ));

    $router->get('/login', array(
        'as' => 'saml2_login',
        'uses' => $saml2_controller.'@login',
    ));

    $router->get('/metadata', array(
        'as' => 'saml2_metadata',
        'uses' => $saml2_controller.'@metadata',
    ));

    $router->post('/acs', array(
        'as' => 'saml2_acs',
        'uses' => $saml2_controller.'@acs',
    ));

    $router->get('/sls', array(
        'as' => 'saml2_sls',
        'uses' => $saml2_controller.'@sls',
    ));
});
