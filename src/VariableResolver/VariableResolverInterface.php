<?php

declare(strict_types=1);

namespace Rpn\VariableResolver;

use Rpn\Operands\OperandInterface;

interface VariableResolverInterface
{
    public function resolve(string $variableName, mixed $variableValue): OperandInterface;
}
