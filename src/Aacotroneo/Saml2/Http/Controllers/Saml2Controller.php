<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Events\Saml2LogoutEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Routing\Controller;
use Config;
use Event;
use Log;
use Redirect;
use Response;
use Session;


class Saml2Controller extends Controller
{

    protected $saml2Auth;

    /**
     * @param Saml2Auth $saml2Auth injected.
     */
    function __construct(Saml2Auth $saml2Auth)
    {
        $this->saml2Auth = $saml2Auth;
    }


    /**
     * Generate local sp metadata
     * @return \Illuminate\Http\Response
     */
    public function metadata()
    {

        $metadata = $this->saml2Auth->getMetadata();
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
        $errors = $this->saml2Auth->acs();

        if (!empty($errors)) {
            Log::error('Saml2 error', $errors);
            Session::flash('saml2_error', $errors);
            return Redirect::to(config('saml2_settings.errorRoute'));
        }
        $user = $this->saml2Auth->getSaml2User();

        Event::fire(new Saml2LoginEvent($user));

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl !== null) {
            return Redirect::to($redirectUrl);
        } else {

            return Redirect::to(config('saml2_settings.loginRoute')); 
        }
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     */
    public function sls()
    {
        $error = $this->saml2Auth->sls();
        if (!empty($error)) {
            throw new \Exception("Could not log out");
        }

        Event::fire(new Saml2LogoutEvent());
        return Redirect::to(Config::get('saml2::settings.logoutRoute')); //may be set a configurable default
    }

    /**
     * This initiates a logout request across all the SSO infrastructure.
     */
    public function logout()
    {
        $this->saml2Auth->logout(); //will actually end up in the sls endpoint
        //does not return
    }


    /**
     * This initiates a login request
     */
    public function login()
    {
        $this->saml2Auth->login(config('saml2_settings.loginRoute'));
    }

}
