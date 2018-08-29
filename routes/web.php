<?php

use Illuminate\Support\Facades\Route;

Route::get('/logout', 'Saml2Controller@logout')->name('saml2.logout');
Route::get('/login', 'Saml2Controller@login')->name('saml2.login');
Route::get('/metadata', 'Saml2Controller@metadata')->name('saml2.metadata');
Route::post('/acs', 'Saml2Controller@acs')->name('saml2.acs');
Route::get('/sls', 'Saml2Controller@sls')->name('saml2.sls');
