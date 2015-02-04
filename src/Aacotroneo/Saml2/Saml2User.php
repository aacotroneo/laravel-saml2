<?php

namespace Aacotroneo\Saml2;

use Input;
use OneLogin_Saml2_Auth;
use URL;

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
        return Input::get('SAMLResponse'); //just this request
    }

    function getIntendedUrl()
    {
        $relayState = Input::get('RelayState'); //just this request

        if ($relayState && URL::full() !=$relayState) {

            return $relayState;
        }
    }

} 