<?php

declare(strict_types=1);

namespace Rpn\Operands\Resolvers;

use Override;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;
use Rpn\Operands\Variable;

use function is_numeric;
use function str_starts_with;
use function strlen;

final class OperandResolver implements OperandResolverInterface
{
    #[Override]
    public function resolve(string $token): ?OperandInterface
    {
        if (str_starts_with($token, ':')) {
            return strlen($token) < 2 ? null : new Variable($token);
        }

        if (is_numeric($token)) {
            return new Number((float)$token);
        }

        return null;
    }
}
