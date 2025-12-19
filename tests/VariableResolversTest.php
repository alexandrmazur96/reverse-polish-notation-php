<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Rpn\Exceptions\InvalidVariableException;
use Rpn\Operands\Number;
use Rpn\VariableResolver\StandardVariableResolver;

final class VariableResolversTest extends TestCase
{
    /** @throws InvalidVariableException */
    public function testAlreadyMappedToOperandResolution(): void
    {
        $operand = new Number(10);

        $this->assertEquals(
            $operand,
            (new StandardVariableResolver())->resolve('test', $operand),
        );
    }

    /** @throws InvalidVariableException */
    public function testNumericVariableResolution(): void
    {
        $this->assertEquals(
            new Number(42),
            (new StandardVariableResolver())->resolve('test', 42),
        );

        $this->assertEquals(
            new Number(3.14),
            (new StandardVariableResolver())->resolve('test', 3.14),
        );
    }

    public function testInvalidVariableResolution(): void
    {
        $this->expectException(InvalidVariableException::class);
        (new StandardVariableResolver())->resolve('test', 'invalid_string');
    }
}
