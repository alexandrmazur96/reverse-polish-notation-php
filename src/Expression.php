<?php

declare(strict_types=1);

namespace Rpn;

use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\CubeRoot;
use Rpn\Operators\Exp;
use Rpn\Operators\Factorial;
use Rpn\Operators\FourthRoot;
use Rpn\Operators\Log;
use Rpn\Operators\OperatorInterface;
use Rpn\Operators\Sqrt;
use SplStack;

readonly class Expression
{
    /** @var iterable<OperandInterface|OperatorInterface> */
    private iterable $parts;

    public function __construct(OperandInterface|OperatorInterface ...$parts)
    {
        $this->parts = $parts;
    }

    /** @throws InvalidExpressionException */
    public function evaluate(): float
    {
        /** @var SplStack<OperandInterface> $operandsStack */
        $operandsStack = new SplStack();

        foreach ($this->parts as $part) {
            if ($part instanceof OperandInterface) {
                $operandsStack->push($part);
                continue;
            }

            // For Binary ops, this is the RIGHT side. For Unary, it's the ONLY side.
            $operandA = $this->popOperand($operandsStack);

            if ($this->isUnary($part)) {
                // We pass a dummy '0' as the second argument to satisfy the Interface.
                // Unary implementations (Sqrt, Log) use the $left argument, so we pass $operandA first.
                $result = $part->apply($operandA, new Number(0));
            } else {
                $operandB = $this->popOperand($operandsStack);

                $result = $part->apply($operandB, $operandA);
            }

            $operandsStack->push($result);
        }

        // The stack must contain exactly one item (the result)
        if ($operandsStack->count() !== 1) {
            throw new InvalidExpressionException('Too many operands remaining.');
        }

        return $operandsStack->pop()->value();
    }

    /**
     * Safe pop that throws a clear exception instead of crashing
     * @param SplStack<OperandInterface> $exprStack
     * @throws InvalidExpressionException
     */
    private function popOperand(SplStack $exprStack): OperandInterface
    {
        if ($exprStack->isEmpty()) {
            throw new InvalidExpressionException('Not enough operands.');
        }

        return $exprStack->pop();
    }

    private function isUnary(OperatorInterface $operator): bool
    {
        return $operator instanceof Sqrt
               || $operator instanceof Factorial
               || $operator instanceof Log
               || $operator instanceof Exp
               || $operator instanceof CubeRoot
               || $operator instanceof FourthRoot;
    }
}
