<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

readonly class Factorial implements OperatorInterface
{
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        $value = $left->value();

        if ($value < 0) {
            return new Number(0);
        }

        $factorial = (int)$right->value();
        $result = 1;
        for ($i = 2; $i <= $factorial; $i++) {
            $result *= $i;
        }

        return new Number((float)$result);
    }
}
