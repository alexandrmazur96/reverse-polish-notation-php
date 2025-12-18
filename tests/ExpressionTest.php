<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Expression;
use Rpn\Operands\Number;
use Rpn\Operators\Factorial;
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

    public function testTooManyOperandsRemaining(): void
    {
        $this->expectException(InvalidExpressionException::class);
        $this->expectExceptionMessage('Too many operands remaining.');

        $stream = ExpressionPartsStream::of([new Number(3), new Number(4)]);
        (new Expression())->evaluate($stream);
    }

    public function testNotEnoughOperandsForBinaryOperation(): void
    {
        $this->expectException(InvalidExpressionException::class);
        $this->expectExceptionMessage('Not enough operands.');

        $stream = ExpressionPartsStream::of([new Number(3), new Multiplication()]);
        (new Expression())->evaluate($stream);
    }

    public function testNotEnoughOperandsForUnaryOperation(): void
    {
        $this->expectException(InvalidExpressionException::class);
        $this->expectExceptionMessage('Not enough operands.');

        $stream = ExpressionPartsStream::of([new Factorial()]);
        (new Expression())->evaluate($stream);
    }
}
