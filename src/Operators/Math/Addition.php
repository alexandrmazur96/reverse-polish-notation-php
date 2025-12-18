<?php

declare(strict_types=1);

namespace Rpn\Operators\Math;

use Override;
use Rpn\Enum\Associativity;
use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;

use function count;

readonly class Addition implements OperatorInterface
{
    #[Override]
    public function getPrecedence(): int
    {
        return 1;
    }

    #[Override]
    public function getAssociativity(): Associativity
    {
        return Associativity::Left;
    }

    #[Override]
    public function getType(): OperatorType
    {
        return OperatorType::Binary;
    }

    #[Override]
    public function apply(OperandInterface ...$operands): OperandInterface
    {
        if (count($operands) !== 2) {
            throw new InvalidOperatorArgumentException('Addition operator requires exactly two operands.');
        }
        if (!($operands[0] instanceof Number) || !($operands[1] instanceof Number)) {
            throw new InvalidOperatorArgumentException('Addition operator requires Number operands.');
        }

        return new Number($operands[0]->value() + $operands[1]->value());
    }
}
