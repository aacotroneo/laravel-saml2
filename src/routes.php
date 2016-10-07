<?php

foreach (config('saml2_settings.idpNames') as $key => $value) {
   
    Route::group([
        'prefix' => $value,
        'middleware' => config('saml2_settings.routesMiddleware'),
    ], function () use ($value) {
        
        Route::get('/logout', array(
            'as' => $value.'_logout',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@logout',
        ));

        Route::get('/login', array(
            'as' => $value.'_login',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@login',
        ));

        Route::get('/metadata', array(
            'as' => $value.'_metadata',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@metadata',
        ));

        Route::post('/acs', array(
            'as' => $value.'_acs',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@acs',
        ));

        Route::get('/sls', array(
            'as' => $value.'_sls',
            'uses' => 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller@sls',
        ));
    });

}