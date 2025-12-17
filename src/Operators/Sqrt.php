<?php

declare(strict_types=1);

namespace Rpn\Operators;

use InvalidArgumentException;
use Override;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function sqrt;

readonly class Sqrt implements OperatorInterface
{
    #[Override]
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        $value = $left->value();

        if ($value < 0) {
            throw new InvalidArgumentException("Cannot calculate square root of a negative number: $value");
        }

        return new Number(sqrt($value));
    }
}
