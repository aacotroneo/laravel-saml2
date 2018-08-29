<?php
namespace Aacotroneo\Saml2;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils;

class Saml2ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();

        if (config('saml2.proxyVars', false)) {
            // TODO: what is this for?
            Utils::setProxyVars(true);
        }
    }

    /**
     * Register the Saml2 routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'prefix' => config('saml2.uri', 'saml2'),
            'namespace' => 'Aacotroneo\Saml2\Http\Controllers',
            'middleware' => config('saml2.middleware', 'web'),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->offerPublishing();

        $this->registerOneLoginInContainer();

        $this->app->singleton(Saml2Auth::class, function ($app) {
            // TODO: this layering of dependencies is probably not desirable
            return new Saml2Auth($app->make(Auth::class));
        });

    }

    protected function registerOneLoginInContainer()
    {
        // TODO: this makes me very sad
        $this->app->singleton(Auth::class, function ($app) {
            $config = config('saml2');
            if (empty($config['sp']['entityId'])) {
                $config['sp']['entityId'] = URL::route('saml2.metadata');
            }
            if (empty($config['sp']['assertionConsumerService']['url'])) {
                $config['sp']['assertionConsumerService']['url'] = URL::route('saml2.acs');
            }
            if (!empty($config['sp']['singleLogoutService']) &&
                empty($config['sp']['singleLogoutService']['url'])) {
                $config['sp']['singleLogoutService']['url'] = URL::route('saml2.sls');
            }
            if (strpos($config['sp']['privateKey'], 'file://') === 0) {
                $config['sp']['privateKey'] = $this->extractPkeyFromFile($config['sp']['privateKey']);
            }
            if (strpos($config['sp']['x509cert'], 'file://') === 0) {
                $config['sp']['x509cert'] = $this->extractCertFromFile($config['sp']['x509cert']);
            }
            if (strpos($config['idp']['x509cert'], 'file://') === 0) {
                $config['idp']['x509cert'] = $this->extractCertFromFile($config['idp']['x509cert']);
            }

            return new Auth($config);
        });
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

    /**
     * Setup the resource publishing groups for Saml2.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/saml2.php' => config_path('saml2.php'),
            ], 'saml2-config');
        }
    }
}
