<?php

namespace Aacotroneo\Saml2;

use OneLogin\Saml2\Auth as OneLogin_Saml2_Auth;

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
     * Returns the requested SAML attribute
     *
     * @param string $name The requested attribute of the user.
     * @return array|null Requested SAML attribute ($name).
     */
    function getAttribute($name) {
        $auth = $this->auth;

        return $auth->getAttribute($name);
    }
    
    /**
     * @return array attributes retrieved from assertion processed this request
     */
    function getAttributesWithFriendlyName()
    {
        $auth = $this->auth;

        return $auth->getAttributesWithFriendlyName();
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

    /**
     * Parses a SAML property and adds this property to this user or returns the value
     *
     * @param string $samlAttribute
     * @param string $propertyName
     * @return array|null
     */
    function parseUserAttribute($samlAttribute = null, $propertyName = null) {
        if(empty($samlAttribute)) {
            return null;
        }
        if(empty($propertyName)) {
            return $this->getAttribute($samlAttribute);
        }

        return $this->{$propertyName} = $this->getAttribute($samlAttribute);
    }

    /**
     * Parse the saml attributes and adds it to this user
     *
     * @param array $attributes Array of properties which need to be parsed, like this ['email' => 'urn:oid:0.9.2342.19200300.100.1.3']
     */
    function parseAttributes($attributes = array()) {
        foreach($attributes as $propertyName => $samlAttribute) {
            $this->parseUserAttribute($samlAttribute, $propertyName);
        }
    }

    function getSessionIndex()
    {
        return $this->auth->getSessionIndex();
    }

    function getNameId()
    {
        return $this->auth->getNameId();
    }

}
