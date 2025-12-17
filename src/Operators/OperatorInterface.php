<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Operands\OperandInterface;

interface OperatorInterface
{
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface;
}
