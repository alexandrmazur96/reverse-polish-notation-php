<?php

declare(strict_types=1);

namespace Rpn\Operands;

use Override;

/** @implements OperandInterface<string> */
readonly class Variable implements OperandInterface
{
    public function __construct(private string $variableName)
    {
    }

    #[Override]
    public function value(): string
    {
        return $this->variableName;
    }
}
