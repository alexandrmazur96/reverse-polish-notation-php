<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Rpn\Enum\OperatorType;
use Rpn\Operators\Math\Addition;
use Rpn\Operators\Math\Negation;
use Rpn\Operators\Math\Subtraction;
use Rpn\Operators\OperatorRegistry;

final class OperatorRegistryTest extends TestCase
{
    public function testAddAndResolveSingleSymbol(): void
    {
        $registry = new OperatorRegistry();
        $addition = new Addition();
        $registry->add('+', $addition);

        $this->assertSame($addition, $registry->resolve('+', false));
    }

    public function testAddAndResolveMultipleSymbols(): void
    {
        $registry = new OperatorRegistry();
        $addition = new Addition();
        $registry->add(['plus', '+'], $addition);

        $this->assertSame($addition, $registry->resolve('+', false));
        $this->assertSame($addition, $registry->resolve('plus', false));
    }

    public function testResolveReturnsNullForUnknownToken(): void
    {
        $registry = new OperatorRegistry();
        $this->assertNull($registry->resolve('@', false));
    }

    public function testGetSymbolicTokens(): void
    {
        $registry = new OperatorRegistry();
        $registry->add('+', new Addition());
        $registry->add(['-', 'minus'], new Subtraction());

        $this->assertEqualsCanonicalizing(['+', '-'], $registry->getSymbolicTokens());
    }

    public function testResolveDistinguishesUnaryAndBinary(): void
    {
        $registry = new OperatorRegistry();
        $subtraction = new Subtraction(); // Binary
        $negation = new Negation();       // Unary

        $registry->add('-', $subtraction);
        $registry->add('-', $negation);

        // When an operand is NOT expected, it's a binary operator (e.g., 3 - 4)
        $resolvedBinary = $registry->resolve('-', false);
        $this->assertNotNull($resolvedBinary);
        $this->assertEquals(OperatorType::Binary, $resolvedBinary->getType());

        // When an operand IS expected, it's a unary operator (e.g., -4)
        $resolvedUnary = $registry->resolve('-', true);
        $this->assertNotNull($resolvedUnary);
        $this->assertEquals(OperatorType::UnaryPrefix, $resolvedUnary->getType());
    }

    public function testResolveReturnsNullIfNoMatchingType(): void
    {
        $registry = new OperatorRegistry();
        $negation = new Negation(); // Unary only
        $registry->add('-', $negation);

        // Should not find a binary version of '-'
        $this->assertNull($registry->resolve('-', false));
    }
}
