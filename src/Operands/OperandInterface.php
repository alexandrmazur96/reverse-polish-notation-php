<?php

declare(strict_types=1);

namespace Rpn\Operands;

/** @template TOut */
interface OperandInterface
{
    /** @return TOut */
    public function value(): mixed;
}
