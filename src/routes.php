<?php

//Config::get('administrator::administrator.uri')
Route::group(array('prefix' => '/saml'), function() {
    //Admin Dashboard
    Route::get('/metadata', array(
        'as' => 'saml_metadata',
        'uses' => 'Aacotroneo\Saml2\Saml2Controller@metadata',
    ));

    Route::post('/acs', array(
        'as' => 'saml_metadata',
        'uses' => 'Aacotroneo\Saml2\Controllers\AdminController@acs',
    ));

    Route::get('/sls', array(
        'as' => 'saml_metadata',
        'uses' => 'Aacotroneo\Saml2\Controllers\AdminController@sls',
    ));
});
