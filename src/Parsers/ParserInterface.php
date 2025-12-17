<?php

declare(strict_types=1);

namespace Rpn\Parsers;

use Rpn\Exceptions\UnknownFunctionException;
use Rpn\Exceptions\UnknownTokenException;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;

interface ParserInterface
{
    /**
     * @return iterable<int, OperandInterface|OperatorInterface>
     * @throws UnknownTokenException
     * @throws UnknownFunctionException
     */
    public function parse(): iterable;
}
