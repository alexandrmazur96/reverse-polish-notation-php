<?php

declare(strict_types=1);

namespace Rpn\Operands;

interface OperandInterface
{
    public function value(): mixed;
}
