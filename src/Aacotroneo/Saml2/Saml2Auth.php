<?php

namespace Aacotroneo\Saml2;

use OneLogin_Saml2_Auth;
use OneLogin_Saml2_Error;

use Log;
use InvalidArgumentException;

class Saml2Auth
{

    /**
     * @var \OneLogin_Saml2_Auth
     */
    protected $auth;

    function __construct(OneLogin_Saml2_Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return bool if a valid user was fetched from the saml assertion this request.
     */
    public function isAuthenticated()
    {
        $auth = $this->auth;

        return $auth->isAuthenticated();
    }

    /**
     * The user info from the assertion
     *
     * @return Saml2User
     */
    public function getSaml2User()
    {
        return new Saml2User($this->auth);
    }

    /**
     * Initiate a saml2 login flow. It will redirect! Before calling this, check if user is
     * authenticated (here in saml2). That would be true when the assertion was received this request.
     *
     * @param string $returnTo page return to
     */
    public function login($returnTo = null)
    {
        $auth = $this->auth;

        $auth->login($returnTo);
    }

    /**
     * Initiate a saml2 logout flow. It will close session on all other SSO services.
     * You should close local session if applicable.
     */
    public function logout()
    {
        $auth = $this->auth;

        $auth->logout();
    }

    /**
     * Process a Saml response (assertion consumer service)
     * When errors are encountered, it returns an array with proper description
     *
     * @return array|null return array with proper error description.
     */
    public function acs()
    {
        $auth = $this->auth;

        $auth->processResponse();

        $errors = $auth->getErrors();

        if (!empty($errors)) {
            return $errors;
        }

        if (!$auth->isAuthenticated()) {
            return array('error' => 'Could not authenticate');
        }

        return null;

    }

    /**
     * Process a Saml response (assertion consumer service)
     *
     * @return array array with errors if it cannot logout.
     */
    public function sls()
    {
        $auth = $this->auth;

        $keep_local_session = true; //we don't touch session here
        $auth->processSLO($keep_local_session);

        $errors = $auth->getErrors();

        return $errors;
    }

    /**
     * Show metadata about the local sp. Use this to configure your saml2 IDP
     * @return string xml string representing metadata
     * @throws \InvalidArgumentException if metadata is not correctly set
     */
    public function getMetadata()
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