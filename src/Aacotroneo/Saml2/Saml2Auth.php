<?php

namespace Aacotroneo\Saml2;

use OneLogin_Saml2_Auth;
use OneLogin_Saml2_Error;
use OneLogin_Saml2_Utils;

use Log;
use Psr\Log\InvalidArgumentException;

class Saml2Auth
{

    /**
     * @var \OneLogin_Saml2_Auth
     */
    protected $auth;

    protected $samlAssertion;

    function __construct($config)
    {
        $this->auth = new OneLogin_Saml2_Auth($config);
    }

    /**
     * @return bool if a valid user was fetched from the saml assertion this request.
     */
    function isAuthenticated()
    {
        $auth = $this->auth;

        return $auth->isAuthenticated();
    }

    /**
     * The user info from the assertion
     * @return Saml2User
     */
    function getSaml2User()
    {

        return new Saml2User($this->auth);
    }

    /**
     * Initiate a saml2 login flow. It will redirect! Before calling this, check if user is
     * authenticated (here in saml2). That would be true when the assertion was received this request.
     */
    function login($returnTo = null)
    {
        $auth = $this->auth;

        $auth->login($returnTo);
    }

    /**
     * Initiate a saml2 logout flow. It will close session on all other SSO services. You should close
     * local session if applicable.
     */
    function logout()
    {
        $auth = $this->auth;

        $auth->logout();
    }

    /**
     * Process a Saml response (assertion consumer service)
     * @throws \Exception when errors are encountered. This sould not happen in a normal flow.
     */
    function acs()
    {

        /** @var $auth OneLogin_Saml2_Auth */
        $auth = $this->auth;

        $auth->processResponse();

        $errors = $auth->getErrors();

        if (!empty($errors)) {
            Log::error("Invalid saml response", $errors);
            throw new \Exception("The saml assertion is not valid, please check the logs.");
        }

        if (!$auth->isAuthenticated()) {
            Log::error("Could not authenticate with the saml response. Something happened");
            throw new \Exception("The saml assertion is not valid, please check the logs.");
        }

    }

    /**
     * Process a Saml response (assertion consumer service)
     * @throws \Exception
     */
    function sls()
    {
        $auth = $this->auth;

        $keep_local_session = true; //we don't touch session here
        $auth->processSLO($keep_local_session);

        $errors = $auth->getErrors();

        if (!empty($errors)) {
            Log::error("Could not log out", $errors);
            throw new \Exception("Could not log out");
        }

    }

    /**
     * Show metadata about the local sp. Use this to configure your saml2 IDP
     * @return mixed xml string representing metadata
     * @throws \InvalidArgumentException if metadata is not correctly set
     */
    function getMetadata()
    {
        $auth = $this->auth;
        $settings = $auth->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);

        if (empty($errors)) {

            return $metadata;
        } else {

            throw new InvalidArgumentException(
                'Invalid SP metadata: ' . implode(', ', $errors),
                OneLogin_Saml2_Error::METADATA_SP_INVALID
            );
        }
    }


} 