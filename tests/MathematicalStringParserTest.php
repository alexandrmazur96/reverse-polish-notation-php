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
        $this->assertEquals($expected, (new Expression(...$expressionParts))->evaluate());
    }

    public static function mathStringsProvider(): Generator
    {
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
        yield 'complex-1' => ['3 + 4 * 2 / ( 1 - 5 )', 1];
        yield 'complex-2' => ['5 + ( ( 1 + 2 ) * 4 ) - 3', 14];
        yield 'complex-3' => ['10 + 2 * 6', 22];
        yield 'complex-4' => ['100 * 2 + 12', 212];
        yield 'complex-5' => ['100 * ( 2 + 12 )', 1400];
        yield 'complex-6' => ['100 * ( 2 + 12 ) / 14', 100];
        yield 'complex-7' => ['10 - - 7', 17];
        yield 'complex-8' => ['(5+3)+12', 20];
        yield 'complex-9' => ['100 * (2 + 12) / 14', 100];
    }
}
