<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Override;
use Rpn\Enum\Associativity;
use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function abs;
use function count;

readonly class CubeRoot implements OperatorInterface
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
            throw new InvalidOperatorArgumentException('CubeRoot operator requires exactly one operand.');
        }

        $value = $operands[0]->value();
        if ($value < .0) {
            return new Number(-1. * (abs($value) ** (1 / 3)));
        }

        return new Number($value ** (1 / 3));
    }
}
