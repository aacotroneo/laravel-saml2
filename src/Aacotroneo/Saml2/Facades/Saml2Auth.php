<?php
namespace Aacotroneo\Saml2\Facades;

use Illuminate\Support\Facades\Facade;

class Saml2Auth extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Aacotroneo\Saml2\Saml2Auth';
    }

} 