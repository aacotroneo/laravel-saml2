<?php

namespace Aacotroneo\Saml2\Events;

use Aacotroneo\Saml2\Saml2User;

class Saml2LoginEvent {

    /**
     * @var Saml2User
     */
    protected $user;

    function __construct(Saml2User $user)
    {
        $this->user = $user;
    }

    public function getSaml2User()
    {
        return $this->user;
    }



}
