<?php

declare(strict_types=1);

namespace Rpn\Parser;

use InvalidArgumentException;
use Rpn\Operands\Number;
use Rpn\Operators\Addition;
use Rpn\Operators\Division;
use Rpn\Operators\Multiplication;
use Rpn\Operators\Subtraction;
use SplStack;

use function is_numeric;
use function preg_match_all;

readonly class MathematicalStringParser implements ParserInterface
{
    private const array PRECEDENCE = [
        Addition::class => 1,
        Subtraction::class => 1,
        Multiplication::class => 2,
        Division::class => 2,
    ];

    private const string PARENTHESIS_OPEN = '(';
    private const string PARENTHESIS_CLOSE = ')';
    
    public function __construct(private string $source)
    {
    }

    /** @inheritdoc */
    public function parse(): iterable
    {
        $operatorsStack = new SplStack();

        // Regex tokenizer to handle tight spacing like "(5+3)"
        preg_match_all('/\d+(?:\.\d+)?|[+\-*\/()]/', $this->source, $matches);
        $tokens = $matches[0];

        $isOperandExpected = true;

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                yield new Number((float) $token);
                $isOperandExpected = false;
                continue;
            }

            if ($token === self::PARENTHESIS_OPEN) {
                $operatorsStack->push($token);
                $isOperandExpected = true;
                continue;
            }

            if ($token === self::PARENTHESIS_CLOSE) {
                while (!$operatorsStack->isEmpty()) {
                    $lastOperator = $operatorsStack->pop();
                    if ($lastOperator === self::PARENTHESIS_OPEN) {
                        break;
                    }
                    yield $lastOperator;
                }
                $isOperandExpected = false;
                continue;
            }

            $currentOperator = match ($token) {
                '+' => new Addition(),
                '-' => new Subtraction(),
                '*' => new Multiplication(),
                '/' => new Division(),
                default => throw new InvalidArgumentException("Unknown token: $token"),
            };

            // UNARY MINUS LOGIC
            if ($token === '-' && $isOperandExpected) {
                // 1. Inject a zero to convert "-x" into "0 - x"
                yield new Number(0);

                // 2. Push the Subtraction operator immediately.
                // CRITICAL FIX: Do NOT enter the while loop below.
                // By skipping the pop loop, we effectively give this specific minus
                // higher precedence (right-associativity) than whatever is on the stack.
                $operatorsStack->push($currentOperator);

                // 3. We still expect a number next
                $isOperandExpected = true;
                continue;
            }

            // STANDARD BINARY OPERATOR LOGIC
            while (!$operatorsStack->isEmpty()) {
                $lastOperator = $operatorsStack->top();

                if ($lastOperator === self::PARENTHESIS_OPEN) {
                    break;
                }

                if (self::PRECEDENCE[$lastOperator::class] >= self::PRECEDENCE[$currentOperator::class]) {
                    yield $operatorsStack->pop();
                } else {
                    break;
                }
            }

            $operatorsStack->push($currentOperator);
            $isOperandExpected = true;
        }

        yield from $operatorsStack;
    }
}
