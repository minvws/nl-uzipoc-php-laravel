<?php

declare(strict_types=1);

return [
    'issuer' => env('UZI_ISSUER', ''),
    'client_id' => env('UZI_CLIENT_ID', ''),
    'decryption_key_path' => env('UZI_DECRYPTION_KEY_PATH', ''),
];
