<?php

declare(strict_types=1);

namespace Rpn\Parser;

use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;

interface ParserInterface
{
    /** @return iterable<OperandInterface|OperatorInterface> */
    public function parse(): iterable;
}
