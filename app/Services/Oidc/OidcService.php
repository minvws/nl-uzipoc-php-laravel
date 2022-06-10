<?php

namespace App\Services\Oidc;

use Illuminate\Support\Facades\Http;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP;
use Jose\Component\Encryption\Compression\CompressionMethodManager;
use Jose\Component\Encryption\Compression\Deflate;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Easy\Load;
use Jose\Easy\ParameterBag;
use Jose\Easy\Validate;

class OidcService
{
    protected OpenIdConfiguration $openIdConfiguration;

    public function __construct(
        protected string $issuer,
        protected string $decryptionKey,
    ) {
        $this->openIdConfiguration = $this->getOpenIdConfiguration();
    }

    public function getOpenIdConfiguration(): OpenIdConfiguration
    {
        $response = Http::get($this->issuer . '/.well-known/openid-configuration');

        return new OpenIdConfiguration(
            version: $response->json('version'),
            tokenEndpointAuthMethodsSupported: $response->json('token_endpoint_auth_methods_supported'),
            claimsParameterSupported: $response->json('claims_parameter_supported'),
            requestParameterSupported: $response->json('request_parameter_supported'),
            requestUriParameterSupported: $response->json('request_uri_parameter_supported'),
            requireRequestUriRegistration: $response->json('require_request_uri_registration'),
            grantTypesSupported: $response->json('grant_types_supported'),
            frontchannelLogoutSupported: $response->json('frontchannel_logout_supported'),
            frontchannelLogoutSessionSupported: $response->json('frontchannel_logout_session_supported'),
            backchannelLogoutSupported: $response->json('backchannel_logout_supported'),
            backchannelLogoutSessionSupported: $response->json('backchannel_logout_session_supported'),
            issuer: $response->json('issuer'),
            authorizationEndpoint: $response->json('authorization_endpoint'),
            jwksUri: $response->json('jwks_uri'),
            tokenEndpoint: $response->json('token_endpoint'),
            scopesSupported: $response->json('scopes_supported'),
            responseTypesSupported: $response->json('response_types_supported'),
            responseModesSupported: $response->json('response_modes_supported'),
            subjectTypesSupported: $response->json('subject_types_supported'),
            idTokenSigningAlgValuesSupported: $response->json('id_token_signing_alg_values_supported'),
            userinfoEndpoint: $response->json('userinfo_endpoint'),
            codeChallengeMethodsSupported: $response->json('code_challenge_methods_supported')
        );
    }

    /**
     * @throws \Exception
     */
    public function requestUserInfo(string $accessToken): ParameterBag
    {
        // Get user info endpoint
        $jwe = Http::withToken($accessToken)->get($this->openIdConfiguration->userinfoEndpoint . '?schema=openid');

        // Decrypt jwe to jwt
        $jwt = $this->decryptJwe($jwe);

        // Verify JWT
        /** @var Validate $jws */
        $jws = Load::jws($jwt)
            ->algs(['RS256'])
            ->exp()
            ->iss($this->issuer)
            ->keyset($this->getJwkSet());

        $jwt = $jws->run();

        return $jwt->claims;
    }

    /**
     * Decrypts the given JWE and returns JWT.
     * @throws \Exception
     */
    private function decryptJwe(string $jwe): string
    {
        // Decrypt JWE
        $decryptionKey = JWKFactory::createFromKeyFile($this->decryptionKey);
        $keyEncryptionAlgorithmManager = new AlgorithmManager([new RSAOAEP()]);
        $contentEncryptionAlgorithmManager = new AlgorithmManager([new A128CBCHS256()]);
        $compressionMethodManager = new CompressionMethodManager([new Deflate()]);
        $jweDecrypter = new JWEDecrypter(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
            $compressionMethodManager
        );

        $serializerManager = new JWESerializerManager([new CompactSerializer()]);

        $jwe = $serializerManager->unserialize($jwe);

        // Success of decryption, $jwe is now decrypted
        $success = $jweDecrypter->decryptUsingKey($jwe, $decryptionKey, 0);
        if (!$success) {
            throw new \Exception('Failed to decrypt JWE');
        }

        return $jwe->getPayload();
    }

    private function getJwkSet(): JWKSet
    {
        $response = Http::get($this->openIdConfiguration->jwksUri);

        return JWKSet::createFromJson($response);
    }
}