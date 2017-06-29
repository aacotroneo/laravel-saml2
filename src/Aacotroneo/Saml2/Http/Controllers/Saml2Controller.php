<?php

namespace Aacotroneo\Saml2\Http\Controllers;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Aacotroneo\Saml2\Saml2Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use HL7;
use App\Models\User;


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
        event(new Saml2LoginEvent($user));

        $message = $this->getHL7MessageFromRequest( $user );
        $requestQueryParams = '';
        if( !empty($message) )
        {
            $appUser = $this->getUserFromRequest( $user );
            $request = $this->createRequest( $message, $appUser );
            $requestQueryParams = $this->getUrlParamString( $request );
        }

        $redirectUrl = $user->getIntendedUrl();

        if ($redirectUrl === null) {
            $redirectUrl = config('saml2_settings.loginRoute');
        }

        print_r( $redirectUrl . $requestQueryParams );
        exit();

        return redirect($redirectUrl . $requestQueryParams);
    }

    /**
     * Process an incoming saml2 logout request.
     * Fires 'saml2.logoutRequestReceived' event if its valid.
     * This means the user logged out of the SSO infrastructure, you 'should' log him out locally too.
     */
    public function sls()
    {
        $error = $this->saml2Auth->sls(config('saml2_settings.retrieveParametersFromServer'));
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

    /**
     * Gets the HL7 message from the user request
     *
     * @param   Saml2User     $user
     * @return  String       
     */
    private function getHL7MessageFromRequest( $user )
    {
        $attributes = $user->getAttributes();

        if( !array_key_exists('RequestMessage', $attributes) || sizeof($attributes['RequestMessage']) === 0 )
        {
            return null;
        }

        return $attributes['RequestMessage'][0];
    }

    /**
     * Get the user model using the email on request
     *
     * @param   Saml2User   $user
     * @return  User
     */
    private function getUserFromRequest( $samlUser )
    {
        $email = $samlUser->getAttributes()['Email'][0];

        // Find a node with the attribute Name set as Email, after find the text node that contains the email
        $user = User::whereUsername( $email )->first();
        if ( is_null($user) )
        {
            throw new Saml2UserNotPresentException( "Saml request with username {$username} does not have an user on AristaMD" );
        }
        return $user;
    }

    /**
     * Get the query params for the request object
     *
     * @param   Request   $request
     * @return  String
     */
    public function getUrlParamString( $request )
    {
        if( empty($request) || empty($request->id) )
        {
            return "";
        }

        $recordType = null;

        switch ( get_class($request) )
        {
            case 'App\Models\EConsult':
                $recordType = 'econsult';
                break;
            case 'App\Models\Referral':
                $recordType = 'referral';
                break;
            default:
                $recordType = null;
                break;
        }

        return "&record_type=$recordType&record_id=$request->id";
    }

    /**
     * Creates a referral or an eConsult from a HL7 message
     *
     * @param   String  $message    HL7 message as string
     * @param   User    $user       Authenticated user
     * @return  Integer             Request id
     */
    private function createRequest( $message, $user )
    {
        if ( empty($message) )
        {
            /*$this->responseCode( 500 );
            $this->message = "Internal Error. Please provide the HL7 v.2.3 REF message.";
            return $this->buildResponse( );*/
        }
        // Getting the patient data from the HL7 message
        $patient = HL7::transformPatient( $user->organization->id, $message );
        $requestObject = HL7::transformRequest( $user->id, $user->organization->id, $message );

        $patient->save();

        $requestObject->patient_id = $patient->id;
        $requestObject->patient_version = $patient->version;

        $requestObject->save();

        return $requestObject;
    }
}
