<?php

declare(strict_types=1);

namespace Rpn\Parsers;

use Generator;
use Override;
use Rpn\Enum\Associativity;
use Rpn\Enum\OperatorType;
use Rpn\Exceptions\InvalidExpressionException;
use Rpn\Exceptions\UnknownTokenException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\OperatorInterface;
use Rpn\Operators\OperatorRegistry;
use Rpn\Stream\ExpressionPartsStream;
use Rpn\Tokenizers\TokenizerInterface;
use SplStack;

use function is_numeric;

readonly class ShuntingYardParser implements ParserInterface
{
    private const string PARENTHESIS_OPEN = '(';
    private const string PARENTHESIS_CLOSE = ')';
    private const string FUNCTION_ARG_SEPARATOR = ',';

    public function __construct(
        private OperatorRegistry $registry,
        private TokenizerInterface $tokenizer
    ) {
    }

    /** @inheritdoc */
    #[Override]
    public function parse(string $source): ExpressionPartsStream
    {
        return ExpressionPartsStream::of($this->parseInfixSource($source));
    }

    /**
     * @return Generator<int, OperatorInterface|OperandInterface, mixed, never>
     * @throws UnknownTokenException
     * @throws InvalidExpressionException
     */
    private function parseInfixSource(string $source): Generator
    {
        /** @var SplStack<string|OperatorInterface> $operatorsStack */
        $operatorsStack = new SplStack();
        $isOperandExpected = true;
        $openParentheses = 0; // Track balance to ensure safety at the end

        foreach ($this->tokenizer->tokenize($source) as $token) {
            if (is_numeric($token)) {
                yield new Number((float)$token);
                $isOperandExpected = false;
                continue;
            }

            if ($token === self::PARENTHESIS_OPEN) {
                $operatorsStack->push(self::PARENTHESIS_OPEN);
                $openParentheses++;
                $isOperandExpected = true;
                continue;
            }

            if ($token === self::PARENTHESIS_CLOSE) {
                while (!$operatorsStack->isEmpty() && $operatorsStack->top() !== self::PARENTHESIS_OPEN) {
                    /** @var OperatorInterface $popped */
                    $popped = $operatorsStack->pop();
                    yield $popped;
                }

                if ($operatorsStack->isEmpty()) {
                    throw new InvalidExpressionException("Mismatched parentheses");
                }
                $operatorsStack->pop(); // Pop parenthesis open
                $openParentheses--;     // Decrement counter

                if (!$operatorsStack->isEmpty()) {
                    $operator = $operatorsStack->top();
                    if ($operator instanceof OperatorInterface && $operator->getType() === OperatorType::Function) {
                        /** @var OperatorInterface $popped */
                        $popped = $operatorsStack->pop();
                        yield $popped;
                    }
                }
                $isOperandExpected = false;
                continue;
            }

            if ($token === self::FUNCTION_ARG_SEPARATOR) {
                while (!$operatorsStack->isEmpty() && $operatorsStack->top() !== self::PARENTHESIS_OPEN) {
                    /** @var OperatorInterface $popped */
                    $popped = $operatorsStack->pop();
                    yield $popped;
                }
                $isOperandExpected = true;
                continue;
            }

            // Operators
            $op = $this->registry->resolve($token, $isOperandExpected);

            if ($op !== null) {
                $type = $op->getType();

                if ($type === OperatorType::UnaryPrefix || $type === OperatorType::Function) {
                    $operatorsStack->push($op);
                    $isOperandExpected = true;
                    continue;
                }

                if ($type === OperatorType::UnaryPostfix) {
                    yield $op;
                    $isOperandExpected = false;
                    continue;
                }

                while (!$operatorsStack->isEmpty()) {
                    $top = $operatorsStack->top();
                    if ($top === self::PARENTHESIS_OPEN) {
                        break;
                    }

                    /** @var OperatorInterface $top */
                    if (
                        (
                            $op->getAssociativity() === Associativity::Left
                            && $op->getPrecedence() <= $top->getPrecedence()
                        )
                        || (
                            $op->getAssociativity() === Associativity::Right
                            && $op->getPrecedence() < $top->getPrecedence()
                        )
                    ) {
                        /** @var OperatorInterface $popped */
                        $popped = $operatorsStack->pop();
                        yield $popped;
                    } else {
                        break;
                    }
                }
                $operatorsStack->push($op);
                $isOperandExpected = true;
                continue;
            }

            throw new UnknownTokenException("Unknown token: $token");
        }

        // If parentheses are balanced (0), the stack implies strictly OperatorInterface.
        if ($openParentheses > 0) {
            throw new InvalidExpressionException("Mismatched parentheses");
        }

        /** @var SplStack<OperatorInterface> $operatorsStack */
        yield from $operatorsStack;
    }
}
