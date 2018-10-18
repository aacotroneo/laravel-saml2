<?php

namespace Aacotroneo\Saml2\Tests;


use Aacotroneo\Saml2\Facades\Saml2Auth;
use Aacotroneo\Saml2\Saml2ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{

    protected function getPackageProviders($app)
    {
        return [
            Saml2ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Saml2' => Saml2Auth::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('saml2_settings.useRoutes', true);
    }


}
