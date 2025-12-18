<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Rpn\Expression;
use Rpn\Operands\Number;
use Rpn\Operators\Multiplication;
use Rpn\Stream\ExpressionPartsStream;
use Throwable;

final class ExpressionTest extends TestCase
{
    public function testSimpleExpression(): void
    {
        $stream = ExpressionPartsStream::of(
            [
                new Number(3),
                new Number(4),
                new Multiplication(),
            ]
        );

        try {
            $this->assertEquals(12, (new Expression())->evaluate($stream));
        } catch (Throwable $e) {
            $this->fail("Failed to evaluate expression: " . $e->getMessage());
        }
    }
}
