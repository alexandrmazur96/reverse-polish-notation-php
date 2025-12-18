<?php

declare(strict_types=1);

namespace Rpn\Tokenizers;

interface TokenizerInterface
{
    /** @return iterable<int, string> */
    public function tokenize(string $source): iterable;
}
