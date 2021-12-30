<?php

namespace JmaDsm\ODataLegacy\Laravel\Providers;

use JmaDsm\ODataLegacy\ODataClient;
use JmaDsm\TenantService\Client as TenantServiceClient;

class ODataLegacyServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bind ODataClient to container
     *
     * @return void
     */
    public function boot(\Illuminate\Http\Request $request)
    {
        $this->publishes([
            __DIR__ . '/../config/odata-legacy.php' => config_path('odata-legacy.php')
        ], 'odata-legacy-config');

        $tenantToken  = self::getTenantToken($request);

        $this->app->singleton(ODataClient::class, function () use ($tenantToken, $request) {
            if ($tenantToken) {
                $tenant = (\Illuminate\Support\Facades\App::make(TenantServiceClient::class))->get($tenantToken);
            } elseif (config('odata-legacy.resolve_tenant_from_origin') === true) {
                $origin = $request->headers->get('origin', $request->headers->get('referer'));
                if (!$origin && config('odata-legacy.exeption_without_tenant_token')) {
                    throw new \Exception("No origin to get tenant from");
                }

                $tenant = (\Illuminate\Support\Facades\App::make(TenantServiceClient::class))->getByDomain(parse_url($origin)['host']);
            } else {
                if (config('odata-legacy.exeption_without_tenant_token')) {
                    throw new \Exception('no_tenant_token', 1);
                }

                return null;
            }

            return ODataClient::factory($tenant, config('odata-legacy.verify_ssl'));
        });
    }

    /**
     * Get the services provided by the provider
     *
     * @return array
     */
    public function provides()
    {
        return [ODataClient::class];
    }

    /**
     * Get the tenant token
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public static function getTenantToken(\Illuminate\Http\Request $request)
    {
        return  $request->header(
            'x-tenant-token',
            $request->input(
                'tenant_token',
                $request->input('tenant-token')
            )
        );
    }
}
