<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Override;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function abs;

readonly class CubeRoot implements OperatorInterface
{
    #[Override]
    public function apply(OperandInterface $left, OperandInterface $right): OperandInterface
    {
        $value = $left->value();
        if ($value < .0) {
            return new Number(-1. * (abs($value) ** (1 / 3)));
        }

        // 2. Standard positive calculation
        return new Number($value ** (1 / 3));
    }
}
