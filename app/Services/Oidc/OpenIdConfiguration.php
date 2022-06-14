<?php

namespace App\Services\Oidc;

class OpenIdConfiguration
{
    public function __construct(
        public string $version = '',
        public array $tokenEndpointAuthMethodsSupported = [],
        public bool $claimsParameterSupported = false,
        public bool $requestParameterSupported = false,
        public bool $requestUriParameterSupported = false,
        public bool $requireRequestUriRegistration = false,
        public array $grantTypesSupported = [],
        public bool $frontchannelLogoutSupported = false,
        public bool $frontchannelLogoutSessionSupported = false,
        public bool $backchannelLogoutSupported = false,
        public bool $backchannelLogoutSessionSupported = false,
        public string $issuer = '',
        public string $authorizationEndpoint = '',
        public string $jwksUri = '',
        public string $tokenEndpoint = '',
        public array $scopesSupported = [],
        public array $responseTypesSupported = [],
        public array $responseModesSupported = [],
        public array $subjectTypesSupported = [],
        public array $idTokenSigningAlgValuesSupported = [],
        public string $userinfoEndpoint = '',
        public array $codeChallengeMethodsSupported = [],
    ) {
    }
}