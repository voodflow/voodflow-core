<?php

namespace Voodflow\Core\Contracts;

interface CacheStoreInterface
{
    public function put(string $key, mixed $value, int $ttlSeconds): void;

    public function pull(string $key): mixed;
}

