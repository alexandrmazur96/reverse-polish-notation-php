<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Generator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionException;
use Rpn\Enum\Associativity;
use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\Math\Addition;
use Rpn\Operators\Math\CubeRoot;
use Rpn\Operators\Math\Division;
use Rpn\Operators\Math\Exp;
use Rpn\Operators\Math\Factorial;
use Rpn\Operators\Math\FourthRoot;
use Rpn\Operators\Math\Log;
use Rpn\Operators\Math\Multiplication;
use Rpn\Operators\Math\Negation;
use Rpn\Operators\Math\Power;
use Rpn\Operators\Math\Sqrt;
use Rpn\Operators\Math\Subtraction;
use Rpn\Operators\OperatorInterface;

use function array_fill;

final class OperatorsTest extends TestCase
{
    /** @throws InvalidOperatorArgumentException */
    public function testAddition(): void
    {
        $operator = new Addition();
        $result = $operator->apply(new Number(5), new Number(3));
        $this->assertEquals(8, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testSubtraction(): void
    {
        $operator = new Subtraction();
        $result = $operator->apply(new Number(5), new Number(3));
        $this->assertEquals(2, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testMultiplication(): void
    {
        $operator = new Multiplication();
        $result = $operator->apply(new Number(5), new Number(3));
        $this->assertEquals(15, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testDivision(): void
    {
        $operator = new Division();
        $result = $operator->apply(new Number(10), new Number(2));
        $this->assertEquals(5, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testDivisionByZero(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage('Division by zero is not allowed.');
        (new Division())->apply(new Number(10), new Number(0));
    }

    /** @throws InvalidOperatorArgumentException */
    public function testPower(): void
    {
        $operator = new Power();
        $result = $operator->apply(new Number(2), new Number(3));
        $this->assertEquals(8, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testNegation(): void
    {
        $operator = new Negation();
        $result = $operator->apply(new Number(5));
        $this->assertEquals(-5, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
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
        $this->expectExceptionMessage('Factorial operator is only defined for integers.');
        (new Factorial())->apply(new Number(1.5));
    }

    /** @throws InvalidOperatorArgumentException */
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

    /** @throws InvalidOperatorArgumentException */
    public function testSqrtOfZero(): void
    {
        $operator = new Sqrt();
        $result = $operator->apply(new Number(0));
        $this->assertEquals(0, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testCubeRoot(): void
    {
        $operator = new CubeRoot();
        $result = $operator->apply(new Number(27));
        $this->assertEquals(3, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testCubeRootOfNegative(): void
    {
        $operator = new CubeRoot();
        $result = $operator->apply(new Number(-27));
        $this->assertEquals(-3, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
    public function testCubeRootOfZero(): void
    {
        $operator = new CubeRoot();
        $result = $operator->apply(new Number(0));
        $this->assertEquals(0, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
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

    /** @throws InvalidOperatorArgumentException */
    public function testFourthRootOfZero(): void
    {
        $operator = new FourthRoot();
        $result = $operator->apply(new Number(0));
        $this->assertEquals(0, $result->value());
    }

    /** @throws InvalidOperatorArgumentException */
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

    /** @throws InvalidOperatorArgumentException */
    public function testExp(): void
    {
        $operator = new Exp();
        $result = $operator->apply(new Number(0));
        $this->assertEquals(1, $result->value());
    }

    public function testAdditionWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Addition())->apply(new Number(1));
    }

    public function testSubtractionWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Subtraction())->apply(new Number(1));
    }

    public function testMultiplicationWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Multiplication())->apply(new Number(1));
    }

    public function testDivisionWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Division())->apply(new Number(1));
    }

    public function testPowerWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Power())->apply(new Number(1));
    }

    public function testNegationWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Negation())->apply(new Number(1), new Number(2));
    }

    public function testFactorialWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Factorial())->apply(new Number(1), new Number(2));
    }

    public function testSqrtWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Sqrt())->apply(new Number(1), new Number(2));
    }

    public function testCubeRootWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new CubeRoot())->apply(new Number(1), new Number(2));
    }

    public function testFourthRootWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new FourthRoot())->apply(new Number(1), new Number(2));
    }

    public function testLogWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Log())->apply(new Number(1), new Number(2));
    }

    public function testExpWrongArgs(): void
    {
        $this->expectException(InvalidOperatorArgumentException::class);
        (new Exp())->apply(new Number(1), new Number(2));
    }

    /** @param class-string<OperatorInterface> $class */
    #[DataProvider('operatorMetaProvider')]
    public function testOperatorMeta(
        string $class,
        int $precedence,
        Associativity $associativity,
        OperatorType $type
    ): void {
        $operator = new $class();
        $this->assertSame($precedence, $operator->getPrecedence());
        $this->assertSame($associativity, $operator->getAssociativity());
        $this->assertSame($type, $operator->getType());
    }

    /** @return Generator<string, array{0: class-string<OperatorInterface>, 1: int, 2: Associativity, 3: OperatorType}> */
    public static function operatorMetaProvider(): Generator
    {
        yield 'Addition' => [Addition::class, 1, Associativity::Left, OperatorType::Binary];
        yield 'Subtraction' => [Subtraction::class, 1, Associativity::Left, OperatorType::Binary];
        yield 'Multiplication' => [Multiplication::class, 2, Associativity::Left, OperatorType::Binary];
        yield 'Division' => [Division::class, 2, Associativity::Left, OperatorType::Binary];
        yield 'Power' => [Power::class, 3, Associativity::Right, OperatorType::Binary];
        yield 'Negation' => [Negation::class, 3, Associativity::Right, OperatorType::UnaryPrefix];
        yield 'Factorial' => [Factorial::class, 5, Associativity::None, OperatorType::UnaryPostfix];
        yield 'Sqrt' => [Sqrt::class, 4, Associativity::None, OperatorType::Function];
        yield 'CubeRoot' => [CubeRoot::class, 4, Associativity::None, OperatorType::Function];
        yield 'FourthRoot' => [FourthRoot::class, 4, Associativity::None, OperatorType::Function];
        yield 'Log' => [Log::class, 4, Associativity::None, OperatorType::Function];
        yield 'Exp' => [Exp::class, 4, Associativity::None, OperatorType::Function];
    }

    /**
     * @param class-string<OperatorInterface> $operatorClass
     * @throws ReflectionException
     */
    #[DataProvider('mathOperatorProvider')]
    public function testMathOperatorWithBadOperand(string $operatorClass, int $operandCounts): void
    {
        $operator = new $operatorClass();
        $reflect = new ReflectionClass($operator);
        $operatorClassName = $reflect->getShortName();

        $this->expectException(InvalidOperatorArgumentException::class);
        $this->expectExceptionMessage(
            "$operatorClassName operator requires"
            . ($operandCounts === 1 ? ' a' : '')
            . " Number operand"
            . ($operandCounts === 1 ? '' : 's')
            . '.'
        );

        $badOperand = new readonly class implements OperandInterface
        {
            #[Override]
            public function value(): string
            {
                return 'test';
            }
        };

        /** @var array<int, OperandInterface> $operands */
        $operands = array_fill(0, $operandCounts, $badOperand);
        $operator->apply(...$operands);
    }

    /** @return Generator<string, array{0: class-string<OperatorInterface>, 1: int}> */
    public static function mathOperatorProvider(): Generator
    {
        yield 'Addition' => [Addition::class, 2];
        yield 'Subtraction' => [Subtraction::class, 2];
        yield 'Multiplication' => [Multiplication::class, 2];
        yield 'Division' => [Division::class, 2];
        yield 'Power' => [Power::class, 2];
        yield 'Negation' => [Negation::class, 1];
        yield 'Factorial' => [Factorial::class, 1];
        yield 'Sqrt' => [Sqrt::class, 1];
        yield 'CubeRoot' => [CubeRoot::class, 1];
        yield 'FourthRoot' => [FourthRoot::class, 1];
        yield 'Log' => [Log::class, 1];
        yield 'Exp' => [Exp::class, 1];
    }
}
