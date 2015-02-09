<?php

namespace Aacotroneo\Saml2;


use App;
use Mockery as m;

class Saml2AuthTest extends \PHPUnit_Framework_TestCase
{


    public function tearDown()
    {
        m::close();
    }


    public function testIsAuthenticated()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);

        $auth->shouldReceive('isAuthenticated')->andReturn('return');

        $this->assertEquals('return', $saml2->isAuthenticated());

    }

    public function testLogin()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('login')->once();
        $saml2->login();
    }

    public function testLogout()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('logout')->with()->once();
        $saml2->logout();
    }


    public function testAcsError()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processResponse')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn(array('errors'));

        $error = $saml2->acs();

        $this->assertNotEmpty($error);
    }


    public function testAcsNotAutenticated()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processResponse')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn(null);
        $auth->shouldReceive('isAuthenticated')->once()->andReturn(false);
        $error =  $saml2->acs();

        $this->assertNotEmpty($error);
    }


    public function testAcsOK()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processResponse')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn(null);
        $auth->shouldReceive('isAuthenticated')->once()->andReturn(true);

        $error =  $saml2->acs();

        $this->assertEmpty($error);
    }

    public function testSlsError()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processSLO')->once()->with(true);
        $auth->shouldReceive('getErrors')->once()->andReturn('errors');

        $error =  $saml2->sls();

        $this->assertNotEmpty($error);
    }

    public function testSlsOK()
    {
        $auth = m::mock('OneLogin_Saml2_Auth');
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processSLO')->once()->with(true);
        $auth->shouldReceive('getErrors')->once()->andReturn(null);

        $error =  $saml2->sls();

        $this->assertEmpty($error);
    }

/**
         * Cant test here. It uses Laravel dependencies (eg. config())
         */

//        $app = m::mock('Illuminate\Contracts\Foundation\Application[register,setDeferredServices]');
//
//        $s = m::mock('Aacotroneo\Saml2\Saml2ServiceProvider[publishes]', array($app));
//        $s->boot();
//        $s->shouldReceive('publishes');
//

//        $repo = m::mock('Illuminate\Foundation\ProviderRepository[createProvider,loadManifest,shouldRecompile]', array($app, m::mock('Illuminate\Filesystem\Filesystem'), array(__DIR__.'/services.json')));
//        $repo->shouldReceive('loadManifest')->once()->andReturn(array('eager' => array('foo'), 'deferred' => array('deferred'), 'providers' => array('providers'), 'when' => array()));
//        $repo->shouldReceive('shouldRecompile')->once()->andReturn(false);
//        $provider = m::mock('Illuminate\Support\ServiceProvider');
//        $repo->shouldReceive('createProvider')->once()->with('foo')->andReturn($provider);
//        $app->shouldReceive('register')->once()->with($provider);
//        $app->shouldReceive('runningInConsole')->andReturn(false);
//        $app->shouldReceive('setDeferredServices')->once()->with(array('deferred'));
//        $repo->load(array());
//        $s = new Saml2ServiceProvider();
//
//        $mock = \Mockery::mock(array('pi' => 3.1, 'e' => 2.71));
//        $this->assertEquals(3.1416, $mock->pi());
//        $this->assertEquals(2.71, $mock->e());

}
 