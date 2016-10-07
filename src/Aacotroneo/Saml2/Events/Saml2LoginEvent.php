<?php

namespace Aacotroneo\Saml2\Events;

class Saml2LoginEvent extends Saml2Event {

    protected $user;

    function __construct($idp, $user)
    {
        parent::__construct($idp);
        $this->user = $user;
    }

    public function getSaml2User()
    {
        return $this->user;
    }

}
