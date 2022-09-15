<?php

namespace App\Services\Oidc;

use Exception;
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
use Jumbojett\Interfaces\HandleJweResponseInterface;
use Jumbojett\OpenIDConnectClient;
use RuntimeException;

class OidcService
{
    protected OpenIdConfiguration $openIdConfiguration;

    public function __construct(
        protected string $issuer,
        protected string $decryptionKeyPath,
    ) {
        $this->openIdConfiguration = $this->getOpenIdConfiguration();
    }

    public function getOpenIdConfiguration(): OpenIdConfiguration
    {
        $response = Http::get($this->issuer . '/.well-known/openid-configuration');

        return new OpenIdConfiguration(
            version: $response->json('version', ''),
            tokenEndpointAuthMethodsSupported: $response->json('token_endpoint_auth_methods_supported', []),
            claimsParameterSupported: $response->json('claims_parameter_supported', false),
            requestParameterSupported: $response->json('request_parameter_supported', false),
            requestUriParameterSupported: $response->json('request_uri_parameter_supported', false),
            requireRequestUriRegistration: $response->json('require_request_uri_registration', false),
            grantTypesSupported: $response->json('grant_types_supported', []),
            frontchannelLogoutSupported: $response->json('frontchannel_logout_supported', false),
            frontchannelLogoutSessionSupported: $response->json('frontchannel_logout_session_supported', false),
            backchannelLogoutSupported: $response->json('backchannel_logout_supported', false),
            backchannelLogoutSessionSupported: $response->json('backchannel_logout_session_supported', false),
            issuer: $response->json('issuer', ''),
            authorizationEndpoint: $response->json('authorization_endpoint', ''),
            jwksUri: $response->json('jwks_uri', ''),
            tokenEndpoint: $response->json('token_endpoint', ''),
            scopesSupported: $response->json('scopes_supported', []),
            responseTypesSupported: $response->json('response_types_supported', []),
            responseModesSupported: $response->json('response_modes_supported', []),
            subjectTypesSupported: $response->json('subject_types_supported', []),
            idTokenSigningAlgValuesSupported: $response->json('id_token_signing_alg_values_supported', []),
            userinfoEndpoint: $response->json('userinfo_endpoint', ''),
            codeChallengeMethodsSupported: $response->json('code_challenge_methods_supported', [])
        );
    }

    /**
     * @throws Exception
     */
    public function requestUserInfo(string $accessToken): object
    {
        // Get user info endpoint
        $response = Http::withToken($accessToken)->get($this->openIdConfiguration->userinfoEndpoint . '?schema=openid');

        if (str_contains($response->header('Content-Type'), 'application/json')) {
            return $response->object();
        }

        if (str_contains($response->header('Content-Type'), 'application/jwt')) {
            // Get jwt header
            $jwtHeader = json_decode(base64_decode(explode('.', $response->body())[0]), false);

            // Check if jwt header contains enc field
            if (isset($jwtHeader->enc)) {
                // Decrypt jwe, we now have the nested signed jwt
                $jwt = $this->decryptJwe($response->body());
            } else {
                // When we do not have a enc field, we have a signed jwt
                $jwt = $response->body();
            }

            // Validate signed jwt
            /** @var Validate $jws */
            $jws = Load::jws($jwt)
                ->algs(['RS256'])
                ->exp()
                ->iss($this->issuer)
                ->keyset($this->getJwkSet());

            $jwt = $jws->run();

            // Return the claims as object
            return (object) $jwt->claims->all();
        }

        throw new RuntimeException('Unsupported content type');
    }

    /**
     * Decrypts the given JWE string and returns JWT.
     * @throws Exception
     */
    private function decryptJwe(string $jweString): string
    {
        // Decrypt JWE
        $decryptionKey = JWKFactory::createFromKeyFile($this->decryptionKeyPath);
        $keyEncryptionAlgorithmManager = new AlgorithmManager([new RSAOAEP()]);
        $contentEncryptionAlgorithmManager = new AlgorithmManager([new A128CBCHS256()]);
        $compressionMethodManager = new CompressionMethodManager([new Deflate()]);
        $jweDecrypter = new JWEDecrypter(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
            $compressionMethodManager
        );

        $serializerManager = new JWESerializerManager([new CompactSerializer()]);

        $jwe = $serializerManager->unserialize($jweString);

        // Success of decryption, $jwe is now decrypted
        $success = $jweDecrypter->decryptUsingKey($jwe, $decryptionKey, 0);
        if (!$success) {
            throw new RuntimeException('Failed to decrypt JWE');
        }

        return $jwe->getPayload();
    }

    private function getJwkSet(): JWKSet
    {
        $response = Http::get($this->openIdConfiguration->jwksUri);

        return JWKSet::createFromJson($response);
    }
}