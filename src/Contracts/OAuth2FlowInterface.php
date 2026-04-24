<?php

namespace Voodflow\Core\Contracts;

use Voodflow\Core\DataTransferObjects\OAuth2AuthorizationConfig;
use Voodflow\Core\DataTransferObjects\OAuth2TokenResponse;

interface OAuth2FlowInterface
{
    public function buildAuthorizationUrl(OAuth2AuthorizationConfig $config, string $state): string;

    public function exchangeCodeForToken(OAuth2AuthorizationConfig $config, string $code, ?string $state = null): OAuth2TokenResponse;

    public function refreshAccessToken(OAuth2AuthorizationConfig $config): OAuth2TokenResponse;
}

