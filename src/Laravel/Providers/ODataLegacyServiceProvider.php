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
        $tenantDomain = $request->header('x-tenant-domain');

        $this->app->singleton(ODataClient::class, function () use ($tenantToken, $tenantDomain) {
            if ($tenantToken) {
                $tenant = (\Illuminate\Support\Facades\App::make(TenantServiceClient::class))->get($tenantToken);
            } elseif ($tenantDomain) {
                $tenant = $this->getTenantFromDomain($tenantDomain);
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

    protected function getTenantFromDomain(string $domain)
    {
        $tenants = (\Illuminate\Support\Facades\App::make(TenantServiceClient::class))->search('domain', $domain);
        if (count($tenants) > 1) {
            throw new \Exception('More than 1 tenant resolved by tenant domain: ' . var_dump($domain), 1);
        }

        return $tenants[0];
    }
}
