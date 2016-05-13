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

    function getAuth() {
        return $this->auth;
    }

    /**
     * @return string the saml assertion processed this request
     */
    function getRawSamlAssertion()
    {
        return app('request')->input('SAMLResponse'); //just this request
    }

    function getSessionIndex() {

        return $this->auth->getSessionIndex();
    }

    function getIntendedUrl()
    {
        $relayState = app('request')->input('RelayState'); //just this request
        $relayState = preg_replace("/http\:\/\/(.*?)\/login/", "http://$1/", $relayState);
        if ($relayState && url()->full() != $relayState) {

            return $relayState;
        }
    }

} 