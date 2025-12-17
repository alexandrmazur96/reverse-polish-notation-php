<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function exp;

readonly class Exp implements OperatorInterface
{
    public function apply(OperandInterface $left, OperandInterface $unused): OperandInterface
    {
        return new Number(exp($left->value()));
    }
}
