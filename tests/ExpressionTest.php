<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Rpn\Expression;
use Rpn\Operands\Number;
use Rpn\Operators\Multiplication;

final class ExpressionTest extends TestCase
{
    public function testSimpleExpression(): void
    {
        $expression = new Expression(
            new Number(3),
            new Number(4),
            new Multiplication(),
        );

        $this->assertEquals(12, $expression->evaluate());
    }
}
