<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rpn\Expression;
use Rpn\Parser\MathematicalStringParser;

final class MathematicalStringParserTest extends TestCase
{
    #[DataProvider('mathStringsProvider')]
    public function testMathStringParsed(string $mathStr, float $expected): void
    {
        $expressionParts = (new MathematicalStringParser($mathStr))->parse();
        $this->assertEqualsWithDelta($expected, (new Expression(...$expressionParts))->evaluate(), 0.0001);
    }

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
        yield 'pow-func-1'   => ['pow(2, 3)', 8];
        yield 'pow-func-2'   => ['pow(2, 3) + 2', 10];
        yield 'pow-unicode-sq' => ['5²', 25]; // Unicode ²
        yield 'pow-unicode-cb' => ['2³', 8];  // Unicode ³

        // Right Associativity Test: 2^3^2 should be 2^(3^2) = 2^9 = 512.
        // If left associative, it would be (2^3)^2 = 8^2 = 64.
        yield 'pow-right-assoc' => ['2 ^ 3 ^ 2', 512];

        // --- Square Root (sqrt, √) ---
        yield 'sqrt-func-1' => ['sqrt(16)', 4];
        yield 'sqrt-func-2' => ['sqrt(16) + sqrt(9)', 7];
        yield 'sqrt-symbol-1' => ['√16', 4];
        yield 'sqrt-symbol-2' => ['√16 + √9', 7];
        yield 'sqrt-complex'  => ['sqrt(pow(3, 2) + pow(4, 2))', 5]; // Pythagorean 3-4-5

        // --- Other Roots (∛, ∜) ---
        yield 'cuberoot-symbol' => ['∛8', 2];
        yield 'cuberoot-neg'    => ['∛-8', -2]; // Assuming your CubeRoot op handles negatives
        yield 'fourthroot-symbol' => ['∜16', 2];

        // --- Factorial (!) ---
        yield 'factorial-1' => ['5!', 120];
        yield 'factorial-2' => ['3! + 2', 8]; // Precedence check: (3!) + 2
        yield 'factorial-3' => ['(2 + 1)!', 6];
        yield 'factorial-0' => ['0!', 1];

        // --- Logarithm & Exponential (log, exp) ---
        yield 'exp-1' => ['exp(0)', 1];
        yield 'log-1' => ['log(1)', 0];
        yield 'log-exp-inverse' => ['log(exp(5))', 5];
        yield 'exp-log-inverse' => ['exp(log(10))', 10];
        yield 'log-combo' => ['log(exp(2) * exp(3))', 5]; // ln(e^2 * e^3) = ln(e^5) = 5

        // --- Unary Minus Edge Cases ---
        yield 'unary-pow-precedence' => ['-2^2', -4]; // -(2^2) = -4, not (-2)^2 = 4
        yield 'unary-pow-grouping'   => ['(-2)^2', 4];
        yield 'unary-with-func'      => ['-sqrt(4)', -2];
        yield 'unary-chain'          => ['- - 5', 5]; // 0 - (0 - 5)

        // --- Mega Complex Combinations ---
        // 5! + 2^3 - sqrt(16) = 120 + 8 - 4 = 124
        yield 'mega-1' => ['5! + 2^3 - sqrt(16)', 124];

        // 10 + 3 * 2^2 = 10 + 3 * 4 = 22 (Check precedence: ^ higher than *)
        yield 'mega-2' => ['10 + 3 * 2^2', 22];

        // (3!)! = 6! = 720
        yield 'mega-nested-factorial' => ['(3!)!', 720];

        // sqrt(100) * 2 + 5! / 2 = 10 * 2 + 120 / 2 = 20 + 60 = 80
        yield 'mega-3' => ['sqrt(100) * 2 + 5! / 2', 80];
    }
}
