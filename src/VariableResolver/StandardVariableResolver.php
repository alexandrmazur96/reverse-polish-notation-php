<?php

declare(strict_types=1);

namespace Rpn\VariableResolver;

use Override;
use Rpn\Exceptions\InvalidVariableException;
use Rpn\Operands\Number;
use Rpn\Operands\OperandInterface;

use function is_numeric;

readonly class StandardVariableResolver implements VariableResolverInterface
{
    #[Override]
    public function resolve(string $variableName, mixed $variableValue): OperandInterface
    {
        if ($variableValue instanceof OperandInterface) {
            return $variableValue;
        }

        if (is_numeric($variableValue)) {
            return new Number($variableValue);
        }

        throw new InvalidVariableException('Unsupported variable type for variable: ' . $variableName);
    }
}
