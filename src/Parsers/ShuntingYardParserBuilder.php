<?php

declare(strict_types=1);

namespace Rpn\Parsers;

use Rpn\Operands\Resolvers\NumericOperandResolver;
use Rpn\Operands\Resolvers\OperandResolverInterface;
use Rpn\Operators\Math\Addition;
use Rpn\Operators\Math\CubeRoot;
use Rpn\Operators\Math\Division;
use Rpn\Operators\Math\Exp;
use Rpn\Operators\Math\Factorial;
use Rpn\Operators\Math\FourthRoot;
use Rpn\Operators\Math\Log;
use Rpn\Operators\Math\Max;
use Rpn\Operators\Math\Min;
use Rpn\Operators\Math\Multiplication;
use Rpn\Operators\Math\Negation;
use Rpn\Operators\Math\Percent;
use Rpn\Operators\Math\Power;
use Rpn\Operators\Math\Sqrt;
use Rpn\Operators\Math\Subtraction;
use Rpn\Operators\OperatorInterface;
use Rpn\Operators\OperatorRegistry;
use Rpn\Tokenizers\StringTokenizer;
use Rpn\Tokenizers\TokenizerInterface;

readonly class ShuntingYardParserBuilder
{
    private function __construct(
        private OperatorRegistry $operatorRegistry,
        private ?TokenizerInterface $tokenizer = null,
        private ?OperandResolverInterface $operandResolver = null
    ) {
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
        $operatorRegistry->add('%', new Percent());
        $operatorRegistry->add('min', new Min());
        $operatorRegistry->add('max', new Max());

        return new self(
            operatorRegistry: $operatorRegistry,
            operandResolver: new NumericOperandResolver()
        );
    }

    /** @param string|array<int, string> $symbols */
    public function withOperator(string|array $symbols, OperatorInterface $operator): self
    {
        $this->operatorRegistry->add($symbols, $operator);
        return $this;
    }

    public function withTokenizer(TokenizerInterface $tokenizer): self
    {
        return new self($this->operatorRegistry, $tokenizer);
    }

    public function withOperandResolver(OperandResolverInterface $operandResolver): self
    {
        return new self($this->operatorRegistry, $this->tokenizer, $operandResolver);
    }

    public function build(): ShuntingYardParser
    {
        return new ShuntingYardParser(
            $this->operatorRegistry,
            $this->tokenizer ?? new StringTokenizer($this->operatorRegistry->getSymbolicTokens()),
            $this->operandResolver ?? new NumericOperandResolver()
        );
    }
}
