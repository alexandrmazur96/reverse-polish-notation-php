<?php

declare(strict_types=1);

namespace Rpn\Operators;

use Rpn\Enum\OperatorType;

use function array_unique;
use function preg_match;

final class OperatorRegistry
{
    /** @var array<string, OperatorInterface> */
    private array $prefixOperators = [];

    /** @var array<string, OperatorInterface> */
    private array $infixOperators = [];

    /** @var array<int, string> */
    private array $symbolicTokens = [];

    /** @param string|array<int, string> $symbols */
    public function add(string|array $symbols, OperatorInterface $operator): self
    {
        foreach ((array)$symbols as $symbol) {
            $this->registerSingle($symbol, $operator);
            // If symbol is NOT a word (e.g. "+"), track it for regex
            if (!preg_match('/^[a-zA-Z_]+$/', $symbol)) {
                $this->symbolicTokens[] = $symbol;
            }
        }
        return $this;
    }

    public function resolve(string $token, bool $isPrefixContext): ?OperatorInterface
    {
        return $isPrefixContext
            ? ($this->prefixOperators[$token] ?? $this->infixOperators[$token] ?? null)
            : ($this->infixOperators[$token] ?? null);
    }

    /** @return array<int, string> */
    public function getSymbolicTokens(): array
    {
        return array_unique($this->symbolicTokens);
    }

    private function registerSingle(string $symbol, OperatorInterface $operator): void
    {
        $type = $operator->getType();
        if ($type === OperatorType::UnaryPrefix || $type === OperatorType::Function) {
            $this->prefixOperators[$symbol] = $operator;
        } else {
            $this->infixOperators[$symbol] = $operator;
        }
    }
}
