<?php

declare(strict_types=1);

return [
    'issuer' => env('OIDC_ISSUER', ''),
    'client_id' => env('OIDC_CLIENT_ID', ''),
    'client_secret' => env('OIDC_CLIENT_SECRET', ''),
    'decryption_key_path' => env('OIDC_DECRYPTION_KEY_PATH', ''),
    'additional_scopes' => explode(',', env('OIDC_ADDITIONAL_SCOPES', '')),
    'private_key_jwt' => env('OIDC_PRIVATE_KEY_PATH_FOR_JWT', ''),
];
