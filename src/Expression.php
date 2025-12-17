<?php

declare(strict_types=1);

namespace Rpn;

use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;
use SplStack;

readonly class Expression
{
    /** @var iterable<OperandInterface|OperatorInterface> */
    private iterable $parts;

    public function __construct(OperandInterface|OperatorInterface ...$parts)
    {
        $this->parts = $parts;
    }

    public function evaluate(): float
    {
        $operandsStack = new SplStack();
        foreach ($this->parts as $part) {
            if ($part instanceof OperandInterface) {
                $operandsStack->push($part);
                continue;
            }

            $rightOperand = $operandsStack->pop();
            $leftOperand = $operandsStack->pop();
            $operandsStack->push($part->apply($leftOperand, $rightOperand));
        }

        return $operandsStack->pop()->value();
    }
}
