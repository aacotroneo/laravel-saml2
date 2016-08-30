<?php

Route::group([
    'prefix' => config('saml2_settings.routesPrefix'),
    'middleware' => config('saml2_settings.routesMiddleware'),
    'middleware' => 'session',
], function () {

    Route::get('/logout', array(
        'as' => 'saml_logout',
        'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@logout',
    ));

    Route::get('/login', array(
        'as' => 'saml_login',
        'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@login',
    ));

    Route::get('/metadata', array(
        'as' => 'saml_metadata',
        'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@metadata',
    ));

    Route::post('/acs', array(
        'as' => 'saml_acs',
        'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@acs',
    ));

    Route::post('/sls', array(
        'as' => 'saml_sls',
        'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@sls',
    ));
});
