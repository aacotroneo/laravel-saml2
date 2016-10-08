<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use OneLogin_Saml2_Auth;
use URL;

class Saml2Controller extends Controller
{

    protected $saml2Auth;

    protected $idp;

    /**
     * @param Saml2Auth $saml2Auth injected.
     */
    function __construct(Request $request){

        $this->idp = explode('/',$request->path())[0];
        if (!$this->idp) {
            $this->idp = 'test';
        }

        $config = config('saml2.'.$this->idp.'_idp_settings');

        $config['sp']['entityId'] = URL::route($this->idp.'_metadata');

        $config['sp']['assertionConsumerService']['url'] = URL::route($this->idp.'_acs');

        $config['sp']['singleLogoutService']['url'] = URL::route($this->idp.'_sls');

        $auth = new OneLogin_Saml2_Auth($config);

        $this->saml2Auth = new Saml2Auth($auth);
    }

    /**
     * Generate local sp metadata
     * @return \Illuminate\Http\Response
     */
    public function metadata()
    {

        $metadata = $this->saml2Auth->getMetadata();

        return response($metadata, 200, ['Content-Type' => 'text/xml']);
    }

    /**
     * Process an incoming saml2 assertion request.
     * Fires 'saml2.loginRequestReceived' event if a valid user is Found
     */
    public function acs()
    {
        $errors = $this->saml2Auth->acs();

        if (!empty($errors)) {
            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);
            return redirect(config('saml2_settings.errorRoute'));
        }
        $user = $this->saml2Auth->getSaml2User();

        event(new Saml2LoginEvent($this->idp, $user));

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl !== null) {
            return redirect($redirectUrl);
        } else {

            return redirect(config('saml2_settings.loginRoute'));
        }
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     */
    public function sls()
    {
        $error = $this->saml2Auth->sls($this->idp, config('saml2_settings.retrieveParametersFromServer'));
        if (!empty($error)) {
            throw new \Exception("Could not log out");
        }

        return redirect(config('saml2_settings.logoutRoute')); //may be set a configurable default
    }

    /**
     * This initiates a logout request across all the SSO infrastructure.
     */
    public function logout(Request $request)
    {
        $returnTo = $request->query('returnTo');
        $sessionIndex = $request->query('sessionIndex');
        $nameId = $request->query('nameId');
        $this->saml2Auth->logout($returnTo, $nameId, $sessionIndex); //will actually end up in the sls endpoint
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
