<?php

declare(strict_types=1);

namespace Rpn;

use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Exceptions\InvalidOperatorArgumentException;
use Rpn\Exceptions\InvalidVariableException;
use Rpn\Exceptions\UndefinedVariableException;
use Rpn\Operands\OperandInterface;
use Rpn\Operands\Variable;
use Rpn\Operators\OperatorInterface;
use Rpn\Stream\ExpressionPartsStream;
use Rpn\VariableResolver\StandardVariableResolver;
use Rpn\VariableResolver\VariableResolverInterface;
use SplStack;

readonly class Expression
{
    public function __construct(private VariableResolverInterface $variableResolver = new StandardVariableResolver())
    {
    }

    /**
     * @param array<string, float|OperandInterface> $variables
     * @throws InvalidExpressionException
     * @throws UndefinedVariableException
     * @throws InvalidOperatorArgumentException
     * @throws InvalidVariableException
     */
    public function evaluate(ExpressionPartsStream $stream, array $variables = []): OperandInterface
    {
        /** @var SplStack<OperandInterface> $operandsStack */
        $operandsStack = new SplStack();

        foreach ($stream as $part) {
            if ($part instanceof OperandInterface) {
                if ($part instanceof Variable) {
                    $varName = $part->value();
                    if (!isset($variables[$varName])) {
                        throw new UndefinedVariableException("Undefined variable: $varName");
                    }

                    $part = $this->variableResolver->resolve($varName, $variables[$varName]);
                }

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
