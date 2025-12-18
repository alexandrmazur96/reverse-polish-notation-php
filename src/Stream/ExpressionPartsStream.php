<?php

declare(strict_types=1);

namespace Rpn\Stream;

use IteratorAggregate;
use Override;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;
use Traversable;

use function is_array;
use function iterator_to_array;

/** @implements IteratorAggregate<int, OperatorInterface|OperandInterface> */
final class ExpressionPartsStream implements IteratorAggregate
{
    /** @var array<int, OperatorInterface|OperandInterface> */
    private array $buffer;

    /** @param iterable<int, OperatorInterface|OperandInterface> $source */
    public function __construct(private iterable $source)
    {
        if (is_array($source)) {
            $this->buffer = $source;
        } else {
            $this->buffer = [];
        }
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
        if (!empty($this->buffer)) {
            yield from $this->buffer;
            return;
        }

        $buffer = [];
        foreach ($this->source as $key => $item) {
            $buffer[$key] = $item;
            yield $key => $item;
        }

        $this->buffer = $buffer;
    }

    /** @return array{source: array<int, OperatorInterface|OperandInterface>} */
    public function __serialize(): array
    {
        if (empty($this->buffer)) {
            $this->buffer = iterator_to_array($this->source, true);
        }

        return ['source' => $this->buffer];
    }

    /** @param array{source: array<int, OperatorInterface|OperandInterface>} $data */
    public function __unserialize(array $data): void
    {
        $this->source = $data['source'];
        $this->buffer = $data['source'];
    }
}
