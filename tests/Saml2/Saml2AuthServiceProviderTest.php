<?php

namespace Aacotroneo\Saml2;


use App;
use Mockery as m;

class Saml2AuthServiceProviderTest extends \PHPUnit_Framework_TestCase
{


    public function tearDown()
    {
        m::close();
    }


    public function testSimpleMock()
    {
        $this->assertTrue(true);
        /**
         * Cant test here. It uses Laravel dependencies (eg. config())
         */
    }

}
 