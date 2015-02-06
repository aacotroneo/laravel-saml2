<?php

//Config::get('saml2::settings.routesPrefix')
Route::group(array('prefix' => 'saml'), function () {

    Route::get('/logout', array(
        'as' => 'saml_logout',
        'uses' => 'Aacotroneo\Saml2\Controllers\Saml2Controller@logout',
    ));

    Route::get('/metadata', array(
        'as' => 'saml_metadata',
        'uses' => 'Aacotroneo\Saml2\Controllers\Saml2Controller@metadata',
    ));

    Route::post('/acs', array(
        'as' => 'saml_acs',
        'uses' => 'Aacotroneo\Saml2\Controllers\Saml2Controller@acs',
    ));

    Route::get('/sls', array(
        'as' => 'saml_sls',
        'uses' => 'Aacotroneo\Saml2\Controllers\Saml2Controller@sls',
    ));
});
