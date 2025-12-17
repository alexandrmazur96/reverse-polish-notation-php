<?php

declare(strict_types=1);

namespace Rpn\Operators;

use InvalidArgumentException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

readonly class Factorial implements OperatorInterface
{
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        $value = (int)$left->value();

        if ($value < 0) {
            throw new InvalidArgumentException('Factorial is not defined for negative numbers.');
        }

        $result = 1;
        for ($i = 2; $i <= $value; $i++) {
            $result *= $i;
        }

        return new Number((float)$result);
    }
}
