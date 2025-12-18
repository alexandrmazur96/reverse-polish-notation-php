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

readonly class Percent implements OperatorInterface
{
    #[Override]
    public function getPrecedence(): int
    {
        return 5;
    }

    #[Override]
    public function getAssociativity(): Associativity
    {
        return Associativity::Left;
    }

    #[Override]
    public function getType(): OperatorType
    {
        return OperatorType::UnaryPostfix;
    }

    #[Override]
    public function apply(OperandInterface ...$operands): OperandInterface
    {
        if (count($operands) !== 1) {
            throw new InvalidOperatorArgumentException('Percent operator requires exactly 1 operand.');
        }

        if (!($operands[0] instanceof Number)) {
            throw new InvalidOperatorArgumentException('Percent operator requires a Number operand.');
        }

        return new Number($operands[0]->value() / 100.0);
    }
}
