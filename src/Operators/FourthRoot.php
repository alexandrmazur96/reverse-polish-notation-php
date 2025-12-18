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

readonly class FourthRoot implements OperatorInterface
{
    #[Override]
    public function getPrecedence(): int
    {
        return 4;
    }

    #[Override]
    public function getAssociativity(): Associativity
    {
        return Associativity::None;
    }

    #[Override]
    public function getType(): OperatorType
    {
        return OperatorType::Function;
    }

    #[Override]
    public function apply(OperandInterface ...$operands): OperandInterface
    {
        if (count($operands) !== 1) {
            throw new InvalidOperatorArgumentException('FourthRoot operator requires exactly one operand.');
        }

        $val = $operands[0]->value();
        if ($val < 0) {
            throw new InvalidOperatorArgumentException('Cannot calculate fourth root of a negative number.');
        }

        return new Number($val ** (1 / 4));
    }
}
