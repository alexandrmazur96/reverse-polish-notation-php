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

use function explode;
use function preg_replace;
use function trim;
use function is_numeric;

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
        $cleanedSource = preg_replace('/\s+/', ' ', trim($this->source));
        $operatorsStack = new SplStack();
        if ($cleanedSource !== false && $cleanedSource !== '') {
            foreach (explode(' ', $cleanedSource) as $token) {
                // numerals goes straight to the expression stream
                if (is_numeric($token)) {
                    yield new Number((float) $token);
                    continue;
                }

                if ($token === self::PARENTHESIS_OPEN) {
                    $operatorsStack->push($token);
                    continue;
                }

                if ($token === self::PARENTHESIS_CLOSE) {
                    while ($lastOperator = $operatorsStack->pop()) {
                        if ($lastOperator === self::PARENTHESIS_OPEN) {
                            break;
                        }
                        yield $lastOperator;
                    }
                    continue;
                }

                // operators have to be pushed to the operator stack first.
                // before put operator to the stack we have to check that operator on the top of the stack
                // has less precedence than the current one.
                $currentOperator = match ($token) {
                    '+' => new Addition(),
                    '-' => new Subtraction(),
                    '*' => new Multiplication(),
                    '/' => new Division(),
                    default => throw new InvalidArgumentException("Unknown token: {$token}"),
                };

                $lastOperator = $operatorsStack->isEmpty() ? null : $operatorsStack->top();
                if (
                    !is_string($lastOperator)
                    && $lastOperator !== null
                    && self::PRECEDENCE[$lastOperator::class] >= self::PRECEDENCE[$currentOperator::class]
                ) {
                    yield $operatorsStack->pop();
                }

                $operatorsStack->push($currentOperator);
            }

            yield from $operatorsStack;
        }
    }
}
