<?php

declare(strict_types=1);

namespace Rpn\Operands\Resolvers;

use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function is_numeric;

final class NumericOperandResolver implements OperandResolverInterface
{
    public function resolve(string $token): ?OperandInterface
    {
        if (is_numeric($token)) {
            return new Number((float)$token);
        }

        return null;
    }
}
