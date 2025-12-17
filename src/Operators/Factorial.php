<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Override;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

readonly class Factorial implements OperatorInterface
{
    #[Override]
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        $value = (int)$left->value();

        if ($value < 0) {
            throw new InvalidOperatorArgumentException('Factorial is not defined for negative numbers.');
        }

        $result = 1;
        for ($i = 2; $i <= $value; $i++) {
            $result *= $i;
        }

        return new Number((float)$result);
    }
}
