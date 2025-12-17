<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\OperandInterface;

interface OperatorInterface
{
    /** @throws InvalidOperatorArgumentException */
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface;
}
