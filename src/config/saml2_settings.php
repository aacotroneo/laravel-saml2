<?php

return $settings = array(

    /**
     * Array of IDP prefixes to be configured e.g. 'idpNames' => ['test1', 'test2', 'test3'],
     * Separate routes will be automatically registered for each IDP specified with IDP name as prefix
     * Separate config file saml2/<idpName>_idp_settings.php should be added & configured accordingly
     **/
    'idpNames' => ['test'],

    /**
     * Cosmetic settings - controller routes
     **/
    'useRoutes' => true, //include library routes and controllers

    /**
     * which middleware group to use for the saml routes
     * Laravel 5.2 will need a group which includes StartSession
     */
    'routesMiddleware' => [],

    /**
     * Indicates how the parameters will be
     * retrieved from the sls request for signature validation
     */
    'retrieveParametersFromServer' => false,

    /**
     * Where to redirect after logout
     */
    'logoutRoute' => '/',

    /**
     * Where to redirect after login if no other option was provided
     */
    'loginRoute' => '/',


    /**
     * Where to redirect after login if no other option was provided
     */
    'errorRoute' => '/',

);
