<?php
namespace Aacotroneo\Saml2\Facades;

use Illuminate\Support\Facades\Facade;
use Aacotroneo\Saml2\Saml2Auth as Accessor;

class Saml2Auth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }

} 