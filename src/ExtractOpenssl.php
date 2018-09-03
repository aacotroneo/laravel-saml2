<?php

namespace Aacotroneo\Saml2;

use Illuminate\Support\Str;

class ExtractOpenssl
{
    private const CERTIFICATE = 'CERTIFICATE';
    private const PRIVATE_KEY = 'PRIVATE KEY';

    /**
     * Get the certificate string from a file path.
     * 
     * @todo this is re-exporting the cert, how does this benefit us?
     * 
     * @return string
     */
    public static function certFromFile($path) {
        // 
        $res = openssl_x509_read(file_get_contents($path));
        if (empty($res)) {
            throw new \Exception('Could not read X509 certificate-file at path \'' . $path . '\'');
        }
        openssl_x509_export($res, $cert);
        openssl_x509_free($res);
        return static::stripTypeFromString(static::CERTIFICATE, $cert);
    }
    
    /**
     * Get the privatekey string from a file path.
     * Note that you need a working openssl.cnf for this to work.
     * 
     * @todo this is re-exporting the privatekey, how does this benefit us?
     *
     * @return string
     */
    public static function privatekeyFromFile($path) {
        $res = openssl_pkey_get_private(file_get_contents($path));
        if (empty($res)) {
            throw new \Exception('Could not read private key-file at path \'' . $path . '\'');
        }
        openssl_pkey_export($res, $pkey);
        openssl_pkey_free($res);
        return static::stripTypeFromString(static::PRIVATE_KEY, $pkey);
    }

    /**
     * Strip leading openssl identifiers.
     *
     * @todo  OneLogin php-saml already takes care of stripping these
     * 
     * @return string
     */
    protected static function stripTypeFromString($type, $keyString) {
        $keyString = str_replace(["\r", "\n"], "", $keyString);

        // Private keys can also start and end with RSA so we strip it as well
        if ($type === static::PRIVATE_KEY && Str::startsWith($keyString, "-----BEGIN RSA")) {
            // This is kind of an ugly hack...
            $keyString = Str::replaceFirst('RSA', '', $keyString);
            $keyString = Str::replaceLast('RSA', '', $keyString);
        }

        $keyString = Str::after($keyString, "-----BEGIN {$type}-----");
        $keyString = Str::before($keyString, "-----END {$type}-----");

        // TODO: check we actually are left with some sort of key?
        return $keyString;
    }
}
