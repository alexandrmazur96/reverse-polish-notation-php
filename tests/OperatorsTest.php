<?php

declare(strict_types=1);

namespace Rpn\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\Number;
use Rpn\Operators\Addition;
use Rpn\Operators\CubeRoot;
use Rpn\Operators\Division;
use Rpn\Operators\Exp;
use Rpn\Operators\Factorial;
use Rpn\Operators\FourthRoot;
use Rpn\Operators\Log;
use Rpn\Operators\Multiplication;
use Rpn\Operators\Negation;
use Rpn\Operators\Power;
use Rpn\Operators\Sqrt;
use Rpn\Operators\Subtraction;

final class OperatorsTest extends TestCase
{
    public function testAddition(): void
    {
        $operator = new Addition();
        $result = $operator->apply(new Number(5), new Number(3));
        $this->assertEquals(8, $result->value());
    }

    public function testSubtraction(): void
    {
        $operator = new Subtraction();
        $result = $operator->apply(new Number(5), new Number(3));
        $this->assertEquals(2, $result->value());
    }

    public function testMultiplication(): void
    {
        $operator = new Multiplication();
        $result = $operator->apply(new Number(5), new Number(3));
        $this->assertEquals(15, $result->value());
    }

    public function testDivision(): void
    {
        $operator = new Division();
        $result = $operator->apply(new Number(10), new Number(2));
        $this->assertEquals(5, $result->value());
    }

    public function testDivisionByZero(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Division by zero is not allowed.');
        (new Division())->apply(new Number(10), new Number(0));
    }

    public function testPower(): void
    {
        $operator = new Power();
        $result = $operator->apply(new Number(2), new Number(3));
        $this->assertEquals(8, $result->value());
    }

    public function testNegation(): void
    {
        $operator = new Negation();
        $result = $operator->apply(new Number(5));
        $this->assertEquals(-5, $result->value());
    }

    #[DataProvider('factorialProvider')]
    public function testFactorial(float $number, float $expected): void
    {
        $operator = new Factorial();
        $result = $operator->apply(new Number($number));
        $this->assertEquals($expected, $result->value());
    }

    /** @return array<string, array{0: float, 1: float}> */
    public static function factorialProvider(): array
    {
        return [
            'factorial of 5' => [5, 120],
            'factorial of 0' => [0, 1],
            'factorial of 1' => [1, 1],
        ];
    }

    public function testFactorialOfNegative(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Factorial is not defined for negative numbers.');
        (new Factorial())->apply(new Number(-1));
    }

    public function testFactorialOfNonInteger(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Factorial is only defined for integers.');
        (new Factorial())->apply(new Number(1.5));
    }

    public function testSqrt(): void
    {
        $operator = new Sqrt();
        $result = $operator->apply(new Number(16));
        $this->assertEquals(4, $result->value());
    }

    public function testSqrtOfNegative(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Cannot calculate square root of a negative number.');
        (new Sqrt())->apply(new Number(-1));
    }

    public function testCubeRoot(): void
    {
        $operator = new CubeRoot();
        $result = $operator->apply(new Number(27));
        $this->assertEquals(3, $result->value());
    }

    public function testCubeRootOfNegative(): void
    {
        $operator = new CubeRoot();
        $result = $operator->apply(new Number(-27));
        $this->assertEquals(-3, $result->value());
    }

    public function testFourthRoot(): void
    {
        $operator = new FourthRoot();
        $result = $operator->apply(new Number(81));
        $this->assertEquals(3, $result->value());
    }

    public function testFourthRootOfNegative(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Cannot calculate fourth root of a negative number.');
        (new FourthRoot())->apply(new Number(-1));
    }

    public function testLog(): void
    {
        $operator = new Log();
        $result = $operator->apply(new Number(1));
        $this->assertEquals(0, $result->value());
    }

    public function testLogOfNegative(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Logarithm is only defined for positive numbers.');
        (new Log())->apply(new Number(-1));
    }

    public function testLogOfZero(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Logarithm is only defined for positive numbers.');
        (new Log())->apply(new Number(0));
    }

    public function testExp(): void
    {
        $operator = new Exp();
        $result = $operator->apply(new Number(0));
        $this->assertEquals(1, $result->value());
    }
}
