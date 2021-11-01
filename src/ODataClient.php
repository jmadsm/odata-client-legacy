<?php

namespace JmaDsm\ODataLegacy;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

class ODataClient extends Client
{
    protected $tenant;
    protected $odata = true;

    public function __construct(array $config, $tenant)
    {
        parent::__construct($config);

        if (is_array($tenant)) {
            $tenant = (object) $tenant;
        }
        $this->tenant = $tenant;
    }

    public static function factory($tenant, bool $verifySsl = false)
    {
        if (is_array($tenant)) {
            $tenant = (object) $tenant;
        }

        $handler = HandlerStack::create();

        $handler->push(Middleware::mapRequest(function (RequestInterface $request) use ($tenant) {
            $extraParams = ['tenant' => $tenant->api_tenant];

            $uri = $request->getUri();
            $uri .= (isset(parse_url($uri)['query']) ? '&' : '?');
            $uri .= http_build_query($extraParams);

            return new Request(
                $request->getMethod(),
                $uri,
                $request->getHeaders(),
                $request->getBody(),
                $request->getProtocolVersion()
            );
        }));

        $clientConfig = [
            'base_uri' => rtrim($tenant->api_base_url, '/') . "/api/{$tenant->api_rest_version}/companies({$tenant->api_company_id})/",
            'timeout'  => 5.0,
            'handler'  => $handler,
            'auth'     => [$tenant->api_user, $tenant->api_password],
            'curl'     => [CURLOPT_SSL_VERIFYPEER => $verifySsl],
            'debug'    => false,
            'headers'  => [
                'Accept'          => 'application/json',
                'If-Match'        => '*',
                'Accept-Language' => 'en-US',
                'OData-Version'   => '4.0',
                'Prefer'          => 'odata.continue-on-error',
            ],
        ];

        return new static($clientConfig, $tenant);
    }

    public function getBatchUri()
    {
        return rtrim($this->tenant->api_base_url, '/') . "/api/{$this->tenant->api_rest_version}/\$batch";
    }

    /**
     * Converts request data for batch preparation.
     *
     * @param  array  $body
     * @param  string $method
     * @param  string $url
     * @return array
     */
    public function batch(array $body, string $method, string $url)
    {
        $extraParams = ['tenant' => $this->tenant->api_tenant];

        $uri = $url;
        $uri .= (isset(parse_url($uri)['query']) ? '&' : '?');
        $uri .= http_build_query($extraParams);

        return array_filter([
            'method'         => $method,
            'atomicityGroup' => uniqid('', true),
            'id'             => 'id_' . uniqid('', true),
            'url'            => $uri,
            'body'           => (empty($body)) ? null : $body,
            'headers'        => [
                'Content-Type'  => 'application/json; odata.metadata=minimal; odata.streaming=true',
                'OData-Version' => '4.0',
                'If-Match'      => '*',
                'Prefer'        => 'odata.continue-on-error',
            ],
        ]);
    }
}
