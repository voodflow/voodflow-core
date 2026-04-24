<?php

namespace Voodflow\Core\DataTransferObjects;

final class CredentialTestRequest
{
    /**
     * @param array<string, mixed> $credentials
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $type,
        public ?string $provider = null,
        public array $credentials = [],
        public array $metadata = [],
    ) {}
}

