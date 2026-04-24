<?php

namespace Voodflow\Core\Services;

use Voodflow\Core\Contracts\CacheStoreInterface;
use Voodflow\Core\Contracts\HttpClientInterface;
use Voodflow\Core\Contracts\OAuth2FlowInterface;
use Voodflow\Core\DataTransferObjects\OAuth2AuthorizationConfig;
use Voodflow\Core\DataTransferObjects\OAuth2TokenResponse;
use Voodflow\Core\Support\Pkce;

final class OAuth2Flow implements OAuth2FlowInterface
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly CacheStoreInterface $cache,
        private readonly int $pkceTtlSeconds = 600,
    ) {}

    public function buildAuthorizationUrl(OAuth2AuthorizationConfig $config, string $state): string
    {
        $params = [
            'client_id' => $config->clientId,
            'redirect_uri' => $config->redirectUri,
            'response_type' => 'code',
            'state' => $state,
        ];

        if (count($config->scopes) > 0) {
            $params['scope'] = implode(' ', $config->scopes);
        }

        if ($config->usePkce) {
            $pkce = Pkce::generate();

            $this->cache->put(
                $this->pkceCacheKey($state),
                $pkce['code_verifier'],
                $this->pkceTtlSeconds,
            );

            $params['code_challenge'] = $pkce['code_challenge'];
            $params['code_challenge_method'] = 'S256';
        }

        return $config->authorizeUrl . '?' . http_build_query($params);
    }

    public function exchangeCodeForToken(OAuth2AuthorizationConfig $config, string $code, ?string $state = null): OAuth2TokenResponse
    {
        $params = [
            'client_id' => $config->clientId,
            'client_secret' => $config->clientSecret ?? '',
            'code' => $code,
            'redirect_uri' => $config->redirectUri,
            'grant_type' => 'authorization_code',
        ];

        if ($config->usePkce && $state) {
            $codeVerifier = $this->cache->pull($this->pkceCacheKey($state));
            if (is_string($codeVerifier) && $codeVerifier !== '') {
                $params['code_verifier'] = $codeVerifier;
            }
        }

        $response = $this->http->postForm($config->tokenUrl, $params, [
            'Accept' => 'application/json',
        ]);

        $json = $response['json'] ?? null;

        if (! is_array($json) || ! isset($json['access_token'])) {
            throw new \RuntimeException('Token exchange failed.');
        }

        return new OAuth2TokenResponse(
            accessToken: (string) $json['access_token'],
            refreshToken: isset($json['refresh_token']) ? (string) $json['refresh_token'] : $config->refreshToken,
            tokenType: isset($json['token_type']) ? (string) $json['token_type'] : 'Bearer',
            expiresIn: isset($json['expires_in']) ? (int) $json['expires_in'] : null,
            scope: isset($json['scope']) ? (string) $json['scope'] : null,
            raw: $json,
        );
    }

    public function refreshAccessToken(OAuth2AuthorizationConfig $config): OAuth2TokenResponse
    {
        if (! $config->refreshToken) {
            throw new \RuntimeException('No refresh token available.');
        }

        $params = [
            'client_id' => $config->clientId,
            'client_secret' => $config->clientSecret ?? '',
            'refresh_token' => $config->refreshToken,
            'grant_type' => 'refresh_token',
        ];

        $response = $this->http->postForm($config->tokenUrl, $params, [
            'Accept' => 'application/json',
        ]);

        $json = $response['json'] ?? null;

        if (! is_array($json) || ! isset($json['access_token'])) {
            throw new \RuntimeException('Token refresh failed.');
        }

        return new OAuth2TokenResponse(
            accessToken: (string) $json['access_token'],
            refreshToken: isset($json['refresh_token']) ? (string) $json['refresh_token'] : $config->refreshToken,
            tokenType: isset($json['token_type']) ? (string) $json['token_type'] : 'Bearer',
            expiresIn: isset($json['expires_in']) ? (int) $json['expires_in'] : null,
            scope: isset($json['scope']) ? (string) $json['scope'] : null,
            raw: $json,
        );
    }

    private function pkceCacheKey(string $state): string
    {
        return "oauth.pkce.{$state}";
    }
}

