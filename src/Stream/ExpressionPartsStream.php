<?php

declare(strict_types=1);

namespace Rpn\Stream;

use IteratorAggregate;
use Override;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;
use Traversable;

/** @implements IteratorAggregate<int, OperatorInterface|OperandInterface> */
final readonly class ExpressionPartsStream implements IteratorAggregate
{
    /** @param iterable<int, OperatorInterface|OperandInterface> $source */
    public function __construct(private iterable $source)
    {
    }

    /** @param iterable<int, OperatorInterface|OperandInterface> $source */
    public static function of(iterable $source): self
    {
        return new self($source);
    }

    /** @return Traversable<int, OperatorInterface|OperandInterface> */
    #[Override]
    public function getIterator(): Traversable
    {
        yield from $this->source;
    }
}
