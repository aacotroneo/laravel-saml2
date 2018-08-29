<?php

// TODO: this seems questionable at best...
$idp_host = env('SAML2_IDP_HOST');

return [
    /**
     * Indicates how the parameters will be
     * retrieved from the sls request for signature validation
     */
    'retrieveParametersFromServer' => false,

    /**
     * Where to redirect after logout
     */
    'logoutRoute' => '/',

    /**
     * Where to redirect after login if no other option was provided
     */
    'loginRoute' => '/',

    /**
     * Where to redirect after login if no other option was provided
     */
    'errorRoute' => '/',

    /**
     *
     *  OneLogin settings
     *
     */

    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Destination, NameId, Conditions ... are validated too.
    'strict' => true, //@todo: make this depend on laravel config

    // Enable debug mode (to print errors)
    'debug' => env('APP_DEBUG', false),

    // If 'proxyVars' is True, then the Saml lib will trust proxy headers
    // e.g X-Forwarded-Proto / HTTP_X_FORWARDED_PROTO. This is useful if
    // your application is running behind a load balancer which terminates
    // SSL.
    'proxyVars' => false,

    // Service Provider Data that we are deploying
    'sp' => [
        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => env('SAML2_SP_x509'),

        'privateKey' => env('SAML2_SP_PRIVATEKEY'),
    ],

    // Identity Provider Data that we want connect with our SP
    'idp' => [
        // Identifier of the IdP entity  (must be a URI)
        'entityId' => env('SAML2_IDP_ENTITYID', $idp_host . '/saml2/idp/metadata.php'),
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => [
            // URL Target of the IdP where the SP will send the Authentication Request Message,
            // using HTTP-Redirect binding.
            'url' => env('SAML2_IDP_SSO', $idp_host . '/saml2/idp/SSOService.php'),
        ],
        // SLO endpoint info of the IdP.
        'singleLogoutService' => [
            // URL Location of the IdP where the SP will send the SLO Request,
            // using HTTP-Redirect binding.
            'url' => env('SAML2_IDP_SLO', $idp_host . '/saml2/idp/SingleLogoutService.php'),
        ],
        // Public x509 certificate of the IdP
        'x509cert' => env('SAML2_IDP_x509'),
    ],

    /**
     *
     *  OneLogin advanced settings
     *
     */
    'security' => [
        /** signatures and encryptions offered */

        // Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
        // will be encrypted.
        'nameIdEncrypted' => false,

        // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
        // will be signed.              [The Metadata of the SP will offer this info]
        'authnRequestsSigned' => false,

        // Indicates whether the <samlp:logoutRequest> messages sent by this SP
        // will be signed.
        'logoutRequestSigned' => false,

        // Indicates whether the <samlp:logoutResponse> messages sent by this SP
        // will be signed.
        'logoutResponseSigned' => false,

        /* Sign the Metadata
         False || True (use sp certs) || array (
                                                    keyFileName => 'metadata.key',
                                                    certFileName => 'metadata.crt'
                                                )
        */
        'signMetadata' => false,

        /** signatures and encryptions required **/

        // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        // <samlp:LogoutResponse> elements received by this SP to be signed.
        'wantMessagesSigned' => false,

        // Indicates a requirement for the <saml:Assertion> elements received by
        // this SP to be signed.        [The Metadata of the SP will offer this info]
        'wantAssertionsSigned' => false,

        // Indicates a requirement for the NameID received by
        // this SP to be encrypted.
        'wantNameIdEncrypted' => false,

        // Authentication context.
        // Set to false and no AuthContext will be sent in the AuthNRequest,
        // Set true or don't present thi parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        // Set an array with the possible auth context values: array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509'),
        'requestedAuthnContext' => true,
    ],

    // Contact information template, it is recommended to suply a technical and support contacts
    'contactPerson' => [
        'technical' => [
            'givenName' => 'name',
            'emailAddress' => 'no@reply.com',
        ],
        'support' => [
            'givenName' => 'Support',
            'emailAddress' => 'no@reply.com',
        ],
    ],

    // Organization information template, the info in en_US lang is recomended, add more if required
    'organization' => [
        'en-US' => [
            'name' => 'Name',
            'displayname' => 'Display Name',
            'url' => 'http://url',
        ],
    ],
];
