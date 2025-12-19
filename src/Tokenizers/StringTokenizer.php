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
        usort($symbols, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));

        $escaped = array_map(static fn(string $s): string => preg_quote($s, '/'), $symbols);
        $symbolGroup = join('|', $escaped);

        // 1. :[a-zA-Z_]\w*   -> Variables (e.g. :weight, :x_1) - MUST BE FIRST
        // 2. [a-zA-Z_]+      -> Words (Functions like min, max)
        // 3. \d+(?:\.\d+)?   -> Numbers
        // 4. $symbolGroup    -> Operators
        // 5. Structure       -> ( ) ,
        // 6. \S              -> Catch-all
        $this->regex = "/(:[a-zA-Z_]\w*)|[a-zA-Z_]+|\d+(?:\.\d+)?|$symbolGroup|[(),]|\S/u";
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
