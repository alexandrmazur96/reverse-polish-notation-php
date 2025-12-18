<?php

declare(strict_types=1);

namespace Rpn\Parsers;

use Rpn\Operators\Addition;
use Rpn\Operators\CubeRoot;
use Rpn\Operators\Division;
use Rpn\Operators\Exp;
use Rpn\Operators\Factorial;
use Rpn\Operators\FourthRoot;
use Rpn\Operators\Log;
use Rpn\Operators\Multiplication;
use Rpn\Operators\Negation;
use Rpn\Operators\OperatorInterface;
use Rpn\Operators\OperatorRegistry;
use Rpn\Operators\Power;
use Rpn\Operators\Sqrt;
use Rpn\Operators\Subtraction;
use Rpn\Tokenizers\StringTokenizer;

final readonly class ShuntingYardParserBuilder
{
    private function __construct(private OperatorRegistry $operatorRegistry)
    {
    }

    public static function empty(): self
    {
        return new self(new OperatorRegistry());
    }

    public static function math(): self
    {
        $operatorRegistry = new OperatorRegistry();
        $operatorRegistry->add('+', new Addition());
        $operatorRegistry->add('-', new Subtraction());
        $operatorRegistry->add('-', new Negation());
        $operatorRegistry->add(['/', '÷'], new Division());
        $operatorRegistry->add(['*', '×'], new Multiplication());
        $operatorRegistry->add(['^', 'pow'], new Power());
        $operatorRegistry->add('!', new Factorial());
        $operatorRegistry->add(['√', 'sqrt'], new Sqrt());
        $operatorRegistry->add('∛', new CubeRoot());
        $operatorRegistry->add('∜', new FourthRoot());
        $operatorRegistry->add('log', new Log());
        $operatorRegistry->add('exp', new Exp());

        return new self($operatorRegistry);
    }

    /** @param string|array<int, string> $symbols */
    public function withOperator(string|array $symbols, OperatorInterface $operator): self
    {
        $this->operatorRegistry->add($symbols, $operator);
        return $this;
    }

    public function build(): ShuntingYardParser
    {
        return new ShuntingYardParser(
            $this->operatorRegistry,
            new StringTokenizer($this->operatorRegistry->getSymbolicTokens())
        );
    }
}
