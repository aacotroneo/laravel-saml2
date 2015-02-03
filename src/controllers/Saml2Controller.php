<?php

namespace Aacotroneo\Saml2;


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

class Saml2Controller extends Controller {


    public function metadata(){

        $config = Config::get('saml2::saml_settings');


        return print_r($config, true);
    }

    public function acs(){
        return "acs";
    }


    public function sls(){
        return "sls";
    }
}
