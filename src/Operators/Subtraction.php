<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Override;
use Rpn\Enum\Associativity;
use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function count;

readonly class Subtraction implements OperatorInterface
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
            throw new InvalidOperatorArgumentException('Subtraction operator requires exactly two operands.');
        }

        return new Number($operands[0]->value() - $operands[1]->value());
    }
}
