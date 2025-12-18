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

readonly class Negation implements OperatorInterface
{
    #[Override]
    public function getPrecedence(): int
    {
        return 3;
    }

    #[Override]
    public function getAssociativity(): Associativity
    {
        return Associativity::Right;
    }

    #[Override]
    public function getType(): OperatorType
    {
        return OperatorType::UnaryPrefix;
    }

    #[Override]
    public function apply(OperandInterface ...$operands): OperandInterface
    {
        if (count($operands) !== 1) {
            throw new InvalidOperatorArgumentException('Negation requires exactly 1 operand.');
        }

        return new Number(-$operands[0]->value());
    }
}
