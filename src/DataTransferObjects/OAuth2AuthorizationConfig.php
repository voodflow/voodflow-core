<?php

namespace Voodflow\Core\DataTransferObjects;

final class OAuth2AuthorizationConfig
{
    /**
     * @param array<int, string> $scopes
     */
    public function __construct(
        public string $authorizeUrl,
        public string $tokenUrl,
        public string $clientId,
        public ?string $clientSecret,
        public string $redirectUri,
        public array $scopes = [],
        public bool $usePkce = false,
        public ?string $refreshToken = null,
    ) {}
}

