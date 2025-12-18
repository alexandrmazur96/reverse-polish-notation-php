<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Exceptions\UnknownFunctionException;
use Rpn\Exceptions\UnknownTokenException;
use Rpn\Expression;
use Rpn\Operators\Addition;
use Rpn\Operators\CubeRoot;
use Rpn\Operators\Division;
use Rpn\Operators\Exp;
use Rpn\Operators\Factorial;
use Rpn\Operators\FourthRoot;
use Rpn\Operators\Log;
use Rpn\Operators\Multiplication;
use Rpn\Operators\Negation;
use Rpn\Operators\OperatorRegistry;
use Rpn\Operators\Power;
use Rpn\Operators\Sqrt;
use Rpn\Operators\Subtraction;
use Rpn\Parsers\ShuntingYardParser;
use Rpn\Parsers\ShuntingYardParserBuilder;
use Rpn\Tokenizers\StringTokenizer;
use Throwable;

final class ParsersTest extends TestCase
{
    #[DataProvider('mathStringsProvider')]
    public function testShuntingYardParser(string $mathStr, float $expected): void
    {
        $registry = new OperatorRegistry();
        $registry->add('+', new Addition());
        $registry->add('-', new Subtraction());
        $registry->add('-', new Negation());
        $registry->add(['/', '÷'], new Division());
        $registry->add(['*', '×'], new Multiplication());
        $registry->add(['^', 'pow'], new Power());
        $registry->add('!', new Factorial());
        $registry->add(['√', 'sqrt'], new Sqrt());
        $registry->add('∛', new CubeRoot());
        $registry->add('∜', new FourthRoot());
        $registry->add('log', new Log());
        $registry->add('exp', new Exp());

        $parser = new ShuntingYardParser(
            $registry,
            new StringTokenizer($registry->getSymbolicTokens()),
        );

        try {
            $this->assertEqualsWithDelta($expected, (new Expression())->evaluate($parser->parse($mathStr)), 0.0001);
        } catch (Throwable $e) {
            $this->fail("Failed to evaluate expression for '$mathStr': " . $e->getMessage());
        }
    }

    /**
     * @throws InvalidOperatorArgumentException
     * @throws UnknownTokenException
     * @throws UnknownFunctionException
     */
    public function testMismatchedParentheses(): void
    {
        $this->expectException(InvalidExpressionException::class);
        $this->expectExceptionMessage('Mismatched parentheses');

        $parser = ShuntingYardParserBuilder::math()->build();
        (new Expression())->evaluate($parser->parse('(3 + 4'));
    }

    /**
     * @throws InvalidExpressionException
     * @throws InvalidOperatorArgumentException
     * @throws UnknownFunctionException
     */
    public function testUnknownToken(): void
    {
        $this->expectException(UnknownTokenException::class);
        $this->expectExceptionMessage('Unknown token: @');

        $parser = ShuntingYardParserBuilder::math()->build();
        (new Expression())->evaluate($parser->parse('3 @ 4'));
    }

    /**
     * @throws InvalidOperatorArgumentException
     * @throws UnknownTokenException
     * @throws UnknownFunctionException
     */
    public function testExpressionEndingWithOpenParenthesis(): void
    {
        $this->expectException(InvalidExpressionException::class);
        $this->expectExceptionMessage('Mismatched parentheses');

        $parser = ShuntingYardParserBuilder::math()->build();
        $evaluator = new Expression();
        $evaluator->evaluate($parser->parse('5 + ('));
    }

    public function testStringTokenizerWithNoSymbols(): void
    {
        $tokenizer = new StringTokenizer([]);
        $tokens = iterator_to_array($tokenizer->tokenize('3 + 4'));

        $this->assertEquals(['3', '+', '4'], $tokens);
    }

    /**
     * @throws InvalidOperatorArgumentException
     * @throws UnknownTokenException
     * @throws UnknownFunctionException
     */
    public function testCommaWithEmptyOperatorStack(): void
    {
        $parser = ShuntingYardParserBuilder::math()->build();
        $evaluator = new Expression();

        // An expression like "5, 3" is not valid syntax, but we need to ensure
        // the parser handles it gracefully without crashing.
        // The parser should effectively ignore the comma and treat it as "5 3".
        // The evaluator will then throw an exception for too many operands.
        $this->expectException(InvalidExpressionException::class);
        $this->expectExceptionMessage('Too many operands remaining.');

        $rpnStream = $parser->parse('5, 3');
        $evaluator->evaluate($rpnStream);
    }

    /**
     * @throws InvalidOperatorArgumentException
     * @throws UnknownTokenException
     * @throws UnknownFunctionException
     * @throws InvalidExpressionException
     */
    public function testMismatchedClosingParenthesis(): void
    {
        $this->expectException(InvalidExpressionException::class);
        $this->expectExceptionMessage('Mismatched parentheses');

        $parser = ShuntingYardParserBuilder::math()->build();
        (new Expression())->evaluate($parser->parse('3 + 4)'));
    }

    /** @return Generator<string, array{0: string, 1: float}> */
    public static function mathStringsProvider(): Generator
    {
        // --- Basic Arithmetic ---
        yield 'simple-add-1' => ['3 + 4', 7];
        yield 'simple-add-2' => ['-3 + 4', 1];
        yield 'simple-add-3' => ['3 + -4', -1];
        yield 'simple-sub-1' => ['3 - 4', -1];
        yield 'simple-sub-2' => ['-3 - 4', -7];
        yield 'simple-sub-3' => ['-3 - -4', 1];
        yield 'simple-mul-1' => ['3 * 4', 12];
        yield 'simple-mul-2' => ['3 * -4', -12];
        yield 'simple-mul-3' => ['0 * 4', 0];
        yield 'simple-div-1' => ['12 / 4', 3];
        yield 'simple-div-2' => ['-12 / 4', -3];
        yield 'simple-div-3' => ['12 / -4', -3];
        yield 'simple-div-4' => ['-12 / -4', 3];

        // --- Unicode Aliases (×, ÷) ---
        yield 'unicode-mul' => ['3 × 4', 12];
        yield 'unicode-div' => ['12 ÷ 4', 3];
        yield 'unicode-mixed' => ['10 × 2 ÷ 5', 4];

        // --- Complex Basic Combinations ---
        yield 'complex-1' => ['3 + 4 * 2 / ( 1 - 5 )', 1];
        yield 'complex-2' => ['5 + ( ( 1 + 2 ) * 4 ) - 3', 14];
        yield 'complex-3' => ['10 + 2 * 6', 22];
        yield 'complex-4' => ['100 * 2 + 12', 212];
        yield 'complex-5' => ['100 * ( 2 + 12 )', 1400];
        yield 'complex-6' => ['100 * ( 2 + 12 ) / 14', 100];
        yield 'complex-7' => ['10 - - 7', 17];
        yield 'complex-8' => ['(5+3)+12', 20];
        yield 'complex-9' => ['100 * (2 + 12) / 14', 100];

        // --- Power (^, pow) ---
        yield 'pow-symbol-1' => ['2 ^ 3', 8];
        yield 'pow-symbol-2' => ['2^3', 8]; // Tight spacing
        yield 'pow-symbol-3' => ['2^0', 1];
        yield 'pow-func-1' => ['pow(2, 3)', 8];
        yield 'pow-func-2' => ['pow(2, 3) + 2', 10];
        yield 'pow-with-expression-arg' => ['pow(1 + 1, 3)', 8];

        // Right Associativity Test: 2^3^2 should be 2^(3^2) = 2^9 = 512.
        // If left associative, it would be (2^3)^2 = 8^2 = 64.
        yield 'pow-right-assoc' => ['2 ^ 3 ^ 2', 512];

        // --- Functions (sqrt, log, exp) ---
        yield 'sqrt-func' => ['sqrt(16)', 4];
        yield 'sqrt-symbol' => ['√16', 4];
        yield 'sqrt-combo' => ['sqrt(16) + 4', 8];
        yield 'log-func' => ['log(1)', 0];
        yield 'exp-func' => ['exp(0)', 1];

        // --- Unary Operators (!, negation) ---
        yield 'factorial' => ['5!', 120];
        yield 'negation' => ['-5', -5];
        yield 'negation-complex' => ['-5 + 10', 5];

        // --- Root symbols ---
        yield 'cube-root' => ['∛27', 3];
        yield 'fourth-root' => ['∜81', 3];
    }
}
