<?php

declare(strict_types=1);

namespace Rpn\Tests;

use Generator;
use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Expression;
use Rpn\Operands\Number;
use Rpn\Operators\Math\Addition;
use Rpn\Operators\Math\Multiplication;
use Rpn\Stream\ExpressionPartsStream;

use function serialize;
use function unserialize;

final class StreamSerializationTest extends TestCase
{
    /**
     * @throws InvalidExpressionException
     * @throws InvalidOperatorArgumentException
     */
    public function testSerialize(): void
    {
        $streamSource = static function (): Generator {
            yield new Number(10);
            yield new Number(20);
            yield new Number(30);
            yield new Multiplication();
            yield new Addition();
        };

        $expression = new Expression();
        $stream = new ExpressionPartsStream($streamSource());

        $this->assertEquals(610, $expression->evaluate($stream)->value());

        $serialized = serialize($stream);
        $unserializedStream = unserialize($serialized, ['allowed_classes' => true]);

        $this->assertInstanceOf(ExpressionPartsStream::class, $unserializedStream);
        $this->assertEquals(610, $expression->evaluate($unserializedStream)->value());
    }

    public function testSerializeGeneratorNotDrained(): void
    {
        $streamSource = static function (): Generator {
            yield new Number(10);
            yield new Number(20);
            yield new Number(30);
            yield new Multiplication();
            yield new Addition();
        };

        $expression = new Expression();

        $stream = new ExpressionPartsStream($streamSource());
        $serialized = serialize($stream);
        unset($stream);
        $unserializedStream = unserialize($serialized, ['allowed_classes' => true]);
        $this->assertInstanceOf(ExpressionPartsStream::class, $unserializedStream);
        $this->assertEquals(610, $expression->evaluate($unserializedStream)->value());
    }
}
