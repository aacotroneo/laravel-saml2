<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class Saml2Controller extends Controller
{
    /**
     * Saml2Auth implementation.
     *
     * @var \Aacotroneo\Saml2\Saml2Auth
     */
    protected $auth;

    /**
     * Create a new controller instance.
     *
     * @param  \Aacotroneo\Saml2\Saml2Auth  $auth
     * @return void
     */
    function __construct(Saml2Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * This initiates a login request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        $this->auth->login(config('saml2.loginRoute'));

        // TODO: create a laravel redirect
        // return redirect()->away($loginUrl);
    }
    
    /**
     * This initiates a logout request across all the SSO infrastructure.
     * 
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $returnTo = $request->query('returnTo');
        $sessionIndex = $request->query('sessionIndex');
        $nameId = $request->query('nameId');
        // the logout request should end up at the sls route
        $this->auth->logout($returnTo, $nameId, $sessionIndex);
        
        // TODO: create a laravel redirect
        // return redirect()->away($logoutUrl);
    }

    /**
     * Generate local sp metadata
     * 
     * @return \Illuminate\Http\Response
     */
    public function metadata()
    {
        $metadata = $this->auth->getMetadata();

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process an incoming saml2 assertion request.
     * Fires 'Saml2LoginEvent' event if a valid user is Found
     * 
     * @return \Illuminate\Http\Response
     */
    public function acs()
    {
        $errors = $this->auth->acs();
        if (!empty($errors)) {
            logger()->error('Saml2 error_detail', ['error' => $this->auth->getLastErrorReason()]);
            session()->flash('saml2_error_detail', [$this->auth->getLastErrorReason()]);

            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);

            return redirect(config('saml2.errorRoute'));
        }

        $user = $this->auth->getSaml2User();

        event(new Saml2LoginEvent($user, $this->auth));

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl !== null) {
            return redirect($redirectUrl);
        }
        
        return redirect(config('saml2.loginRoute'));
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     * 
     * @return \Illuminate\Http\Response
     */
    public function sls()
    {
        $error = $this->auth->sls(config('saml2.retrieveParametersFromServer'));
        if (!empty($error)) {
            throw new \Exception("Could not log out");
        }

        return redirect(config('saml2.logoutRoute')); //may be set a configurable default
    }
}
