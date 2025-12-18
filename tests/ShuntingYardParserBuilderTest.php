<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Rpn\Expression;
use Rpn\Operators\Addition;
use Rpn\Parsers\ShuntingYardParserBuilder;
use Rpn\Tokenizers\StringTokenizer;
use Throwable;

final class ShuntingYardParserBuilderTest extends TestCase
{
    public function testEmpty(): void
    {
        $parser = ShuntingYardParserBuilder::empty()
            ->withOperator('+', new Addition())
            ->build();
        try {
            $this->assertEquals(4, (new Expression())->evaluate($parser->parse('2 + 2')));
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testMath(): void
    {
        $parser = ShuntingYardParserBuilder::math()->build();
        try {
            $this->assertEquals(9, (new Expression())->evaluate($parser->parse('3 ^ 2')));
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testWithTokenizer(): void
    {
        $parser = ShuntingYardParserBuilder::empty()
            ->withOperator('+', new Addition())
            ->withTokenizer(new StringTokenizer(['+']))
            ->build();

        try {
            $this->assertEquals(5, (new Expression())->evaluate($parser->parse('2 + 3')));
        } catch (Throwable $e) {
            $this->fail($e->getMessage());
        }
    }
}
