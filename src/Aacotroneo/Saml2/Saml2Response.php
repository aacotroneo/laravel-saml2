<?php
/**
 * Created by PhpStorm.
 * User: vedant
 * Date: 21/12/15
 * Time: 3:49 PM
 */

class Saml2Response
{
    protected $response;

    function __construct()
    {
        $settingsfile=config('saml2_settings');
        $settings=new \OneLogin_Saml2_Settings($settingsfile);
        $this->response = new \OneLogin_Saml2_Response($settings, $_POST['SAMLResponse']);
    }


    function getSessionIndex()
    {
        return $this->response->getSessionIndex();
    }


}