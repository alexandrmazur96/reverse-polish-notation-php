<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Override;
use Rpn\Operands\Number;
use Rpn\Operands\Resolvers\OperandResolver;
use Rpn\Operands\Variable;

final class ResolversTest extends TestCase
{
    private OperandResolver $resolver;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new OperandResolver();
    }

    #[Override]
    protected function tearDown(): void
    {
        unset($this->resolver);
        parent::tearDown();
    }

    public function testResolvesVariable(): void
    {
        $operand = $this->resolver->resolve(':x');
        $this->assertInstanceOf(Variable::class, $operand);
        $this->assertEquals(':x', $operand->value());
    }

    public function testResolvesNumber(): void
    {
        $operand = $this->resolver->resolve('123.45');
        $this->assertInstanceOf(Number::class, $operand);
        $this->assertEquals(123.45, $operand->value());
    }

    public function testReturnsNullForInvalidVariable(): void
    {
        $this->assertNull($this->resolver->resolve(':'));
    }

    public function testReturnsNullForNonOperand(): void
    {
        $this->assertNull($this->resolver->resolve('abc'));
    }

    public function testReturnsNullForEmptyString(): void
    {
        $this->assertNull($this->resolver->resolve(''));
    }
}
