<?php

Route::middleware(config('saml2_settings.routesMiddleware'))
->prefix(config('saml2_settings.routesPrefix').'/')->group(function() {
    Route::prefix('{idpName}')->group(function() {
        Route::get('/logout', array(
            'as' => 'saml2_logout',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@logout',
        ));

        Route::get('/login', array(
            'as' => 'saml2_login',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@login',
        ));

        Route::get('/metadata', array(
            'as' => 'saml2_metadata',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@metadata',
        ));

        Route::post('/acs', array(
            'as' => 'saml2_acs',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@acs',
        ));

        Route::get('/sls', array(
            'as' => 'saml2_sls',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@sls',
        ));
    });
});
