<?php

namespace Aacotroneo\Saml2\Controllers;

use Event;
use Saml2Auth;
use Controller;
use Response;


class Saml2Controller extends Controller
{

    /**
     * Generate local sp metadata
     * @return \Illuminate\Http\Response
     */
    public function metadata()
    {

        $metadata = Saml2Auth::getMetadata();
        $response = Response::make($metadata, 200);

        $response->header('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * Process an incoming saml2 assertion request.
     * Fires 'saml2.loginRequestReceived' event if a valid user is Found
     */
    public function acs()
    {
        Saml2Auth::acs();
        $user = Saml2Auth::getSaml2User();
        Event::fire('saml2.loginRequestReceived', array($user));
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     */
    public function sls()
    {
        Saml2Auth::sls();
        Event::fire('saml2.logoutRequestReceived');
    }

    /**
     * This initiates a logout request across all the SSO infrastructure.
     */
    public function logout()
    {
        Saml2Auth::logout();  //will actually end up in the sls endpoint
    }

}
