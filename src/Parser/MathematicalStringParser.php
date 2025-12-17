<?php

declare(strict_types=1);

namespace Rpn\Parser;

use InvalidArgumentException;
use Rpn\Operands\Number;
use Rpn\Operators\Addition;
use Rpn\Operators\CubeRoot;
use Rpn\Operators\Division;
use Rpn\Operators\Exp;
use Rpn\Operators\Factorial;
use Rpn\Operators\FourthRoot;
use Rpn\Operators\Log;
use Rpn\Operators\Multiplication;
use Rpn\Operators\Power;
use Rpn\Operators\Sqrt;
use Rpn\Operators\Subtraction;
use SplStack;

use function is_numeric;
use function preg_match;
use function preg_match_all;

readonly class MathematicalStringParser implements ParserInterface
{
    private const array PRECEDENCE = [
        Addition::class => 1,
        Subtraction::class => 1,
        Multiplication::class => 2,
        Division::class => 2,
        Power::class => 3,
        Sqrt::class => 4,
        CubeRoot::class => 4,
        FourthRoot::class => 4,
        Log::class => 4,
        Exp::class => 4,
        Factorial::class => 5,
    ];

    private const array RIGHT_ASSOCIATIVE = [
        Power::class => true,
        Sqrt::class => true,
        CubeRoot::class => true,
        FourthRoot::class => true,
        Log::class => true,
        Exp::class => true,
    ];

    private const string PARENTHESIS_OPEN = '(';
    private const string PARENTHESIS_CLOSE = ')';
    private const string SEPARATOR = ',';

    public function __construct(private string $source)
    {
    }

    public function parse(): iterable
    {
        $operatorsStack = new SplStack();

        preg_match_all('/[a-zA-Z]+|\d+(?:\.\d+)?|[+\-*\/^!(),]|√|×|÷|∛|∜/u', $this->source, $matches);
        $tokens = $matches[0];

        $isOperandExpected = true;
        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                yield new Number((float)$token);
                $isOperandExpected = false;
                continue;
            }

            if (preg_match('/^[a-zA-Z]+$/', $token)) {
                $op = match ($token) {
                    'sqrt' => new Sqrt(),
                    'pow' => new Power(),
                    'log' => new Log(),
                    'exp' => new Exp(),
                    default => throw new InvalidArgumentException("Unknown function: $token")
                };
                $operatorsStack->push($op);
                $isOperandExpected = true;
                continue;
            }

            if ($token === self::SEPARATOR) {
                while (!$operatorsStack->isEmpty()) {
                    if ($operatorsStack->top() === self::PARENTHESIS_OPEN) {
                        break;
                    }
                    yield $operatorsStack->pop();
                }
                $isOperandExpected = true;
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

                if (!$operatorsStack->isEmpty()) {
                    $top = $operatorsStack->top();
                    if (
                        $top instanceof Sqrt ||
                        $top instanceof Log ||
                        $top instanceof Power ||
                        $top instanceof Exp ||
                        $top instanceof CubeRoot ||
                        $top instanceof FourthRoot
                    ) {
                        yield $operatorsStack->pop();
                    }
                }

                $isOperandExpected = false;
                continue;
            }

            $currentOperator = match ($token) {
                '+' => new Addition(),
                '-' => new Subtraction(),
                '*', '×' => new Multiplication(),
                '/', '÷' => new Division(),
                '^' => new Power(),
                '!' => new Factorial(),
                '√' => new Sqrt(),
                '∛' => new CubeRoot(),
                '∜' => new FourthRoot(),
                default => throw new InvalidArgumentException("Unknown token: $token"),
            };

            if ($token === '-' && $isOperandExpected) {
                yield new Number(0);
                $operatorsStack->push($currentOperator);
                $isOperandExpected = true;
                continue;
            }

            if ($token === '!') {
                yield $currentOperator;
                $isOperandExpected = false;
                continue;
            }

            while (!$operatorsStack->isEmpty()) {
                $lastOperator = $operatorsStack->top();

                if ($lastOperator === self::PARENTHESIS_OPEN) {
                    break;
                }

                $lastPrec = self::PRECEDENCE[$lastOperator::class] ?? 0;
                $currPrec = self::PRECEDENCE[$currentOperator::class] ?? 0;

                $isRightAssoc = self::RIGHT_ASSOCIATIVE[$currentOperator::class] ?? false;

                if (($isRightAssoc && $lastPrec > $currPrec) || (!$isRightAssoc && $lastPrec >= $currPrec)) {
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
