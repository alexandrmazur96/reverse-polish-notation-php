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

readonly class Factorial implements OperatorInterface
{
    #[Override]
    public function getPrecedence(): int
    {
        return 5;
    }

    #[Override]
    public function getAssociativity(): Associativity
    {
        return Associativity::None;
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
            throw new InvalidOperatorArgumentException('Factorial requires exactly 1 operand.');
        }

        $value = (int)$operands[0]->value();

        if ($value < 0) {
            throw new InvalidOperatorArgumentException('Factorial is not defined for negative numbers.');
        }

        $result = 1;
        for ($i = 2; $i <= $value; $i++) {
            $result *= $i;
        }

        return new Number($result);
    }
}
