<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use OneLogin\Saml2\Auth as OneLogin_Saml2_Auth;
use URL;

class Saml2Controller extends Controller
{

    protected $saml2Auth;

    protected $idp;

    /**
     * @param Saml2Auth $saml2Auth injected.
     */
    function __construct(Request $request){

        // IdP name is *2nd-to-last* item in path, whether no routesPrefix ("idpName/page") or using routesPrefix ("routesPrefix/idpName/page")
        $pathSegments = $request->segments();
        $this->idp = $pathSegments[count($pathSegments)-2];
        if (!$this->idp) {
            $this->idp = 'test';
        }

        $config = config('saml2.'.$this->idp.'_idp_settings');

        if (empty($config['sp']['entityId'])) {
            $config['sp']['entityId'] = URL::route($this->idp.'_metadata');
        }
        if (empty($config['sp']['assertionConsumerService']['url'])) {
            $config['sp']['assertionConsumerService']['url'] = URL::route($this->idp.'_acs');
        }
        if (!empty($config['sp']['singleLogoutService']) &&
            empty($config['sp']['singleLogoutService']['url'])) {
            $config['sp']['singleLogoutService']['url'] = URL::route($this->idp.'_sls');
        }
        if (strpos($config['sp']['privateKey'], 'file://')===0) {
            $config['sp']['privateKey'] = $this->extractPkeyFromFile($config['sp']['privateKey']);
        }
        if (strpos($config['sp']['x509cert'], 'file://')===0) {
            $config['sp']['x509cert'] = $this->extractCertFromFile($config['sp']['x509cert']);
        }
        if (strpos($config['idp']['x509cert'], 'file://')===0) {
            $config['idp']['x509cert'] = $this->extractCertFromFile($config['idp']['x509cert']);
        }

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
     * Fires 'Saml2LoginEvent' event if a valid user is Found
     */
    public function acs()
    {
        $errors = $this->saml2Auth->acs();

        if (!empty($errors)) {
            logger()->error('Saml2 error_detail', ['error' => $this->saml2Auth->getLastErrorReason()]);
            session()->flash('saml2_error_detail', [$this->saml2Auth->getLastErrorReason()]);

            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);
            return redirect(config('saml2_settings.errorRoute'));
        }
        $user = $this->saml2Auth->getSaml2User();

        event(new Saml2LoginEvent($this->idp, $user, $this->saml2Auth));

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl !== null) {
            return redirect($redirectUrl);
        } else {

            return redirect(config('saml2_settings.loginRoute'));
        }
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'Saml2LogoutEvent' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     */
    public function sls()
    {
        $errors = $this->saml2Auth->sls($this->idp, config('saml2_settings.retrieveParametersFromServer'));
        if (!empty($errors)) {
            logger()->error('Saml2 error', $errors);
            session()->flash('saml2_error', $errors);
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

    protected function extractPkeyFromFile($path) {
        $res = openssl_get_privatekey($path);
        if (empty($res)) {
            throw new \Exception('Could not read private key-file at path \'' . $path . '\'');
        }
        openssl_pkey_export($res, $pkey);
        openssl_pkey_free($res);
        return $this->extractOpensslString($pkey, 'PRIVATE KEY');
    }

    protected function extractCertFromFile($path) {
        $res = openssl_x509_read(file_get_contents($path));
        if (empty($res)) {
            throw new \Exception('Could not read X509 certificate-file at path \'' . $path . '\'');
        }
        openssl_x509_export($res, $cert);
        openssl_x509_free($res);
        return $this->extractOpensslString($cert, 'CERTIFICATE');
    }

    protected function extractOpensslString($keyString, $delimiter) {
        $keyString = str_replace(["\r", "\n"], "", $keyString);
        $regex = '/-{5}BEGIN(?:\s|\w)+' . $delimiter . '-{5}\s*(.+?)\s*-{5}END(?:\s|\w)+' . $delimiter . '-{5}/m';
        preg_match($regex, $keyString, $matches);
        return empty($matches[1]) ? '' : $matches[1];
    }
}
