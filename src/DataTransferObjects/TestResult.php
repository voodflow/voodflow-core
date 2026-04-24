<?php

namespace Voodflow\Core\DataTransferObjects;

final class TestResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?array $details = null,
    ) {}

    public static function success(string $message, ?array $details = null): self
    {
        return new self(true, $message, $details);
    }

    public static function failure(string $message, ?array $details = null): self
    {
        return new self(false, $message, $details);
    }

    public static function unsupported(): self
    {
        return new self(false, 'Test not supported.');
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'details' => $this->details,
        ];
    }
}

