<?php

declare(strict_types=1);

namespace Rpn\Operators;

use InvalidArgumentException;
use Override;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function log;

readonly class Log implements OperatorInterface
{
    #[Override]
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        $value = $left->value();

        if ($value <= 0) {
            throw new InvalidArgumentException("Logarithm undefined for non-positive numbers: $value");
        }

        return new Number(log($value));
    }
}
