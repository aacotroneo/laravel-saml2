<?php

$saml2_controller = config('saml2_settings.saml2_controller', 'Aacotroneo\Saml2\Http\Controllers\Saml2Controller');

foreach (config('saml2_settings.idpNames') as $key => $value) {
   
    Route::group([
        'prefix' => config('saml2_settings.routesPrefix').'/'.$value,
        'middleware' => config('saml2_settings.routesMiddleware'),
    ], function () use ($value, $saml2_controller) {
        
        Route::get('/logout', array(
            'as' => $value.'_logout',
            'uses' => $saml2_controller.'@logout',
        ));

        Route::get('/login', array(
            'as' => $value.'_login',
            'uses' => $saml2_controller.'@login',
        ));

        Route::get('/metadata', array(
            'as' => $value.'_metadata',
            'uses' => $saml2_controller.'@metadata',
        ));

        Route::post('/acs', array(
            'as' => $value.'_acs',
            'uses' => $saml2_controller.'@acs',
        ));

        Route::get('/sls', array(
            'as' => $value.'_sls',
            'uses' => $saml2_controller.'@sls',
        ));
    });

}