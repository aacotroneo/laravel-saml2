<?php

namespace Aacotroneo\Saml2\Events;

class Saml2LoginEvent {

    protected $user;

    function __construct($user)
    {
        $this->user = $user;
    }

    public function getSaml2User()
    {
        return $this->user;
    }



}
