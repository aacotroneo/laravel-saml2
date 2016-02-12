<?php

namespace Aacotroneo\Saml2;

use OneLogin_Saml2_Auth;
use OneLogin_Saml2_Error;
use OneLogin_Saml2_Utils;
use Aacotroneo\Saml2\Events\Saml2LogoutEvent;

use Log;
use Session;
use Psr\Log\InvalidArgumentException;

class Saml2Auth
{

    /**
     * @var \OneLogin_Saml2_Auth
     */
    protected $auth;

    protected $samlAssertion;

    function __construct(OneLogin_Saml2_Auth $auth)
    {
        $this->auth = $auth;
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
    function logout($returnTo = null)
    {
        $auth = $this->auth;
        $federationData = Session::get('federationData');
        $auth->logout($returnTo, array(), $federationData['nameId'], $federationData['sessionIndex']);
    }

    /**
     * Process a Saml response (assertion consumer service)
     * When errors are encountered, it returns an array with proper description
     */
    function acs()
    {

        /** @var $auth OneLogin_Saml2_Auth */
        $auth = $this->auth;

        $auth->processResponse();

        $errors = $auth->getErrors();

        if (!empty($errors)) {
            return $errors;
        }

        Session::put('federationData', [
            'sessionIndex' => $auth->getSessionIndex(),
            'nameId' => $auth->getNameId()
        ]);

        if (!$auth->isAuthenticated()) {
            return array('error' => 'Could not authenticate');
        }

        return null;

    }

    /**
     * Process a Saml response (assertion consumer service)
     * returns an array with errors if it can not logout
     */
    function sls()
    {
        $auth = $this->auth;

        // destroy the local session by firing the Logout event
        $keep_local_session = false;
        $session_callback = function () {
            event(new Saml2LogoutEvent());
        };

        // Mickael: Changed $retrieveParametersFromServer paramter to true otherwise when the signedQuery is corrupted and the signature validation failed
        $auth->processSLO($keep_local_session, null, true, $session_callback);

        $errors = $auth->getErrors();

        return $errors;
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
