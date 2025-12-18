<?php

declare(strict_types=1);

namespace Rpn;

use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;
use Rpn\Stream\ExpressionPartsStream;
use SplStack;

readonly class Expression
{
    /** @throws InvalidExpressionException|InvalidOperatorArgumentException */
    public function evaluate(ExpressionPartsStream $stream): OperandInterface
    {
        /** @var SplStack<OperandInterface> $operandsStack */
        $operandsStack = new SplStack();

        foreach ($stream as $part) {
            if ($part instanceof OperandInterface) {
                $operandsStack->push($part);
                continue;
            }

            $operandA = $this->popOperand($operandsStack);

            if ($this->isUnary($part)) {
                $result = $part->apply($operandA);
            } else {
                $operandB = $this->popOperand($operandsStack);

                $result = $part->apply($operandB, $operandA);
            }

            $operandsStack->push($result);
        }

        if ($operandsStack->count() !== 1) {
            throw new InvalidExpressionException('Too many operands remaining.');
        }

        return $operandsStack->pop();
    }

    /**
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
        $type = $operator->getType();

        return $type === OperatorType::UnaryPrefix
               || $type === OperatorType::UnaryPostfix
               || $type === OperatorType::Function;
    }
}
