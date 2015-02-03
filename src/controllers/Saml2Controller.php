<?php

namespace Aacotroneo\Saml2\Controllers;

use Saml2Auth;
use Controller;
use Response;


class Saml2Controller extends Controller {


    public function metadata(){

        $metadata = Saml2Auth::getMetadata();
        $response = Response::make($metadata, 200);

        $response->header('Content-Type', 'text/xml');

        return $response;
    }

    public function acs(){
        return "acs";
    }


    public function sls(){
        return "sls";
    }
}
