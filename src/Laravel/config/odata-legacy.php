<?php

return [
    'verify_ssl'                    => env('ODATA_VERIFY_SSL', true),
    'exeption_without_tenant_token' => env('ODATA_EXCEPTION_NO_TENANT_TOKEN', true),
    'resolve_tenant_from_origin'    => env('ODATA_LEGACY_FROM_ORIGIN', false)
];
