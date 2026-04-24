<?php

namespace Voodflow\Voodcore\Contracts;

interface ExecutionLoggerInterface
{
    public function debug(string $message, array $context = []): void;

    public function info(string $message, array $context = []): void;

    public function warning(string $message, array $context = []): void;

    public function error(string $message, array $context = []): void;

    public function exception(\Throwable $exception, array $context = []): void;

    public function nodeStart(string $nodeId, array $context = []): void;

    public function nodeComplete(string $nodeId, array $context = []): void;

    public function nodeFailed(string $nodeId, \Throwable $exception, array $context = []): void;
}

