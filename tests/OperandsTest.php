<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Rpn\Operands\Number;
use Rpn\Operands\Variable;

final class OperandsTest extends TestCase
{
    public function testNumberUnmodified(): void
    {
        $this->assertEquals(5.55, (new Number(5.55))->value());
    }

    public function testVariableUnmodified(): void
    {
        $this->assertEquals(':VARIABLE', (new Variable(':VARIABLE'))->value());
    }
}
