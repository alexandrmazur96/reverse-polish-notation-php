<?php

declare(strict_types=1);

namespace Rpn\Tokenizers;

use Override;

use function array_map;
use function join;
use function preg_match_all;
use function preg_quote;
use function strlen;
use function usort;

readonly class StringTokenizer implements TokenizerInterface
{
    /** @var non-empty-string */
    private string $regex;

    /** @param string[] $symbols Custom operator symbols (e.g. ['+', '!=']) */
    public function __construct(array $symbols)
    {
        usort($symbols, static fn(string $a, string $b) => strlen($b) <=> strlen($a));

        $escaped = array_map(static fn(string $s) => preg_quote($s, '/'), $symbols);
        $symbolGroup = join('|', $escaped);

        // Words | Numbers | Custom Symbols | Structure | Catch-all
        $this->regex = "/[a-zA-Z_]+|\d+(?:\.\d+)?|$symbolGroup|[(),]|\S/u";
    }

    /** @return iterable<int, string> */
    #[Override]
    public function tokenize(string $source): iterable
    {
        preg_match_all($this->regex, $source, $matches);

        $matches = $matches[0] ?? [];
        foreach ($matches as $match) {
            if ($match !== '') {
                yield $match;
            }
        }
    }
}
