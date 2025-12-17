<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function exp;

readonly class Exp implements OperatorInterface
{
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        return new Number(exp($left->value()));
    }
}
