<?php

declare(strict_types=1);

namespace Rpn\Parsers;

use Generator;
use Override;
use Rpn\Exceptions\UnknownFunctionException;
use Rpn\Exceptions\UnknownTokenException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;
use Rpn\Operators\Addition;
use Rpn\Operators\CubeRoot;
use Rpn\Operators\Division;
use Rpn\Operators\Exp;
use Rpn\Operators\Factorial;
use Rpn\Operators\FourthRoot;
use Rpn\Operators\Log;
use Rpn\Operators\Multiplication;
use Rpn\Operators\OperatorInterface;
use Rpn\Operators\Power;
use Rpn\Operators\Sqrt;
use Rpn\Operators\Subtraction;
use SplStack;

use function is_numeric;
use function preg_match;
use function preg_match_all;

/**
 * @psalm-type _PrecedenceClass=class-string<Addition|Subtraction|Multiplication|Division|Power|Sqrt|CubeRoot|FourthRoot|Log|Exp|Factorial>
 * @psalm-type _RightAssociativeClass=class-string<Power|Sqrt|CubeRoot|FourthRoot|Log|Exp>
 */
readonly class MathematicalStringParser implements ParserInterface
{
    /** @var array<_PrecedenceClass, int> */
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

    /** @var array<_RightAssociativeClass, bool> */
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

    /** @return Generator<int, OperandInterface|OperatorInterface, mixed, void> */
    #[Override]
    public function parse(): iterable
    {
        /** @var SplStack<string|OperatorInterface> $operatorsStack */
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
                    default => throw new UnknownFunctionException("Unknown function: $token")
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
                    /** @var OperatorInterface $lastOperator */
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
                        /** @var OperatorInterface $popped*/
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
                default => throw new UnknownTokenException("Unknown token: $token"),
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

                /**
                 * @var OperatorInterface $lastOperator
                 * @var _PrecedenceClass $lastOperatorClass
                 */
                $lastOperatorClass = $lastOperator::class;
                $lastPrecedence = self::PRECEDENCE[$lastOperatorClass] ?? 0;
                $currPrecedence = self::PRECEDENCE[$currentOperator::class] ?? 0;

                /**
                 * Somehow psalm completely ignores ?? operator here.
                 * @psalm-suppress MixedAssignment
                 * @psalm-suppress InvalidArrayOffset
                 */
                $isRightAssoc = self::RIGHT_ASSOCIATIVE[$currentOperator::class] ?? false;

                if (
                    ($isRightAssoc !== false && $lastPrecedence > $currPrecedence)
                    || ($isRightAssoc === false && $lastPrecedence >= $currPrecedence)
                ) {
                    /** @var OperatorInterface $popped*/
                    $popped = $operatorsStack->pop();
                    yield $popped;
                } else {
                    break;
                }
            }

            $operatorsStack->push($currentOperator);
            $isOperandExpected = true;
        }

        /** @var SplStack<OperatorInterface> $operatorsStack */
        yield from $operatorsStack;
    }
}
