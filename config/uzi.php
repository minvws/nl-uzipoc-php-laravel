<?php

declare(strict_types=1);

return [
    'issuer' => env('UZI_ISSUER', ''),
    'client_id' => env('UZI_CLIENT_ID', ''),
    'redirect_uri' => env('UZI_REDIRECT_URI', ''),
    'decryption_key' => env('UZI_DECRYPTION_KEY', ''),
];
