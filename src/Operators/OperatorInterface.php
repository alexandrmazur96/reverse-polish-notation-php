<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Enum\Associativity;
use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\OperandInterface;

interface OperatorInterface
{
    public function getPrecedence(): int;

    public function getAssociativity(): Associativity;

    public function getType(): OperatorType;

    /** @throws InvalidOperatorArgumentException */
    public function apply(OperandInterface ...$operands): OperandInterface;
}
