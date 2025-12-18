<?php

declare(strict_types=1);

namespace Rpn\Parsers;

use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Exceptions\UnknownFunctionException;
use Rpn\Exceptions\UnknownTokenException;
use Rpn\Stream\ExpressionPartsStream;

interface ParserInterface
{
    /**
     * @throws UnknownTokenException
     * @throws UnknownFunctionException
     * @throws InvalidExpressionException
     */
    public function parse(string $source): ExpressionPartsStream;
}
