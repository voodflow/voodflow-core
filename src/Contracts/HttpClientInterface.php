<?php

namespace Voodflow\Core\Contracts;

interface HttpClientInterface
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $options
     * @return array{status:int, headers:array<string, string>, body:string, json?:mixed}
     */
    public function get(string $url, array $headers = [], array $options = []): array;

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return array{status:int, headers:array<string, string>, body:string, json?:mixed}
     */
    public function postForm(string $url, array $data, array $headers = [], array $options = []): array;
}

