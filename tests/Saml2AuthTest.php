<?php

namespace Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Aacotroneo\Saml2\Saml2Auth;

class Saml2AuthTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testIsAuthenticated()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);

        $auth->shouldReceive('isAuthenticated')->andReturn('return');

        $this->assertEquals('return', $saml2->isAuthenticated());
    }

    public function testLogin()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);

        $auth->shouldReceive('login')->once();
        $saml2->login();

        // TODO: better assertion...
        $this->assertTrue(true);
    }

    public function testLogout()
    {
        $expectedReturnTo = 'http://localhost';
        $expectedSessionIndex = 'session_index_value';
        $expectedNameId = 'name_id_value';
        $expectedNameIdFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';

        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);

        $auth->shouldReceive('logout')
            ->with($expectedReturnTo, [], $expectedNameId, $expectedSessionIndex, false, $expectedNameIdFormat)
            ->once();
        $saml2->logout($expectedReturnTo, $expectedNameId, $expectedSessionIndex, $expectedNameIdFormat);
        
        // TODO: better assertion...
        $this->assertTrue(true);
    }

    public function testAcsError()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processResponse')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn(['errors']);

        $error = $saml2->acs();

        $this->assertNotEmpty($error);
    }

    public function testAcsNotAutenticated()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processResponse')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn(null);
        $auth->shouldReceive('isAuthenticated')->once()->andReturn(false);
        $error =  $saml2->acs();

        $this->assertNotEmpty($error);
    }

    public function testAcsOK()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processResponse')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn(null);
        $auth->shouldReceive('isAuthenticated')->once()->andReturn(true);

        $error =  $saml2->acs();

        $this->assertEmpty($error);
    }

    public function testSlsError()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processSLO')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn('errors');

        $error =  $saml2->sls();

        $this->assertNotEmpty($error);
    }

    public function testSlsOK()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);
        $auth->shouldReceive('processSLO')->once();
        $auth->shouldReceive('getErrors')->once()->andReturn(null);

        $error =  $saml2->sls();

        $this->assertEmpty($error);
    }

    public function testCanGetLastError()
    {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);

        $auth->shouldReceive('getLastErrorReason')->andReturn('lastError');

        $this->assertSame('lastError', $saml2->getLastErrorReason());
    }

    public function testGetUserAttribute() {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);

        $user = $saml2->getSaml2User();

        $auth->shouldReceive('getAttribute')
            ->with('urn:oid:0.9.2342.19200300.100.1.3')
            ->andReturn(['test@example.com']);

        $this->assertEquals(['test@example.com'], $user->getAttribute('urn:oid:0.9.2342.19200300.100.1.3'));
    }

    public function testParseSingleUserAttribute() {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);

        $user = $saml2->getSaml2User();

        $auth->shouldReceive('getAttribute')
            ->with('urn:oid:0.9.2342.19200300.100.1.3')
            ->andReturn(['test@example.com']);

        $user->parseUserAttribute('urn:oid:0.9.2342.19200300.100.1.3', 'email');

        $this->assertEquals($user->email, ['test@example.com']);
    }

    public function testParseMultipleUserAttributes() {
        $auth = m::mock(\OneLogin\Saml2\Auth::class);
        $saml2 = new Saml2Auth($auth);

        $user = $saml2->getSaml2User();

        $auth->shouldReceive('getAttribute')
            ->twice()
            ->andReturn(['test@example.com'], ['Test User']);

        $user->parseAttributes([
            'email' => 'urn:oid:0.9.2342.19200300.100.1.3',
            'displayName' => 'urn:oid:2.16.840.1.113730.3.1.241'
        ]);

        $this->assertEquals($user->email, ['test@example.com']);
        $this->assertEquals($user->displayName, ['Test User']);
    }

/**
         * Cant test here. It uses Laravel dependencies (eg. config())
         */

//        $app = m::mock('Illuminate\Contracts\Foundation\Application[register,setDeferredServices]');
//
//        $s = m::mock('Aacotroneo\Saml2\Saml2ServiceProvider[publishes]', [$app]);
//        $s->boot();
//        $s->shouldReceive('publishes');
//

//        $repo = m::mock('Illuminate\Foundation\ProviderRepository[createProvider,loadManifest,shouldRecompile]', [$app, m::mock('Illuminate\Filesystem\Filesystem'], [__DIR__.'/services.json']));
//        $repo->shouldReceive('loadManifest')->once()->andReturn(['eager' => ['foo'], 'deferred' => ['deferred'], 'providers' => ['providers'], 'when' => []]);
//        $repo->shouldReceive('shouldRecompile')->once()->andReturn(false);
//        $provider = m::mock('Illuminate\Support\ServiceProvider');
//        $repo->shouldReceive('createProvider')->once()->with('foo')->andReturn($provider);
//        $app->shouldReceive('register')->once()->with($provider);
//        $app->shouldReceive('runningInConsole')->andReturn(false);
//        $app->shouldReceive('setDeferredServices')->once()->with(['deferred']);
//        $repo->load([]);
//        $s = new Saml2ServiceProvider();
//
//        $mock = \Mockery::mock(['pi' => 3.1, 'e' => 2.71]);
//        $this->assertEquals(3.1416, $mock->pi());
//        $this->assertEquals(2.71, $mock->e());

}
