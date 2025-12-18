<?php

declare(strict_types=1);

namespace Rpn\Operands\Resolvers;

use Rpn\Operands\OperandInterface;

interface OperandResolverInterface
{
    public function resolve(string $token): ?OperandInterface;
}
