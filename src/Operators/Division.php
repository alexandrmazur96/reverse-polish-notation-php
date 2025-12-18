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

readonly class Division implements OperatorInterface
{
    #[Override]
    public function getPrecedence(): int
    {
        return 2;
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
            throw new InvalidOperatorArgumentException('Division operator requires exactly two operands.');
        }
        if ($operands[1]->value() === 0.0) {
            throw new InvalidOperatorArgumentException('Division by zero is not allowed.');
        }

        return new Number($operands[0]->value() / $operands[1]->value());
    }
}
