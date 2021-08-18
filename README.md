# JMA OData Legacy Client

## Usage
### Install package
In your ```composer.json``` add this repository.
Example ```composer.json```
```json
{
    "name": "test/test",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jmadsm/odata-client-legacy"
        }
    ],
    "require": {
        ...
    }
    ...
}
```

Once this repository has been added, you can install this package by running the following command:
```sh
composer require jmadsm/odata-client-legacy:dev-main
```

### Getting data
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
