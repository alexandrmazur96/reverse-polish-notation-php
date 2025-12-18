<?php

declare(strict_types=1);

namespace Rpn\Tests\Stubs;

use Override;
use Rpn\Operands\OperandInterface;

/** @implements OperandInterface<string> */
final readonly class BadOperand implements OperandInterface
{
    #[Override]
    public function value(): string
    {
        return 'test';
    }
}
