<?php

namespace Voodflow\Core\Contracts;

use Voodflow\Core\DataTransferObjects\CredentialTestRequest;
use Voodflow\Core\DataTransferObjects\TestResult;

interface CredentialTesterInterface
{
    public function test(CredentialTestRequest $request): TestResult;
}

