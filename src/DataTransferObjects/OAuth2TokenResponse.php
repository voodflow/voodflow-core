<?php

namespace Voodflow\Core\DataTransferObjects;

final class OAuth2TokenResponse
{
    public function __construct(
        public string $accessToken,
        public ?string $refreshToken = null,
        public string $tokenType = 'Bearer',
        public ?int $expiresIn = null,
        public ?string $scope = null,
        public ?array $raw = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'scope' => $this->scope,
        ];
    }
}

