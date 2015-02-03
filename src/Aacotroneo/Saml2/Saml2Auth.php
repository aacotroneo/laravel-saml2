<?php

namespace Aacotroneo\Saml2;

use OneLogin_Saml2_Auth;
use OneLogin_Saml2_Error;
use OneLogin_Saml2_Utils;

class Saml2Auth
{

    /**
     * @var \OneLogin_Saml2_Auth
     */
    protected $auth;
    protected $uid_key;

    function __construct($config)
    {
//        session_start();
        $this->auth = new OneLogin_Saml2_Auth($config);
//        $this->uid_key = $uid_key;
//        $this->id_key = $config[]
    }

    function isAuthenticated()
    {
        return isset($_SESSION['samlUserdata']);
    }

    function getUserId()
    {
        $attributes = $this->getAttributes();
        return $attributes[$this->uid_key][0];
    }

    function getAttributes()
    {
        $attributes = $_SESSION['samlUserdata'];
        return $attributes;
    }

    function getRawSamlAssertion()
    {
        return isset($_SESSION['SAMLAssertion']) ? $_SESSION['SAMLAssertion'] : null;
    }

    function login()
    {
        $this->auth->login();
    }

    function acs()
    {

        /** @var $auth OneLogin_Saml2_Auth */
        $auth = $this->auth;

        $auth->processResponse();


        $errors = $auth->getErrors();

        if (!empty($errors)) {
            print_r('<p>' . implode(', ', $errors) . '</p>');
            exit();
        }

        if (!$auth->isAuthenticated()) {
            echo "<p>Not authenticated</p>";
            exit();
        }

        $_SESSION['samlUserdata'] = $auth->getAttributes();

        $_SESSION['SAMLAssertion'] = $_POST['SAMLResponse']; //se lo robo al saml

        if (isset($_POST['RelayState']) && OneLogin_Saml2_Utils::getSelfURL() != $_POST['RelayState']) {
            $auth->redirectTo($_POST['RelayState']);
        }
    }

    function sls()
    {
        $auth = $this->auth;

        $auth->processSLO();

        $errors = $auth->getErrors();

        if (empty($errors)) {
            print_r('Sucessfully logged out');
        } else {
            print_r(implode(', ', $errors));
        }
    }

    function getMetadata()
    {
        $auth = $this->auth;
        $settings = $auth->getSettings();
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);


        if (empty($errors)) {
            return $metadata;
//            header('Content-Type: text/xml');
//            echo $metadata;
        } else {

            throw new OneLogin_Saml2_Error(
                'Invalid SP metadata: ' . implode(', ', $errors),
                OneLogin_Saml2_Error::METADATA_SP_INVALID
            );
        }
    }


} 