<?php

namespace Aacotroneo\Saml2;

use OneLogin_Saml2_Auth;

/**
 * A simple class that represents the user that 'came' inside the saml2 assertion
 * Class Saml2User
 * @package Aacotroneo\Saml2
 */
class Saml2User
{

    protected $auth;

    function __construct(OneLogin_Saml2_Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return string User Id retrieved from assertion processed this request
     */
    function getUserId()
    {
        $auth = $this->auth;

        return $auth->getNameId();

    }

    /**
     * @return array attributes retrieved from assertion processed this request
     */
    function getAttributes()
    {
        $auth = $this->auth;

        return $auth->getAttributes();
    }

    /**
     * @return string the saml assertion processed this request
     */
    function getRawSamlAssertion()
    {
        return app('request')->input('SAMLResponse'); //just this request
    }

    function getIntendedUrl()
    {
        $relayState = app('request')->input('RelayState'); //just this request

        $url = app('Illuminate\Contracts\Routing\UrlGenerator');

        if ($relayState && $url->full() != $relayState) {

            return $relayState;
        }
    }

} 