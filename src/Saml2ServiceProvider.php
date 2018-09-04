<?php
namespace Aacotroneo\Saml2;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Utils as OneLoginUtils;

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
            OneLoginUtils::setProxyVars(true);
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

        $this->app->singleton(Auth::class, function ($app) {
            return new Auth($this->getAuthConfig());
        });

        // Should only register a single class in the Service Container probably
        $this->app->singleton(Saml2Auth::class, function ($app) {
            return new Saml2Auth($app->make(Auth::class));
        });

    }

    protected function getAuthConfig()
    {
        // TODO: We probably want to feed our wrapper class the Laravel config
        // and then do the manipulation outside of the Service Provider
        // (then we can test this behaviour as well +1)
        $config = config('saml2');

        $config['sp']['entityId'] = URL::route('saml2.metadata');
        $config['sp']['assertionConsumerService']['url'] = URL::route('saml2.acs');
        $config['sp']['singleLogoutService']['url'] = URL::route('saml2.sls');

        // Do we really want to support file:// paths like this?
        if (strpos($config['sp']['privateKey'], 'file://') === 0) {
            // OneLogin saml will format the key for us, no need to do any of this...
            $config['sp']['privateKey'] = ExtractOpenssl::privatekeyFromFile($config['sp']['privateKey']);
        }
        if (strpos($config['sp']['x509cert'], 'file://') === 0) {
            // OneLogin saml will format the cert for us, no need to do any of this...
            $config['sp']['x509cert'] = ExtractOpenssl::certFromFile($config['sp']['x509cert']);
        }
        
        if (strpos($config['idp']['x509cert'], 'file://') === 0) {
            // OneLogin saml will format the cert for us, no need to do any of this...
            $config['idp']['x509cert'] = ExtractOpenssl::certFromFile($config['idp']['x509cert']);
        }

        return $config;
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
