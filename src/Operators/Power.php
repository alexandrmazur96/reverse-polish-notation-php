<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

readonly class Power implements OperatorInterface
{

    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        return new Number($left->value() ** $right->value());
    }
}
