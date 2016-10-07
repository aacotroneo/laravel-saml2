<?php

namespace Aacotroneo\Saml2\Events;

class Saml2Event {

    protected $idp;

    function __construct($idp)
    {
        $this->idp = $idp;
    }

    public function getSaml2Idp()
    {
        return $this->idp;
    }

}
