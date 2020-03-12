<?php

namespace Aacotroneo\Saml2\Events;

use Aacotroneo\Saml2\Saml2User;
use Aacotroneo\Saml2\Saml2Auth;

class Saml2LoginEvent extends Saml2Event
{

    protected $user;
    protected $auth;
    protected $redirect_url = null;

    function __construct($idp, Saml2User $user, Saml2Auth $auth)
    {
        parent::__construct($idp);
        $this->user = $user;
        $this->auth = $auth;
    }

    public function getSaml2User()
    {
        return $this->user;
    }

    public function getSaml2Auth()
    {
        return $this->auth;
    }

    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    public function setRedirectUrl($url)
    {
        $this->redirect_url = $url;
    }
}
