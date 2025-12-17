<?php

declare(strict_types=1);

namespace Rpn\Operands;

use Override;

readonly class Number implements OperandInterface
{
    /** @param float|int|numeric-string $value */
    public function __construct(private float|int|string $value)
    {
    }

    #[Override]
    public function value(): float
    {
        return (float)$this->value;
    }
}
