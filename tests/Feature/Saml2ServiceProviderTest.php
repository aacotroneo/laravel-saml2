<?php

namespace Feature;

use Aacotroneo\Saml2\Tests\TestCase;

class Saml2ServiceProviderTest extends TestCase
{

    /** @test */
    public function it_should_have_config()
    {
        $this->assertNotNull($this->app['config']->get('saml2_settings'));
    }

}
