# JMA OData Legacy Client

## Install package
```console
composer require jmadsm/odata-client-legacy
```

#### Laravel config publish
```console
php artisan vendor:publish --tag=tenant-config --ansi
php artisan vendor:publish --tag=tag=odata-legacy-config --ansi
```

## Usage
```php
<?php
use JmaDsm\ODataLegacy\ODataClient;

$client = ODataClient::factory([
    'api_company_id' => '',
    'api_base_url' => '',
    'api_user' => '',
    'api_password' => '',
    'api_tenant' => '',
    'api_rest_version' => ''
]);

$response = $client->get('contacts', ['query' => [
    '$filter' => "E_Mail eq 'example@example.com'",
    '$count' => 'true',
]]);

foreach(json_decode($response->getBody())->value as $object) {
    print_r($object);
}
```
### Laravel Usage
```php
<?php
use Illuminate\Support\Facades\App;
use JmaDsm\ODataLegacy\ODataClient;

$oDataClient = App::make(ODataClient::class);
```
